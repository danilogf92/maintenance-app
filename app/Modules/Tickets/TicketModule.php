<?php

namespace App\Modules\Tickets;

use Livewire\Livewire;
use App\Modules\Tickets\UI\{TicketPage, RelatedServices};
use App\Modules\Tickets\Queries\TicketCacheObserver;

class TicketModule
{
    public static function register(): void
    {
        view()->addNamespace('tickets', __DIR__.'/UI/Views');
        Livewire::component('tickets-page', TicketPage::class);
        Livewire::component('ticket-related-services', RelatedServices::class);
        foreach ([\App\Modules\Tickets\Models\Ticket::class, \App\Modules\Equipment\Models\Equipment::class,
            \App\Models\User::class, \App\Modules\Services\Models\Service::class] as $model) {
            $model::observe(TicketCacheObserver::class);
        }
    }
}
