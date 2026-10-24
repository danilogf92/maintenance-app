<?php

namespace App\Modules\Workflow\Configuration;

use Illuminate\Support\Facades\Gate;

class Permissions
{
    public static function allows(string $module, string $action = 'view'): bool
    {
        return auth()->check() && auth()->user()->is_active && Gate::allows('workflow.access') && Gate::allows($module.'.'.$action);
    }

    public static function authorize(string $module, string $action = 'view'): void
    {
        abort_unless(self::allows($module, $action), 403);
    }
}
