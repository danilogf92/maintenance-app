<?php

namespace App\Modules\Institutions\Actions;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\Configuration\Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeleteInstitution
{
    public function execute(User $actor, int $id): void
    {
        Gate::forUser($actor)->authorize(Permissions::MANAGE);
        DB::transaction(function () use ($id) {
            $record = Institution::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($record->users()->exists()) {
                throw ValidationException::withMessages(['deletion' => 'No se pueden eliminar instituciones que tengan usuarios asociados.']);
            }
            $record->delete();
        });
    }
}
