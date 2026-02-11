<?php

// =============================================================================
// Artisan Command: Mark stale push devices as offline
// Runs periodically to detect push devices that stopped sending heartbeats.
// A device is considered stale if no data received in 5 minutes.
// =============================================================================

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class MarkStalePushDevicesOffline extends Command
{
    protected $signature = 'push:mark-stale
                            {--threshold=5 : Minutes since last push before marking offline}
                            {--dry-run : Show what would be marked without making changes}';

    protected $description = 'Mark push-mode devices as offline if no heartbeat received recently';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $dryRun = $this->option('dry-run');
        $cutoff = now()->subMinutes($threshold);

        $this->info("Checking for push devices inactive since {$cutoff}...");

        // Get all active push registrations
        $registries = DB::connection('mysql')
            ->table('push_device_registry')
            ->where('is_active', true)
            ->get();

        if ($registries->isEmpty()) {
            $this->info('No active push devices registered.');
            return 0;
        }

        $markedOffline = 0;
        $alreadyOffline = 0;
        $stillActive = 0;
        $errors = 0;

        // Group by tenant for efficiency
        $grouped = $registries->groupBy('tenant_id');

        foreach ($grouped as $tenantId => $devices) {
            $tenant = DB::connection('mysql')
                ->table('tenants')
                ->where('id', $tenantId)
                ->where('is_active', true)
                ->first();

            if (!$tenant) {
                continue;
            }

            try {
                $connName = 'stale_check_' . $tenant->id;
                $dbUser = Crypt::decryptString($tenant->db_username_enc);
                $dbPass = Crypt::decryptString($tenant->db_password_enc);

                config(["database.connections.{$connName}" => [
                    'driver' => 'mysql',
                    'host' => $tenant->db_host,
                    'port' => $tenant->db_port,
                    'database' => $tenant->db_name,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ]]);

                foreach ($devices as $reg) {
                    $device = DB::connection($connName)
                        ->table('devices')
                        ->where('id', $reg->device_id)
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$device) {
                        continue;
                    }

                    // Check if connection_mode is push
                    if (($device->connection_mode ?? 'pull') !== 'push') {
                        continue;
                    }

                    // Check if already offline
                    if ($device->status === 'offline') {
                        $alreadyOffline++;
                        continue;
                    }

                    // Check last activity
                    $lastSeen = $device->last_push_received ?? $device->last_seen;
                    if ($lastSeen && $lastSeen > $cutoff->toDateTimeString()) {
                        $stillActive++;
                        continue;
                    }

                    // Mark offline
                    if (!$dryRun) {
                        DB::connection($connName)
                            ->table('devices')
                            ->where('id', $device->id)
                            ->update([
                                'status' => 'offline',
                                'updated_at' => now(),
                            ]);
                    }

                    $markedOffline++;
                    $this->line("  [{$reg->serial_number}] {$device->name} → offline (last seen: {$lastSeen})");
                }

                // Reset push_records_today at midnight
                if (now()->hour === 0 && now()->minute < 6) {
                    DB::connection($connName)
                        ->table('devices')
                        ->where('connection_mode', 'push')
                        ->where('push_records_today', '>', 0)
                        ->update(['push_records_today' => 0]);
                }

            } catch (\Exception $e) {
                $errors++;
                $this->error("Tenant {$tenantId}: {$e->getMessage()}");
            }
        }

        $prefix = $dryRun ? '[DRY RUN] ' : '';
        $this->info("{$prefix}Results: {$markedOffline} marked offline, {$stillActive} still active, {$alreadyOffline} already offline, {$errors} errors");

        if ($markedOffline > 0) {
            Log::info('Push stale check: marked devices offline', [
                'count' => $markedOffline,
                'threshold_minutes' => $threshold,
            ]);
        }

        return 0;
    }
}
