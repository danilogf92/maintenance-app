<?php
namespace App\Modules\Tickets\Actions;
use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Queries\ContactParents;
use App\Modules\Workflow\Configuration\Permissions;
use App\Modules\Workflow\Queries\VisibleRecords;
use Illuminate\Validation\ValidationException;
class TicketOriginValues
{
    public function execute(array $data, $old): array
    {
        $data['origin'] = $data['origin'] ?? $old?->origin ?? 'internal';
        if ($old?->maintenance_visit_id) {
            if ($data['origin'] !== 'scheduled' || (int)$data['equipment_id'] !== (int)$old->equipment_id) {
                throw ValidationException::withMessages(['origin' => 'El ticket conserva su origen y equipo de la visita programada.']);
            }
        } elseif ($data['origin'] === 'scheduled') {
            throw ValidationException::withMessages(['origin' => 'Genera este ticket desde una visita programada.']);
        }
        if ($data['origin'] === 'legacy' && (!$old || $old->origin !== 'legacy')) {
            throw ValidationException::withMessages(['origin' => 'Elige un origen para el nuevo reporte.']);
        }
        $selected = $data['requester_contact_id'] ?? $old?->requester_contact_id;
        if (array_key_exists('requester_contact_id', $data) && !$data['requester_contact_id']) { $selected = null; }
        $data['requester_contact_id'] = $selected ? (int)$selected : null;
        if (!$selected) { return $data; }
        if ($old && (int)$selected === (int)$old->requester_contact_id && (int)$old->equipment_id === (int)$data['equipment_id']) { return $data; }
        Permissions::authorize('contacts'); $equipment = VisibleRecords::query('equipment')->findOrFail($data['equipment_id']);
        $contact = Contact::where('active', true)->findOrFail($selected);
        $valid = $contact->links()->where('active', true)->where(function ($q) use ($equipment) {
            $q->whereRaw('1 = 0');
            if (ContactParents::allowed('client')) { $q->orWhere('client_id', $equipment->client_id); }
            if (ContactParents::allowed('institution')) { $q->orWhere('institution_id', $equipment->institution_id); }
        })->exists();
        if (!$valid) { throw ValidationException::withMessages(['requester_contact_id' => 'Selecciona un contacto activo vinculado al cliente o institución del equipo.']); }
        return $data;
    }
}
