<?php
namespace App\Modules\Services\Queries;
use App\Modules\Workflow\Queries\VisibleRecords;
class NinoxDetails
{
    public static function forService(?int $id): array
    {
        if (!$id) { return []; }
        $service = VisibleRecords::query('services')->findOrFail($id);
        $import = $service->ninoxImport;
        if (!$import) { return []; }
        $data = $import->payload;
        return ['engineer'=>\App\Modules\FieldEngineers\Configuration\Permissions::allows() ? $service->fieldEngineer : null, 'data' => $data, 'ticket' => \App\Modules\Workflow\Configuration\Permissions::allows('tickets') && $service->ticket_id ? VisibleRecords::query('tickets')->find($service->ticket_id) : null, 'ticket_resolved' => (bool)$service->ticket_id,
            'technician_resolved' => (bool)$service->technician_id, 'documents' => $service->documents()->orderBy('kind')->get(),
            'links' => ['Actividades / hallazgos' => $data['activities_findings'] ?? [],
                'Firmas de solicitantes' => $data['responsible_signatures'] ?? [], 'Recomendaciones' => $data['value_45'] ?? [],
                'Componentes del equipo' => $data['equipment_components'] ?? [], 'Repuestos instalados' => $data['value_59'] ?? []]];
    }
}
