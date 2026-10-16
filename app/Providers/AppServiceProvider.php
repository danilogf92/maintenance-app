<?php

namespace App\Providers;

use App\Models\BrandSetting;
use App\Models\Institution;
use App\Models\UiColorSetting;
use App\Models\User;
use App\Modules\Institutions\InstitutionModule;
use App\Support\Cache\InvalidatesPageData;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        InstitutionModule::register();
        \App\Modules\ReportTemplates\ReportTemplateModule::register();
        foreach ([User::class, Institution::class,
            BrandSetting::class, UiColorSetting::class,
            Role::class] as $model) {
            $model::observe(InvalidatesPageData::class);
        }
        foreach ([RoleAttachedEvent::class,
            RoleDetachedEvent::class,
            PermissionAttachedEvent::class,
            PermissionDetachedEvent::class] as $event) {
            Event::listen($event,
                fn () => app(InvalidatesPageData::class)->rolesChanged());
        }
        Gate::before(fn ($user, $ability) => ! $user->is_active ? false : ($user->hasRole('SuperAdmin') ? true : null));
    }
}
