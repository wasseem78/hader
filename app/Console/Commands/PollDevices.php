<?php

// =============================================================================
// PollDevices Command - Check connectivity of all active devices
// =============================================================================

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\ZKTeco\ZKTecoClient;
use Illuminate\Console\Command;

class PollDevices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:poll-devices
                            {--company= : Only poll devices for specific company ID}
                            {--device= : Only poll a specific device UUID}';

    /**
     * The console command description.
     */
    protected $description = 'Poll ZKTeco devices to check connectivity status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Device::query()->active()->with('company');

        if ($companyId = $this->option('company')) {
            $query->where('company_id', $companyId);
        }

        if ($deviceUuid = $this->option('device')) {
            $query->where('uuid', $deviceUuid);
        }

        $devices = $query->get();

        if ($devices->isEmpty()) {
            $this->warn('No devices found to poll.');
            return Command::SUCCESS;
        }

        $this->info("Polling {$devices->count()} device(s)...");

        $progressBar = $this->output->createProgressBar($devices->count());
        $progressBar->start();

        $online = 0;
        $offline = 0;

        foreach ($devices as $device) {
            $client = new ZKTecoClient($device);
            $result = $client->testConnection();

            if ($result['success']) {
                $online++;
                $this->line(" ✓ {$device->name} - Online (Latency: {$result['latency_ms']}ms)");
            } else {
                $offline++;
                $this->error(" ✗ {$device->name} - Offline");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Status', 'Count'],
            [
                ['Online', $online],
                ['Offline', $offline],
                ['Total', $devices->count()],
            ]
        );

        return Command::SUCCESS;
    }
}
