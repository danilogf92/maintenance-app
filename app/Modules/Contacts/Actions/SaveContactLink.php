<?php
namespace App\Modules\Contacts\Actions;

use App\Modules\Contacts\Models\{Contact, ContactLink};
use App\Modules\Contacts\Configuration\{Fields, Permissions};
use App\Modules\Contacts\Queries\ContactParents;
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Validation\{Rule, ValidationException};

class SaveContactLink
{
    public function execute(int $contactId, string $type, int $parentId, array $input, ?int $version = null): ContactLink
    {
        Permissions::authorize('manage');
        $data = Validator::make($input, ['category' => ['required', Rule::in(array_keys(Fields::categories()))], 'is_primary' => 'required|boolean'])->validate();
        return DB::transaction(function () use ($contactId, $type, $parentId, $data, $version) {
            $contact = Contact::lockForUpdate()->findOrFail($contactId);
            $parent = ContactParents::find($type, $parentId, true);
            if (!$contact->active || ($type === 'client' && !$parent->active)) {
                throw ValidationException::withMessages(['link' => 'El contacto y el cliente deben estar activos para vincular o reactivar.']);
            }
            $column = $type === 'client' ? 'client_id' : 'institution_id';
            $old = ContactLink::where('contact_id', $contactId)->where($column, $parentId)->lockForUpdate()->first();
            if ($old && $version === null) { throw ValidationException::withMessages(['link' => 'El vínculo ya existe. Edítalo o reactívalo en la lista.']); }
            if (($old && $old->lock_version !== $version) || (!$old && $version !== null)) {
                throw ValidationException::withMessages(['link' => 'Otro usuario modificó el vínculo. Vuelve a abrirlo antes de guardar.']);
            }
            $key = $type.':'.$parentId;
            if ($data['is_primary']) {
                ContactLink::where('primary_key', $key)->when($old, fn ($q) => $q->whereKeyNot($old->id))
                    ->update(['is_primary' => false, 'primary_key' => null, 'lock_version' => DB::raw('lock_version + 1'), 'updated_at' => now()]);
            }
            $record = $old ?? new ContactLink(['contact_id' => $contactId, $column => $parentId]);
            $record->fill([...$data, 'active' => true, 'primary_key' => $data['is_primary'] ? $key : null]);
            $record->lock_version = ($old?->lock_version ?? 0) + 1; $record->save();
            return $record->fresh();
        });
    }
}
