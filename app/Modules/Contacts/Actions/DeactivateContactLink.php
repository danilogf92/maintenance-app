<?php
namespace App\Modules\Contacts\Actions;

use App\Modules\Contacts\Models\{Contact, ContactLink};
use App\Modules\Contacts\Configuration\Permissions;
use App\Modules\Contacts\Queries\ContactParents;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeactivateContactLink
{
    public function execute(int $id, int $version): void
    {
        Permissions::authorize('manage');
        DB::transaction(function () use ($id, $version) {
            $initial = ContactLink::findOrFail($id); Contact::lockForUpdate()->findOrFail($initial->contact_id);
            ContactParents::find($initial->parentType(), $initial->parentId(), true);
            $link = ContactLink::lockForUpdate()->findOrFail($id);
            if ($link->lock_version !== $version) { throw ValidationException::withMessages(['link' => 'El vínculo cambió. Revisa sus datos antes de desvincular.']); }
            $link->update(['active' => false, 'is_primary' => false, 'primary_key' => null, 'lock_version' => $version + 1]);
        });
    }
}
