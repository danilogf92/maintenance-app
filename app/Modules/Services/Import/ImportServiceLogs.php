<?php
namespace App\Modules\Services\Import;
use App\Modules\Services\Models\{Service, NinoxServiceImport};
use Illuminate\Support\Facades\DB;
class ImportServiceLogs
{
    public function execute(array $records, array $map, array $roots): array
    {
        $result = ['created' => 0, 'existing' => 0, 'conflicts' => 0, 'pending_ticket' => 0, 'pending_technician' => 0,
            'ready' => 0, 'missing' => 0, 'invalid' => 0];
        foreach ($records as $record) {
            $item = DB::transaction(fn () => $this->one($record, $map, $roots));
            foreach ($item as $key => $count) { $result[$key] += $count; }
        }
        return $result;
    }
    private function one(array $record, array $map, array $roots): array
    {
        $identity = ['ninox_table' => $record['ninox_table'], 'ninox_id' => $record['ninox_id']];
        $hash = hash('sha256', json_encode($record, JSON_THROW_ON_ERROR));
        $import = NinoxServiceImport::where($identity)->lockForUpdate()->first();
        if ($import && $import->source_hash !== $hash) { return ['conflicts' => 1]; }
        $links = app(ApprovedMappings::class)->values($record, $map);
        if (!$import) {
            if (Service::where('code', $record['report_code'])->exists()) { return ['conflicts' => 1]; }
            $service = Service::create([...$links, 'code' => $record['report_code'], 'service_date' => $record['service_date'],
                'service_type' => ($record['service_type_realized'] ?? null) ?: (($record['service_type'] ?? null) ?: 'No especificado'),
                'status' => 'historical', 'summary' => $record['visit_reason'] ?? null]);
            $import = NinoxServiceImport::create([...$identity, 'service_id' => $service->id, 'source_hash' => $hash, 'payload' => $record]);
            $created = true;
        } else {
            $service = $import->service()->lockForUpdate()->firstOrFail(); $created = false;
            foreach ($links as $field => $value) {
                if ($value && $service->$field && (int)$value !== (int)$service->$field) { return ['conflicts' => 1]; }
            }
            foreach ($links as $field => $value) { if (!$service->$field && $value) { $service->$field = $value; } }
            if ($service->isDirty()) { $service->increment('lock_version'); $service->save(); }
        }
        $documents = app(ImportDocuments::class)->execute($service, $record, $roots);
        return [$created ? 'created' : 'existing' => 1, 'pending_ticket' => $service->ticket_id ? 0 : 1,
            'pending_technician' => $service->technician_id ? 0 : 1, ...$documents];
    }
}
