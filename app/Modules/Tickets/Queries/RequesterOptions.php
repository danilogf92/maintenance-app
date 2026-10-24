<?php
namespace App\Modules\Tickets\Queries;
use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Queries\ContactParents;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
class RequesterOptions
{
    public static function get(string $equipmentId, ?int $ticketId): array
    {
        if (!Permissions::allows('contacts') || !Permissions::allows('equipment') || !$equipmentId) { return []; }
        $equipment = VisibleRecords::query('equipment')->findOrFail($equipmentId);
        $items = Contact::where('active', true)->whereHas('links', fn ($q) => $q->where('active', true)->where(function ($link) use ($equipment) {
            $link->whereRaw('1 = 0');
            if (ContactParents::allowed('client')) { $link->orWhere('client_id', $equipment->client_id); }
            if (ContactParents::allowed('institution')) { $link->orWhere('institution_id', $equipment->institution_id); }
        }))->orderBy('name')->get();
        if ($ticketId) {
            $ticket = VisibleRecords::query('tickets')->findOrFail($ticketId);
            if ($ticket->requester_contact_id && (int)$ticket->equipment_id === (int)$equipmentId && !$items->contains('id', $ticket->requester_contact_id)) {
                if ($contact = Contact::find($ticket->requester_contact_id)) { $items->push($contact); }
            }
        }
        return $items->mapWithKeys(fn ($p) => [$p->id => $p->name.($p->active ? '' : ' (inactivo)').($p->phone ? ' · '.$p->phone : '')])->all();
    }
}
