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
    public function register(): void
    {
        $this->app->scoped(\App\Support\Cache\PageDataCache::class);
    }

    public function boot(): void
    {
        InstitutionModule::register();
        \App\Modules\DataManagement\DataManagementModule::register();
        \App\Modules\MaintenanceProtocols\MaintenanceProtocolModule::register();
        \App\Modules\Contracts\ContractsModule::register();
        \App\Modules\FieldEngineers\FieldEngineerModule::register();
        \App\Modules\MaintenancePlans\MaintenancePlanModule::register();
        \App\Modules\Contacts\ContactsModule::register();
        \App\Modules\Catalogs\CatalogModule::register();
        \App\Modules\TechnicalReports\TechnicalReportModule::register();
        \App\Modules\Clients\ClientModule::register();
        \App\Modules\Equipment\EquipmentModule::register();
        \App\Modules\Tickets\TicketModule::register();
        \App\Modules\Services\ServiceModule::register();
        \App\Modules\Activities\ActivityModule::register();
        view()->addNamespace('users', app_path('Modules/Users/UI/Views'));
        \Livewire\Livewire::component('workflow-operations', \App\Modules\Workflow\UI\OperationsPage::class);
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
