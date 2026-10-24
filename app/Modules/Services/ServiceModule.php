<?php

namespace App\Modules\Services;

use Livewire\Livewire;
use App\Modules\Services\UI\{ServicePage, RelatedActivities, RelatedReports};
use App\Modules\Services\Queries\ServiceCacheObserver;

class ServiceModule
{
    public static function register(): void
    {
        view()->addNamespace('services', __DIR__.'/UI/Views');
        Livewire::component('services-page', ServicePage::class);
        Livewire::component('service-related-activities', RelatedActivities::class);
        Livewire::component('service-related-reports', RelatedReports::class);
        foreach ([\App\Modules\Services\Models\Service::class, \App\Modules\Tickets\Models\Ticket::class,
            \App\Modules\Equipment\Models\Equipment::class, \App\Models\User::class,
            \App\Modules\Activities\Models\Activity::class, \App\Modules\TechnicalReports\Models\TechnicalReport::class] as $model) {
            $model::observe(ServiceCacheObserver::class);
        }
    }
}
