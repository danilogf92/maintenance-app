<?php
namespace App\Modules\MaintenanceProtocols\Configuration;
class Permissions
{
    public static function allows(string $action = 'view'): bool
    {
        return \App\Modules\Workflow\Configuration\Permissions::allows('maintenance_protocols', $action);
    }
    public static function authorize(string $action = 'view'): void { abort_unless(self::allows($action), 403); }
}
