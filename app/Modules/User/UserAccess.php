<?php

namespace App\Modules\User;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

/** Rules shared by the application, administration panel and deletion service. */
class UserAccess
{
    public const ADMIN_ABILITIES = ['panel.access', 'users.manage', 'roles.manage', 'branding.manage', 'institutions.manage'];

    public function isAdministrative(User $user): bool
    {
        return $user->can_access_admin || $user->hasRole('SuperAdmin')
            || $user->getAllPermissions()->pluck('name')->intersect(self::ADMIN_ABILITIES)->isNotEmpty();
    }

    public function assertEditable(User $actor, ?User $target): void
    {
        abort_if(! $actor->hasRole('SuperAdmin') && $target && $this->isAdministrative($target), 403);
    }

    public function rolesFor(User $actor)
    {
        return Role::query()->where('guard_name', 'web')
            ->when(! $actor->hasRole('SuperAdmin'), fn ($query) => $query->where('name', '!=', 'SuperAdmin')
                ->whereDoesntHave('permissions', fn ($permissions) => $permissions->whereIn('name', self::ADMIN_ABILITIES)));
    }

    public function assertAssignment(User $actor, ?User $target, array $data, Collection $roles): void
    {
        $this->assertEditable($actor, $target);
        if (! $actor->hasRole('SuperAdmin')) {
            $permitted = $this->rolesFor($actor)->pluck('id');
            abort_if($data['can_access_admin'] || $roles->pluck('id')->diff($permitted)->isNotEmpty(), 403);
        }
        $willBeSuper = $roles->contains('name', 'SuperAdmin');
        if ($target?->is($actor) && (! $data['is_active'] || ($actor->hasRole('SuperAdmin') && ! $willBeSuper))) {
            throw ValidationException::withMessages(['is_active' => 'No puede desactivar su cuenta ni retirar su rol SuperAdmin.']);
        }
        if ($target?->hasRole('SuperAdmin') && (! $willBeSuper || ! $data['is_active'])
            && User::role('SuperAdmin')->where('is_active', true)->whereKeyNot($target->id)->doesntExist()) {
            throw ValidationException::withMessages(['role_ids' => 'Debe conservar al menos un SuperAdmin activo.']);
        }
    }
}
