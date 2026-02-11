<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Jobs\FetchDeviceLogsJob;
use Illuminate\Console\Command;

class PollDevicesCommand extends Command
{
    protected $signature = 'attendance:poll-devices';
    protected $description = 'Poll attendance logs from all active devices';

    public function handle()
    {
        $this->info('Starting device polling...');

        // Get all active devices that are online or due for sync
        // For simplicity, we'll poll all active devices
        $devices = Device::active()->get();

        foreach ($devices as $device) {
            $this->info("Dispatching sync for device: {$device->name} ({$device->ip_address})");
            
            FetchDeviceLogsJob::dispatch($device)
                ->onQueue('attendance-polling');
        }

        $this->info('Polling jobs dispatched successfully.');
    }
}
