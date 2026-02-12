<?php

// =============================================================================
// Kernel - Console Commands Registration and Scheduling
// =============================================================================

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Mark stale push devices as offline (every 3 minutes)
        $schedule->command('push:mark-stale --threshold=5')
            ->everyThreeMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/push-stale-check.log'));

        // Process pending attendance records every 15 minutes
        // NOTE: attendance:process currently runs on default connection only.
        // For multi-tenant, use tenant:migrate wrapper or iterate tenants.
        // $schedule->command('attendance:process')
        //     ->everyFifteenMinutes()
        //     ->withoutOverlapping()
        //     ->runInBackground()
        //     ->appendOutputTo(storage_path('logs/process-attendance.log'));

        // Clean up expired API tokens weekly
        $schedule->call(function () {
            // This runs against the default DB. In multi-tenant setup,
            // you would iterate tenants. Kept as a reminder.
        })->weekly();

        // Check for trial expirations daily
        $schedule->call(function () {
            \App\Models\Company::where('trial_ends_at', '<', now())
                ->whereNull('stripe_subscription_id')
                ->chunk(50, function ($companies) {
                    foreach ($companies as $company) {
                        // TODO: Send trial expired notification
                    }
                });
        })->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
