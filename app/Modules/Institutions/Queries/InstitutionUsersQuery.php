<?php

namespace App\Modules\Institutions\Queries;

use App\Models\Institution;
use App\Models\User;
use App\Modules\Institutions\Configuration\Permissions;
use Illuminate\Support\Facades\Gate;

class InstitutionUsersQuery
{
    public function build(int $institutionId, string $search = '')
    {
        Gate::authorize(Permissions::MANAGE);
        Gate::authorize(Permissions::RELATED_USERS);
        Institution::findOrFail($institutionId);

        return User::query()->where('institution_id', $institutionId)
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->orderBy('name')->orderBy('id');
    }
}
