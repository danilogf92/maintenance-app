<?php

namespace App\Modules\Institutions\Actions;

use App\Models\Institution;
use App\Models\User;

/** Entry point used by Filament; delegates to the same module actions. */
class InstitutionManager
{
    public function save(User $actor, array $data, ?Institution $target = null): Institution
    {
        return app(SaveInstitution::class)->execute($actor, $data, $target);
    }

    public function delete(User $actor, int $id): void
    {
        app(DeleteInstitution::class)->execute($actor, $id);
    }
}
