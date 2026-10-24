<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ContractPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view', 'manage'] as $action) {
            Permission::findOrCreate('contracts.'.$action, 'web');
        }
        foreach (['Admin', 'Administrador'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo(['contracts.view', 'contracts.manage']);
        }
        foreach (['Técnico', 'Engineer', 'CEO'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo('contracts.view');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
