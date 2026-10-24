<?php
namespace App\Modules\Contacts\Actions;

use App\Modules\Contacts\Models\Contact;
use App\Modules\Contacts\Configuration\{Rules, Permissions};
use Illuminate\Support\Facades\{DB, Validator};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaveContact
{
    public function execute(array $input, ?int $id = null, array $parent = []): Contact
    {
        Permissions::authorize('manage'); $data = Validator::make($input, Rules::make())->validate();
        $data['name'] = trim($data['name']);
        if ($data['name'] === '') { throw ValidationException::withMessages(['name' => 'Escribe el nombre del contacto.']); }
        return DB::transaction(function () use ($data, $id, $parent) {
            $old = $id ? Contact::lockForUpdate()->findOrFail($id) : null;
            if ($old && $old->lock_version !== (int)$data['lock_version']) {
                throw ValidationException::withMessages(['lock_version' => 'Otro usuario modificó este contacto. Recarga antes de guardar.']);
            }
            $record = $old ?? new Contact(['code' => 'CTO-'.Str::upper(Str::random(8)), 'created_by' => auth()->id()]);
            $record->fill($data); $record->lock_version = ($old?->lock_version ?? 0) + 1; $record->save();
            if (!$record->active) {
                $record->links()->where('is_primary', true)->update(['is_primary' => false, 'primary_key' => null,
                    'lock_version' => DB::raw('lock_version + 1'), 'updated_at' => now()]);
            }
            if (!$id && $parent) { app(SaveContactLink::class)->execute($record->id, $parent['type'], $parent['id'], ['category' => 'other', 'is_primary' => false]); }
            return $record->fresh();
        });
    }
}
