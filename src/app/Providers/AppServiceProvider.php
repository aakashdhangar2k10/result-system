<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //

        // access given through groups
        Gate::before(function ($user, string $ability) {
            return $user instanceof User && $user->groupPermissionNames()->contains($ability) ? true : null;
        });
    }
}
