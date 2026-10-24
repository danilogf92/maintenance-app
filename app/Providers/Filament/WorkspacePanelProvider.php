<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Http\Middleware\EnsureUserIsActive;
use App\Models\BrandSetting;
use App\Modules\Workflow\UI\Resources;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WorkspacePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->id('workspace')->path('workspace')->login(Login::class)
            ->brandName(fn () => BrandSetting::displayName())->brandLogo(fn () => BrandSetting::logoUrl())
            ->sidebarCollapsibleOnDesktop()->resources(array_values(Resources::all()))
            ->pages([\App\Modules\Workflow\UI\WorkflowDashboard::class])
            ->renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.application-link'))
            ->middleware([EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class,
                \Filament\Http\Middleware\AuthenticateSession::class, ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class])
            ->middleware([\App\Modules\Workflow\UI\SetLocale::class], isPersistent: true)
            ->authMiddleware([\Filament\Http\Middleware\Authenticate::class, EnsureUserIsActive::class], isPersistent: true);
    }
}
