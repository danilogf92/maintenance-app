<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MaintenanceRolesV29Seeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';
        $roles = [
            'SuperAdmin' => [], // Existing superadmin bypass is preserved; no role permission reset.
            'Administrador' => ['panel.access', 'users.manage', 'institutions.manage'],
            'Ingeniero' => ['equipment.view', 'equipment.create', 'equipment.update', 'tickets.create', 'tickets.manage', 'maintenance.manage', 'reports.technical.view'],
            'Asesor comercial' => ['equipment.view', 'tickets.create', 'clients.view', 'clients.manage', 'reports.commercial.view'],
            'Usuario' => ['tickets.create', 'tickets.own.view'],
            'CEO' => ['equipment.view', 'clients.view', 'reports.view'],
        ];
        foreach ($roles as $name => $permissions) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
            foreach ($permissions as $permission) {
                $p = Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
                if (! $role->hasPermissionTo($p)) {
                    $role->givePermissionTo($p);
                }
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
