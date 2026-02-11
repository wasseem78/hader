<?php

// =============================================================================
// ImportFromDevice Command - Fetch attendance logs from a specific device
// =============================================================================

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Device;
use App\Services\ZKTeco\ZKTecoClient;
use Illuminate\Console\Command;

class ImportFromDevice extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:import-from-device
                            {device : Device UUID to import from}
                            {--since= : Only import logs since this date (Y-m-d)}
                            {--dry-run : Show what would be imported without saving}';

    /**
     * The console command description.
     */
    protected $description = 'Import attendance logs from a specific ZKTeco device';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $device = Device::where('uuid', $this->argument('device'))->first();

        if (!$device) {
            $this->error('Device not found.');
            return Command::FAILURE;
        }

        $this->info("Connecting to device: {$device->name} ({$device->ip_address}:{$device->port})");

        $client = new ZKTecoClient($device);

        if (!$client->connect()) {
            $this->error('Failed to connect to device.');
            return Command::FAILURE;
        }

        $this->info('Connected successfully. Fetching attendance logs...');

        // Determine since date
        $since = null;
        if ($this->option('since')) {
            $since = new \DateTime($this->option('since'));
        } elseif ($device->last_sync) {
            $since = $device->last_sync;
        }

        $logs = $client->getAttendanceLogs($since);

        if (empty($logs)) {
            $this->warn('No new attendance logs found.');
            $client->disconnect();
            return Command::SUCCESS;
        }

        $this->info("Found " . count($logs) . " attendance record(s).");

        if ($this->option('dry-run')) {
            $this->table(
                ['User ID', 'Timestamp', 'Type', 'Verification'],
                array_map(fn($log) => [
                    $log['user_id'] ?? '-',
                    $log['timestamp'] ?? '-',
                    $log['type'] ?? '-',
                    $log['verification'] ?? '-',
                ], array_slice($logs, 0, 20))
            );
            $this->warn('Dry run mode - no records imported.');
            $client->disconnect();
            return Command::SUCCESS;
        }

        $progressBar = $this->output->createProgressBar(count($logs));
        $progressBar->start();

        $imported = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            // Find user by device_user_id
            $user = $device->company->users()
                ->where('device_user_id', $log['user_id'])
                ->first();

            if (!$user) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            // Check for duplicate
            $exists = AttendanceRecord::where('device_id', $device->id)
                ->where('device_record_id', $log['record_id'] ?? null)
                ->exists();

            if ($exists) {
                $skipped++;
                $progressBar->advance();
                continue;
            }

            $punchedAt = new \DateTime($log['timestamp']);

            AttendanceRecord::create([
                'company_id' => $device->company_id,
                'user_id' => $user->id,
                'device_id' => $device->id,
                'punched_at' => $punchedAt,
                'punch_date' => $punchedAt->format('Y-m-d'),
                'punch_time' => $punchedAt->format('H:i:s'),
                'type' => $log['type'] ?? 'in',
                'verification_type' => $log['verification'] ?? null,
                'device_record_id' => $log['record_id'] ?? null,
                'status' => 'pending',
                'raw_data' => $log,
            ]);

            $imported++;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Update device sync status
        $device->markSyncComplete($imported);

        $client->disconnect();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported', $imported],
                ['Skipped', $skipped],
                ['Total', count($logs)],
            ]
        );

        $this->info('Import completed successfully.');

        return Command::SUCCESS;
    }
}
