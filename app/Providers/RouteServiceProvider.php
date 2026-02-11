<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // ICLOCK / ADMS protocol routes — no prefix, no CSRF
            // Device sends to: /iclock/cdata?SN=xxx (not /api/iclock/...)
            Route::middleware('api')
                ->group(base_path('routes/iclock.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
