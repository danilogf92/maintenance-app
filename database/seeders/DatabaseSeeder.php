<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['panel.access', 'users.manage', 'institutions.manage', 'branding.manage', 'roles.manage'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
        foreach (['SuperAdmin', 'Administrador', 'Técnico', 'Cliente', 'Instructor', 'Marketing'] as $name) {
            Role::findOrCreate($name, 'web');
        }
        // Idempotente: agregar el mínimo del administrador sin borrar permisos personalizados.
        Role::findByName('Administrador', 'web')->givePermissionTo(['panel.access', 'users.manage', 'institutions.manage']);
        $this->call(ReportTemplatesSeeder::class);
    }
}
