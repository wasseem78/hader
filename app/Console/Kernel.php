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
        // Poll all active devices every 5 minutes to check connectivity
        $schedule->command('attendance:poll-devices')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/poll-devices.log'));

        // Process pending attendance records every 15 minutes
        $schedule->command('attendance:process')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/process-attendance.log'));

        // Clear old processed logs (retention policy)
        $schedule->command('model:prune', ['--model' => 'App\Models\AttendanceRecord'])
            ->daily()
            ->at('02:00');

        // Sync device time daily at midnight
        $schedule->call(function () {
            \App\Models\Device::active()
                ->where('sync_time', true)
                ->chunk(10, function ($devices) {
                    foreach ($devices as $device) {
                        dispatch(new \App\Jobs\SyncDeviceTime($device));
                    }
                });
        })->dailyAt('00:00');

        // Generate daily attendance report
        $schedule->command('report:daily-attendance')
            ->dailyAt('06:00')
            ->runInBackground();

        // Clean up expired API tokens weekly
        $schedule->call(function () {
            \App\Models\ApiToken::where('expires_at', '<', now())
                ->delete();
        })->weekly();

        // Mark stale push devices as offline (every 3 minutes)
        $schedule->command('push:mark-stale --threshold=5')
            ->everyThreeMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/push-stale-check.log'));

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
