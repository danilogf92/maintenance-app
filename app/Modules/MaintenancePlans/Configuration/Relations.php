<?php
namespace App\Modules\MaintenancePlans\Configuration;
class Relations
{
    public static function definitions(): array
    {
        return ['maintenance_visits' => ['allowed' => Permissions::allows(), 'component' => 'maintenance-plan-visits', 'key' => 'planId']];
    }
}
