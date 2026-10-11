<?php

namespace App\Modules\Institutions\Actions;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\Configuration\Permissions;
use App\Modules\Institutions\Configuration\Rules;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class SaveInstitution
{
    public function execute(User $actor, array $data, ?Institution $target = null): Institution
    {
        Gate::forUser($actor)->authorize(Permissions::MANAGE);
        $validated = Validator::make($data, Rules::validation($target))->validate();
        $record = $target ?? new Institution;
        $record->fill($validated)->save();

        return $record;
    }
}
