<?php

namespace App\Modules\Clients;

use App\Modules\Clients\UI\{ClientPage, RelatedEquipment};
use Livewire\Livewire;

class ClientModule
{
    public static function register(): void
    {
        view()->addNamespace('clients', __DIR__.'/UI/Views');
        Livewire::component('clients-page', ClientPage::class);
        Livewire::component('client-related-equipment', RelatedEquipment::class);
        \App\Modules\Clients\Models\Client::observe(\App\Modules\Clients\Queries\ClientCacheObserver::class);
        \App\Modules\Equipment\Models\Equipment::observe(\App\Modules\Clients\Queries\ClientCacheObserver::class);
    }
}
