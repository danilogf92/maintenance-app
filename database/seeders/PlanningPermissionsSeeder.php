<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission, Role};
class PlanningPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view', 'manage'] as $action) { Permission::findOrCreate('maintenance_plans.'.$action, 'web'); }
        foreach (['Admin', 'Administrador'] as $role) { Role::findOrCreate($role, 'web')->givePermissionTo(['maintenance_plans.view', 'maintenance_plans.manage']); }
        foreach (['Técnico', 'Engineer', 'CEO'] as $role) { Role::findOrCreate($role, 'web')->givePermissionTo('maintenance_plans.view'); }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
