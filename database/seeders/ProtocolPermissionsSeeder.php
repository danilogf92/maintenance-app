<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission, Role};
use Spatie\Permission\PermissionRegistrar;
class ProtocolPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view', 'apply'] as $action) { Permission::findOrCreate('maintenance_protocols.'.$action, 'web'); }
        foreach (['Admin', 'Administrador', 'Engineer', 'Técnico'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo(['maintenance_protocols.view', 'maintenance_protocols.apply']);
        }
        Role::findOrCreate('CEO', 'web')->givePermissionTo('maintenance_protocols.view');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
