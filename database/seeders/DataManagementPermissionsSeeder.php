<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission, Role};
use Spatie\Permission\PermissionRegistrar;
class DataManagementPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view', 'manage'] as $action) { Permission::findOrCreate('data_management.'.$action, 'web'); }
        foreach (['Admin', 'Administrador'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo(['data_management.view', 'data_management.manage']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
