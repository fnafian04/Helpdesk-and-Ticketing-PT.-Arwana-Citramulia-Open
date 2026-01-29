<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\BladeHelpers;

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
        // Register custom Blade directives for authentication
        BladeHelpers::register();
    }
}
