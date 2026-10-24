<?php
namespace App\Modules\MaintenancePlans\Configuration;
class Permissions
{
    public static function allows(string $action = 'view'): bool
    {
        return \App\Modules\Workflow\Configuration\Permissions::allows('maintenance_plans', $action)
            && \App\Modules\Workflow\Configuration\Permissions::allows('equipment');
    }
    public static function authorize(string $action = 'view'): void { abort_unless(self::allows($action), 403); }
}
