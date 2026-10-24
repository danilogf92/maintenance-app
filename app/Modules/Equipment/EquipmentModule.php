<?php

namespace App\Modules\Equipment;

use Livewire\Livewire;
use App\Modules\Equipment\UI\{EquipmentPage, RelatedTickets};
use App\Modules\Equipment\Queries\EquipmentCacheObserver;

class EquipmentModule
{
    public static function register(): void
    {
        view()->addNamespace('equipment', __DIR__.'/UI/Views');
        Livewire::component('equipment-page', EquipmentPage::class);
        Livewire::component('equipment-related-tickets', RelatedTickets::class);
        foreach ([\App\Modules\Equipment\Models\Equipment::class, \App\Modules\Clients\Models\Client::class,
            \App\Models\Institution::class, \App\Modules\Tickets\Models\Ticket::class] as $model) {
            $model::observe(EquipmentCacheObserver::class);
        }
    }
}
