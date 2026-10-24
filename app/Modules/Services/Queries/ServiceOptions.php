<?php

namespace App\Modules\Services\Queries;

use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;

class ServiceOptions
{
    public static function tickets(string $search, string $selected): array
    {
        if (!Permissions::allows('tickets')) { return []; }
        $query = VisibleRecords::query('tickets');
        $items = (clone $query)->where(fn ($q) => $q->where('subject', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%'))
            ->latest('opened_at')->limit(30)->get();
        if ($selected && ($ticket = $query->find($selected)) && !$items->contains('id', $ticket->id)) { $items->prepend($ticket); }
        return $items->mapWithKeys(fn ($ticket) => [$ticket->id => $ticket->code.' · '.$ticket->subject])->all();
    }

    public static function context(string $ticketId, ?int $serviceId): array
    {
        if (!ctype_digit($ticketId) || !Permissions::allows('tickets')) { return []; }
        $ticket = VisibleRecords::query('tickets')->with('technician')->find($ticketId);
        if (!$ticket) { return []; }
        $technician = $ticket->technician;
        if ($serviceId) {
            $service = VisibleRecords::query('services')->with('technician')->findOrFail($serviceId);
            if ((string)$service->ticket_id === $ticketId) { $technician = $service->technician; }
        }
        return ['ticket' => $ticket, 'technician' => $technician,
            'equipment' => Permissions::allows('equipment') ? $ticket->equipment : null];
    }
}
