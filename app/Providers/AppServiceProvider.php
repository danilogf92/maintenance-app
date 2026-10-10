<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(fn ($user, $ability) => ! $user->is_active ? false : ($user->hasRole('SuperAdmin') ? true : null));
    }
}
