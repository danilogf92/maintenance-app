<?php

namespace App\Modules\Tickets\Queries;

use App\Modules\Workflow\Configuration\{FormFields, Permissions};
use App\Modules\Workflow\Queries\VisibleRecords;

class TicketOptions
{
    public static function equipment(string $search, string $selected): array
    {
        if (! Permissions::allows('equipment')) { return []; }
        $query = VisibleRecords::query('equipment');
        $items = (clone $query)->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%')->orWhere('serial', 'like', '%'.$search.'%'))
            ->orderBy('name')->limit(30)->get();
        if ($selected && ($record = $query->find($selected)) && ! $items->contains('id', $record->id)) { $items->prepend($record); }
        return $items->mapWithKeys(fn ($record) => [$record->id => $record->code.' · '.$record->name.' · '.($record->serial ?: 'Sin serie')])->all();
    }

    public static function technicians(?int $ticketId): array
    {
        $options = FormFields::technicians();
        if ($ticketId) {
            $ticket = VisibleRecords::query('tickets')->findOrFail($ticketId); $user = $ticket->technician;
            if ($user && ! isset($options[$user->id])) { $options[$user->id] = $user->name.($user->is_active ? '' : ' (inactivo)'); }
        }
        return $options;
    }
}
