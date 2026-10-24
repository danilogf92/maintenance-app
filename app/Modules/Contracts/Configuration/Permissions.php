<?php

namespace App\Modules\Contracts\Configuration;

class Permissions
{
    public static function allows(string $action = 'view'): bool
    {
        return \App\Modules\Workflow\Configuration\Permissions::allows('contracts', $action)
            && \App\Modules\MaintenancePlans\Configuration\Permissions::allows();
    }

    public static function authorize(string $action = 'view'): void
    {
        abort_unless(self::allows($action), 403);
    }
}
