<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission, Role};

class CatalogPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $views = []; $manage = [];
        foreach (array_keys(\App\Modules\Catalogs\Configuration\Catalogs::all()) as $catalog) {
            foreach (['view', 'manage'] as $action) { Permission::findOrCreate($catalog.'.'.$action, 'web'); }
            $views[] = $catalog.'.view'; $manage[] = $catalog.'.manage';
        }
        foreach (['Administrador', 'Admin'] as $name) { Role::findOrCreate($name, 'web')->givePermissionTo([...$views, ...$manage]); }
        foreach (['Técnico', 'Engineer', 'CEO'] as $name) { Role::findOrCreate($name, 'web')->givePermissionTo($views); }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
