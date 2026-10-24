<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission,Role};
class FieldEngineerPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view','sensitive'] as $action) { Permission::findOrCreate('field_engineers.'.$action,'web'); }
        foreach (['Admin','Administrador','Engineer','Técnico','CEO'] as $name) { Role::findOrCreate($name,'web')->givePermissionTo('field_engineers.view'); }
        foreach (['Admin','Administrador'] as $name) { Role::findOrCreate($name,'web')->givePermissionTo('field_engineers.sensitive'); }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
