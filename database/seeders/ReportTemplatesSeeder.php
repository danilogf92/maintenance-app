<?php

namespace Database\Seeders;

use App\Modules\ReportTemplates\Configuration\Blocks;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ReportTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Permission::findOrCreate('report-templates.manage', 'web');
        $role = Role::where('name', 'Administrador')->where('guard_name', 'web')->first();
        $role?->givePermissionTo('report-templates.manage');
        ReportTemplate::firstOrCreate(['name' => 'Informe de mantenimiento'], ['definition' => Blocks::defaults(),
            'description' => 'Formato inicial configurable por bloques.']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
