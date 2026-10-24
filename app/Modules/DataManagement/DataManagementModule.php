<?php
namespace App\Modules\DataManagement;
class DataManagementModule
{
    public static function register(): void { view()->addNamespace('data-management', __DIR__.'/UI/Views'); }
}
