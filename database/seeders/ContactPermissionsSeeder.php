<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Permission, Role};

class ContactPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['view', 'manage'] as $action) { Permission::findOrCreate('contacts.'.$action, 'web'); }
        $views = ['contacts.view']; $manage = ['contacts.manage'];
        foreach (['Administrador', 'Admin'] as $name) { Role::findOrCreate($name, 'web')->givePermissionTo([...$views, ...$manage]); }
        foreach (['Técnico', 'Engineer', 'CEO'] as $name) { Role::findOrCreate($name, 'web')->givePermissionTo($views); }
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
