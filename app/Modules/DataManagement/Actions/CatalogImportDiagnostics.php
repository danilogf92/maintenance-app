<?php
namespace App\Modules\DataManagement\Actions;
use App\Modules\DataManagement\Models\DataImportRun;
use App\Modules\Equipment\Models\ReceivedEquipment;
use Illuminate\Support\Facades\Storage;
class CatalogImportDiagnostics
{
    private function resultPayload(DataImportRun $run): array
    {
        $created = array_sum(array_column($run->summary ?? [], 'created')); $issues = $run->issues ?? [];
        $updated = array_sum(array_column($run->summary ?? [], 'updated'));
        $linked = count(array_filter($run->link_results ?? [], fn ($r) => $r['status'] === 'linked'));
        $operation = $run->operation === 'histories' ? 'load_equipment_histories' : 'load_clients_and_institutions';
        $finished = $run->finished_at !== null;
        return ['status.json' => ['run_id' => $run->id, 'operation' => $operation, 'status' => $run->status,
            'finished' => $finished, 'database_modified' => $created + $updated + $linked > 0, 'created_records' => $created, 'updated_records' => $updated, 'linked_relations' => $linked, 'tables' => $run->summary ?? [],
            'errors' => count(array_filter($issues, fn ($i) => $i['severity'] === 'error')), 'warnings' => count(array_filter($issues, fn ($i) => $i['severity'] === 'warning'))],
            'errors.json' => ['run_id' => $run->id, 'issues' => $issues],
            'ack.json' => ['run_id' => $run->id, 'operation' => $operation, 'finished' => $finished,
                'imported' => $created + $updated + $linked > 0, 'created_records' => $created, 'updated_records' => $updated, 'linked_relations' => $linked,
                'tables' => $run->summary ?? [], 'requires_relationship_validation' => $run->operation !== 'histories' || !$finished || $run->status === 'failed'
                    || (bool)array_filter($run->link_results ?? [], fn ($r) => in_array($r['status'], ['pending', 'conflict'], true))
                    || (bool)array_filter($issues, fn ($i) => ($i['code'] ?? '') !== 'date_missing')],
            'assignment-results.json' => ['run_id' => $run->id, 'assignments' => $run->link_results ?? []]];
    }
    public function writeResult(DataImportRun $run): void
    {
        foreach ($this->resultPayload($run) as $name => $value) {
            Storage::disk('local')->put('data-management/imports/'.$run->id.'/'.$name, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        }
    }
    public function create(DataImportRun $run): string
    {
        $directory = 'data-management/imports/'.$run->id; $disk = Storage::disk('local'); $disk->makeDirectory($directory);
        $pending = [];
        foreach (ReceivedEquipment::with('equipment')->get() as $received) {
            if (!$received->equipment) { continue; }
            $pending[] = ['equipment_id' => $received->equipment_id, 'source_id' => $received->source_id,
                'client_linked' => $received->equipment->client_id !== null, 'institution_linked' => $received->equipment->institution_id !== null,
                'customer_history' => $received->payload['customer_history'] ?? null,
                'equipment_medical_centers_history' => $received->payload['equipment_medical_centers_history'] ?? null];
        }
        $payload = $this->resultPayload($run) + [
            'pending-links.json' => ['reason' => 'Estado actual de los vínculos; el resultado de esta ejecución está en assignment-results.json.', 'equipment' => $pending]];
        $path = $disk->path($directory.'/resultado-'.\Illuminate\Support\Str::random(8).'.zip'); $zip = new \ZipArchive;
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) { throw new \RuntimeException('No se pudo generar el diagnóstico.'); }
        try {
            foreach ($payload as $name => $value) {
                $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
                $disk->put($directory.'/'.$name, $json); $zip->addFromString($name, $json);
            }
        } finally { $zip->close(); }
        return $path;
    }
}
