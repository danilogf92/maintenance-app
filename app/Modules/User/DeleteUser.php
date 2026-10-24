<?php

namespace App\Modules\User;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeleteUser
{
    public function execute(User $actor, int $id): void
    {
        Gate::forUser($actor)->authorize('users.manage');
        DB::transaction(function () use ($actor, $id) {
            $user = User::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($user->is($actor) || app(UserAccess::class)->isAdministrative($user)) {
                throw ValidationException::withMessages(['deletion' => 'No se permite eliminar tu cuenta ni cuentas administrativas.']);
            }
            if (\App\Modules\Tickets\Models\Ticket::where('technician_id', $id)->exists() || \App\Modules\Services\Models\Service::where('technician_id', $id)->exists()) {
                throw ValidationException::withMessages(['deletion' => 'El usuario tiene historial técnico. Desactívalo en vez de eliminarlo.']);
            }
            $user->delete();
        });
    }
}
