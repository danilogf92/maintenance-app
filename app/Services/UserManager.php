<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserManager
{
    /** Único punto de escritura del formulario: valida datos y roles antes de modificar. */
    public function save(User $actor, array $data, ?User $target = null): User
    {
        Gate::forUser($actor)->authorize('users.manage');

        return DB::transaction(function () use ($actor, $data, $target) {
            // Serializa administración de cuentas para proteger el último superadministrador.
            User::orderBy('id')->lockForUpdate()->get(['id']);
            $target = $target ? User::findOrFail($target->id) : null;
            $super = $actor->hasRole('SuperAdmin');
            $validated = Validator::make($data, [
                'name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($target)],
                'institution_id' => 'required|exists:institutions,id', 'identification' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:255', 'job_title' => 'nullable|string|max:255',
                'password' => [$target ? 'nullable' : 'required', Password::min(12)],
                'is_active' => 'required|boolean', 'can_access_admin' => 'required|boolean',
                'role_ids' => 'present|array', 'role_ids.*' => 'integer|exists:roles,id',
            ])->validate();
            $roles = Role::whereIn('id', $validated['role_ids'])->get();
            if (! $super) {
                abort_if($target && ($target->hasRole('SuperAdmin') || $target->can_access_admin), 403);
                // Un administrador ordinario solo asigna roles sin permisos administrativos.
                abort_if($validated['can_access_admin'] || $roles->contains(fn ($r) => $r->name === 'SuperAdmin' || $r->permissions()->whereIn('name', ['panel.access', 'users.manage', 'roles.manage', 'branding.manage', 'institutions.manage'])->exists()), 403);
            }
            $willBeSuper = $roles->contains('name', 'SuperAdmin');
            if ($target?->is($actor) && (! $validated['is_active'] || ($super && ! $willBeSuper))) {
                throw ValidationException::withMessages(['is_active' => 'No puede desactivar su propia cuenta ni retirar su rol SuperAdmin.']);
            }
            if ($target?->hasRole('SuperAdmin') && (! $willBeSuper || ! $validated['is_active']) && User::role('SuperAdmin')->where('is_active', true)->where('id', '!=', $target->id)->doesntExist()) {
                throw ValidationException::withMessages(['role_ids' => 'Debe conservar al menos un SuperAdmin activo.']);
            }
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
