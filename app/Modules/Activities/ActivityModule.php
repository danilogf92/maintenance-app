<?php

namespace App\Modules\Activities;

use Livewire\Livewire;
use App\Modules\Activities\UI\ActivityPage;
use App\Modules\Activities\Queries\ActivityCacheObserver;

class ActivityModule
{
    public static function register(): void
    {
        view()->addNamespace('activities', __DIR__.'/UI/Views');
        Livewire::component('activities-page', ActivityPage::class);
        foreach ([\App\Modules\Activities\Models\Activity::class, \App\Modules\Services\Models\Service::class,
            \App\Modules\Tickets\Models\Ticket::class, \App\Modules\Equipment\Models\Equipment::class, \App\Models\User::class] as $model) {
            $model::observe(ActivityCacheObserver::class);
        }
    }
}
