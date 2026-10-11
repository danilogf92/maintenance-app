<?php

namespace App\Modules\Institution;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\Actions\DeleteInstitution;
use App\Modules\Institutions\Actions\SaveInstitution;

/** Compatibility facade for Filament; edit rules in Modules/Institutions. */
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
