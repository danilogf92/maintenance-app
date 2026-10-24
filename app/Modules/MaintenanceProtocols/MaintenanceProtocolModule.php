<?php
namespace App\Modules\MaintenanceProtocols;
class MaintenanceProtocolModule
{
    public static function register(): void
    {
        view()->addNamespace('maintenance-protocols', __DIR__.'/UI/Views');
        \Livewire\Livewire::component('maintenance-protocols-page', UI\ProtocolPage::class);
        \Livewire\Livewire::component('service-protocols', UI\ServiceProtocols::class);
    }
}
