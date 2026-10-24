<?php
namespace App\Modules\MaintenancePlans;
use Livewire\Livewire;
class MaintenancePlanModule
{
    public static function register(): void
    {
        view()->addNamespace('maintenance-plans', __DIR__.'/UI/Views');
        Livewire::component('maintenance-calendar', UI\CalendarPage::class);
        Livewire::component('maintenance-plans-page', UI\PlanPage::class);
        Livewire::component('maintenance-plan-visits', UI\PlanVisits::class);
        Livewire::component('equipment-maintenance-plans', UI\EquipmentPlans::class);
    }
}
