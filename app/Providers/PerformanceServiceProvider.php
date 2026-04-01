<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\SetCacheHeaders;

class PerformanceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register the cache headers middleware globally
        $this->app['router']->pushMiddlewareToGroup('web', SetCacheHeaders::class);

        // Register performance helpers
        if (file_exists(app_path('Helpers/PerformanceHelpers.php'))) {
            require_once app_path('Helpers/PerformanceHelpers.php');
        }
    }
}
