<?php

namespace Database\Seeders;

use App\Modules\Workflow\Configuration\Modules;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $all = ['workflow.access', 'workflow.view_all']; $views = ['workflow.access', 'workflow.view_all'];
        $technical = ['workflow.access'];
        foreach (array_keys(Modules::all()) as $module) {
            $all[] = $module.'.view'; $all[] = $module.'.manage'; $views[] = $module.'.view';
            $technical[] = $module.'.view';
            if (! in_array($module, ['clients', 'equipment'])) { $technical[] = $module.'.manage'; }
        }
        foreach ($all as $name) { Permission::findOrCreate($name, 'web'); }
        foreach (['Administrador', 'Admin'] as $name) { Role::findOrCreate($name, 'web')->givePermissionTo($all); }
        foreach (['Técnico', 'Engineer'] as $name) { Role::findOrCreate($name, 'web')->givePermissionTo($technical); }
        Role::findOrCreate('CEO', 'web')->givePermissionTo($views);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
