<?php
namespace App\Modules\DataManagement\Configuration;
use Filament\Facades\Filament;
class Permissions
{
    public static function allows(string $action = 'view'): bool
    {
        $user = auth()->user();
        return $user && $user->is_active && $user->canAccessPanel(Filament::getPanel('admin')) && $user->can('data_management.'.$action);
    }
    public static function authorize(string $action = 'view'): void { abort_unless(self::allows($action), 403); }
}
