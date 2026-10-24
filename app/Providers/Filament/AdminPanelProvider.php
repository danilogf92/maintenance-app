<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\EnsureUserIsActive;
use App\Models\BrandSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('admin')->path('admin')->login(Login::class)
            ->colors(['primary'=>\Filament\Support\Colors\Color::hex(config('identity.admin_primary'))])
            ->renderHook(PanelsRenderHook::HEAD_END, fn()=>view('filament.admin-identity'))
            ->sidebarCollapsibleOnDesktop()->sidebarFullyCollapsibleOnDesktop()
            ->brandName(fn () => BrandSetting::displayName().' · Administración')->brandLogo(fn () => BrandSetting::logoUrl() ?? asset('branding/maintenance/maintenance-logo-light.svg'))
            ->darkModeBrandLogo(fn () => BrandSetting::logoUrl() ?? asset('branding/maintenance/maintenance-logo-dark.svg'))
            ->brandLogoHeight('2.5rem')->favicon(fn () => asset('branding/maintenance/admin-favicon.svg').'?v=11.60.0')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([\App\Modules\ReportTemplates\UI\ReportTemplateResource::class])
            ->pages([Dashboard::class, \App\Modules\DataManagement\UI\DataManagementPage::class])
            ->widgets([AccountWidget::class])
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.application-link'))
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class,
                StartSession::class, AuthenticateSession::class,
                ShareErrorsFromSession::class, VerifyCsrfToken::class,
                SubstituteBindings::class, DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])->authMiddleware([Authenticate::class, EnsureUserIsActive::class], isPersistent: true);
    }
}
