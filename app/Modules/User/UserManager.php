<?php

namespace App\Modules\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

class UserManager
{
    public function __construct(private UserData $validator, private UserAccess $access) {}

    public function save(User $actor, array $data, ?User $target = null): User
    {
        Gate::forUser($actor)->authorize('users.manage');

        return DB::transaction(function () use ($actor, $data, $target) {
            // Serialize changes to the protected accounts, not the whole users table.
            User::role('SuperAdmin')->orderBy('id')->lockForUpdate()->get(['users.id']);
            $target = $target ? User::whereKey($target->id)->lockForUpdate()->firstOrFail() : null;
            $validated = $this->validator->validate($data, $target);
            $roles = Role::where('guard_name', 'web')->whereIn('id', $validated['role_ids'])->get();
            $this->access->assertAssignment($actor, $target, $validated, $roles);
            unset($validated['role_ids']);
            if (empty($validated['password'])) {
                unset($validated['password']);
            }
            $user = $target ?? new User;
            $user->fill($validated)->save();
            $user->syncRoles($roles);

            return $user;
        });
    }
}
