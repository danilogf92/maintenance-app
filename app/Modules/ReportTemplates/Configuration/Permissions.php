<?php

namespace App\Modules\ReportTemplates\Configuration;

use App\Models\User;
use Filament\Facades\Filament;

class Permissions
{
    public static function allows(?User $user): bool
    {
        return $user && $user->is_active && $user->canAccessPanel(Filament::getPanel('admin'))
            && ($user->hasRole('SuperAdmin') || $user->can('report-templates.manage'));
    }

    public static function authorize(): void
    {
        abort_unless(self::allows(auth()->user()), 403);
    }
}
