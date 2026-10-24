<?php

namespace App\Modules\TechnicalReports\Queries;

use App\Modules\Activities\Actions\PrivatePhotos;
use App\Modules\Services\Models\Service;
use Illuminate\Validation\ValidationException;

class ReportData
{
    public function execute(Service $service, string $number, string $title): array
    {
        $service->load(['ticket.equipment.client', 'ticket.equipment.institution', 'ticket.equipment.brandRecord.manufacturer', 'ticket.equipment.modelRecord', 'technician']);
        $equipment = $service->ticket?->equipment;
        if (!$equipment || !$equipment->client_id || !$equipment->institution_id || !$service->technician) {
            throw ValidationException::withMessages(['service_id'=>'Completa y verifica equipo, cliente, institución y técnico antes de emitir el informe.']);
        }
        $activities = $service->activities()->where('protocol_include_in_report', true)->orderBy('id')->limit(201)->get();
        if ($activities->count() > 200) { throw ValidationException::withMessages(['service_id' => 'Este borrador admite hasta 200 actividades por informe.']); }
        $size = 0;
        $rows = $activities->map(function ($activity) use (&$size) {
            $photos = [];
            foreach ($activity->photos ?? [] as $index => $path) {
                $image = PrivatePhotos::image($path); $size += strlen($image);
                if ($size > 8 * 1024 * 1024) { throw ValidationException::withMessages(['service_id' => 'Las fotos del informe superan 8 MiB codificados. Reduce su tamaño o cantidad.']); }
                $photos[] = ['src' => $image, 'caption' => $activity->code.' · Foto '.($index + 1)];
            }

            return ['description' => $activity->description, 'result' => $activity->result ?? '', 'photos' => $photos];
        })->all();

        $equipmentData = $equipment->only(['code', 'name', 'brand', 'model', 'serial', 'location', 'status']);
        $equipmentData['brand'] = $equipment->brandRecord?->name ?? $equipment->brand;
        $equipmentData['model'] = $equipment->modelRecord?->name ?? $equipment->model;
        $equipmentData['manufacturer'] = $equipment->brandRecord?->manufacturer?->name;
        $equipmentData['factory_logo'] = $equipment->brandRecord?->manufacturer?->logo_path;
        $equipmentData['status'] = \App\Modules\Equipment\Configuration\Fields::statuses()[$equipment->status] ?? $equipment->status;

        return ['is_sample' => false, 'report' => ['number' => $number, 'title' => $title, 'date' => now()->format('d/m/Y')],
            'ticket' => ['number' => $service->ticket->code],
            'service' => ['number' => $service->code, 'type' => $service->service_type, 'date' => $service->service_date->format('d/m/Y'), 'summary' => $service->summary],
            'institution' => $equipment->institution->only(['name', 'city', 'contact_email', 'contact_phone']),
            'client' => $equipment->client->only(['name', 'tax_id', 'address', 'email', 'phone']),
            'equipment' => $equipmentData,
            'technician' => ['name' => $service->technician->name], 'activities' => $rows, 'components' => [], 'parts' => [],
            'recommendations' => array_values(array_filter(preg_split('/\R/', $service->recommendations ?? ''), fn ($text) => trim($text) !== ''))];
    }
}
