<?php
namespace App\Modules\DataManagement\Jobs;
use App\Models\User;
use App\Modules\DataManagement\Actions\{CatalogImportDiagnostics, ResolveEquipmentAssignments};
use App\Modules\DataManagement\Configuration\HistoryIssues;
use App\Modules\DataManagement\Import\EquipmentHistoryRecord;
use App\Modules\DataManagement\Models\DataImportRun;
use App\Modules\DataManagement\Support\EquipmentHistoryPackage;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
class ImportEquipmentHistoryPackage implements ShouldQueue
{
    use Queueable;
    public int $tries = 1;
    public int $timeout = 210;
    public function __construct(public string $runId) { $this->onConnection('database')->onQueue('data-imports'); }
    public function handle(EquipmentHistoryPackage $reader, EquipmentHistoryRecord $importer, ResolveEquipmentAssignments $resolver): void
    {
        $run = DataImportRun::findOrFail($this->runId);
        if ($run->finished_at) { return; }
        $actor = User::find($run->requested_by);
        if (!$actor || !$actor->canAccessPanel(Filament::getPanel('admin')) || !$actor->can('data_management.manage')) { $this->failed(null); return; }
        $run->update(['status' => 'processing']);
        try {
            $snapshot = $reader->rows($reader->read('data-management/imports/'.$run->id));
            DB::transaction(function () use ($snapshot, $importer, $resolver, $run) {
                $locked = DataImportRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
                if ($locked->finished_at) { return; }
                $summary = []; $issues = []; $conflicts = [];
                foreach ($snapshot as $entity => $rows) {
                    $count = ['read' => count($rows), 'created' => 0, 'updated' => 0, 'existing' => 0, 'conflict' => 0, 'rejected' => 0];
                    foreach ($rows as $source) {
                        $result = $importer->execute($entity, $source); $count[$result['status']]++;
                        if ($result['status'] === 'conflict') { $conflicts[$entity][] = (string)$source['ninox_id']; }
                        foreach ($result['issues'] as $code) {
                            $issues[] = ['severity' => $code === 'source_conflict' ? 'error' : 'warning', 'code' => $code,
                                'entity' => $entity, 'source_id' => (string)$source['ninox_id'], 'message' => HistoryIssues::message($code)];
                        }
                    }
                    $summary[$entity] = $count;
                }
                $links = $resolver->execute($snapshot, $conflicts);
                foreach ($links as $link) {
                    if (in_array($link['status'], ['pending', 'conflict'], true)) {
                        $issues[] = ['severity' => $link['status'] === 'conflict' ? 'error' : 'warning', 'code' => $link['reason'],
                            'entity' => 'equipment', 'source_id' => $link['source_id'], 'field' => $link['column'], 'message' => HistoryIssues::message($link['reason'])];
                    }
                }
                $locked->update(['summary' => $summary, 'link_results' => $links, 'issues' => $issues,
                    'status' => $issues ? 'completed_with_observations' : 'completed', 'finished_at' => now()]);
            });
            app(\App\Support\Cache\PageDataCache::class)->invalidate('equipment', 'clients', 'institutions', 'institution-options');
            app(CatalogImportDiagnostics::class)->writeResult($run->fresh());
        } catch (\Throwable $e) { $this->failed($e); throw $e; }
    }
    public function failed(?\Throwable $exception): void { (new ImportCatalogPackage($this->runId))->failed($exception); }
}
