<?php
namespace App\Modules\DataManagement\Jobs;
use App\Models\User;
use App\Modules\DataManagement\Import\CatalogRecord;
use App\Modules\DataManagement\Models\DataImportRun;
use App\Modules\DataManagement\Support\CatalogPackage;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\{DB, Log};
class ImportCatalogPackage implements ShouldQueue
{
    use Queueable;
    public int $tries = 1;
    public int $timeout = 210;
    public function __construct(public string $runId) { $this->onConnection('database')->onQueue('data-imports'); }
    public function handle(CatalogPackage $reader, CatalogRecord $importer): void
    {
        $run = DataImportRun::findOrFail($this->runId);
        if (in_array($run->status, ['completed', 'completed_with_observations', 'failed'], true)) { return; }
        $actor = User::find($run->requested_by);
        if (!$actor || !$actor->canAccessPanel(Filament::getPanel('admin')) || !$actor->can('data_management.manage')) {
            $this->failed(null); return;
        }
        $run->update(['status' => 'processing']);
        try {
            $rows = $reader->rows($reader->read('data-management/imports/'.$run->id));
            DB::transaction(function () use ($run, $rows, $importer) {
                $locked = DataImportRun::whereKey($run->id)->lockForUpdate()->firstOrFail();
                if ($locked->finished_at) { return; }
                $summary = []; $issues = [];
                foreach ($rows as $entity => $records) {
                    $counts = ['read' => count($records), 'created' => 0, 'existing' => 0, 'conflict' => 0, 'rejected' => 0];
                    foreach ($records as $source) {
                        $result = $importer->execute($entity, $source); $counts[$result['status']]++;
                        foreach ($result['issues'] as $issue) { $issues[] = $issue + ['entity' => $entity, 'source_id' => (string)$source['ninox_id']]; }
                        if ($result['status'] === 'conflict') {
                            $issues[] = ['severity' => 'error', 'code' => 'source_conflict', 'entity' => $entity, 'source_id' => (string)$source['ninox_id'],
                                'message' => 'Cambió el origen, falta el registro vinculado o el código ya existe. Se conserva el registro actual.'];
                        }
                    }
                    $summary[$entity] = $counts;
                }
                $issues[] = ['severity' => 'warning', 'code' => 'equipment_histories_pending', 'entity' => 'equipment',
                    'message' => 'Faltan los historiales que identifican el cliente y centro actuales de cada equipo. No se han modificado esos vínculos.'];
                $locked->update(['summary' => $summary, 'issues' => $issues, 'finished_at' => now(),
                    'status' => $issues ? 'completed_with_observations' : 'completed']);
            });
            app(\App\Support\Cache\PageDataCache::class)->invalidate('clients', 'institutions', 'institution-options', 'users');
            app(\App\Modules\DataManagement\Actions\CatalogImportDiagnostics::class)->writeResult($run->fresh());
        } catch (\Throwable $e) {
            $this->failed($e); throw $e;
        }
    }
    public function failed(?\Throwable $exception): void
    {
        // Avoid source values and SQL bindings in the downloadable diagnostics.
        DataImportRun::whereKey($this->runId)->whereNull('finished_at')->update(['status' => 'failed', 'finished_at' => now(),
            'issues' => json_encode([['severity' => 'error', 'code' => 'import_failed', 'message' => 'La carga no terminó. Revisa el proceso de cola y vuelve a preparar el paquete.']])]);
        if ($run = DataImportRun::find($this->runId)) { app(\App\Modules\DataManagement\Actions\CatalogImportDiagnostics::class)->writeResult($run); }
        Log::error('Catalog import failed', ['run_id' => $this->runId, 'exception_type' => $exception ? get_class($exception) : 'authorization']);
    }
}
