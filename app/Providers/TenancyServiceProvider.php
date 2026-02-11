<?php

namespace App\Providers;

use App\Console\Commands\TenantMigrate;
use App\Console\Commands\TenantProvision;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->commands([
            TenantProvision::class,
            TenantMigrate::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
