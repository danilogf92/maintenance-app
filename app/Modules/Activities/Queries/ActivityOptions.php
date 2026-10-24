<?php

namespace App\Modules\Activities\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;

class ActivityOptions
{
    public static function services(string $search, string $selected): array
    {
        if (!Permissions::allows('services')) { return []; }
        $query = VisibleRecords::query('services');
        $items = (clone $query)->where(fn ($q) => $q->where('code', 'like', '%'.$search.'%')->orWhere('service_type', 'like', '%'.$search.'%'))
            ->latest('service_date')->limit(30)->get();
        if ($selected && ($service = $query->find($selected)) && !$items->contains('id', $service->id)) { $items->prepend($service); }
        return $items->mapWithKeys(fn ($service) => [$service->id => $service->code.' · '.$service->service_type])->all();
    }

    public static function context(string $serviceId): array
    {
        if (!ctype_digit($serviceId) || !Permissions::allows('services')) { return []; }
        $service = VisibleRecords::query('services')->with('technician')->find($serviceId);
        if (!$service) { return []; }
        $ticket = Permissions::allows('tickets') ? VisibleRecords::query('tickets')->find($service->ticket_id) : null;
        return ['service' => $service, 'technician' => $service->technician,
            'ticket' => $ticket, 'equipment' => $ticket && Permissions::allows('equipment') ? $ticket->equipment : null];
    }
}
