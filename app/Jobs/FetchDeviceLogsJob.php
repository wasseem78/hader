<?php

namespace App\Jobs;

use App\Models\Device;
use App\Models\AttendanceImport;
use App\Services\ZKTeco\ZKTecoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchDeviceLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    protected Device $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    public function handle()
    {
        $client = new ZKTecoClient($this->device);
        
        // Fetch logs since last sync or all
        $logs = $client->fetchAttendanceLogs();

        if (empty($logs)) {
            Log::info("No new logs for device: {$this->device->name}");
            return;
        }

        // Save raw logs
        $import = AttendanceImport::create([
            'device_id' => $this->device->id,
            'raw_data' => $logs,
            'status' => 'pending',
        ]);

        // Dispatch processing job
        ProcessAttendanceLogsJob::dispatch($import)
            ->onQueue('attendance-processing');
            
        // Update device stats
        $this->device->markSyncComplete(count($logs));
    }
}
