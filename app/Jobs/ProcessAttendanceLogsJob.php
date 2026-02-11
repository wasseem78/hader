<?php

namespace App\Jobs;

use App\Events\AttendanceProcessed;
use App\Models\AttendanceImport;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessAttendanceLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected AttendanceImport $import;

    public function __construct(AttendanceImport $import)
    {
        $this->import = $import;
    }

    public function handle()
    {
        $logs = $this->import->raw_data;
        $device = $this->import->device;
        $companyId = $device->company_id;

        DB::beginTransaction();

        try {
            foreach ($logs as $log) {
                // Find user by device_user_id
                $user = User::where('company_id', $companyId)
                    ->where('device_user_id', $log['user_id'])
                    ->first();

                if (!$user) {
                    Log::warning("User not found for device user id: {$log['user_id']} in company: {$companyId}");
                    continue;
                }
                
                $timestamp = Carbon::parse($log['timestamp']);
                $type = $this->mapLogType($log['state']);
                
                // Deduplication check
                $exists = AttendanceRecord::where('device_id', $device->id)
                    ->where('device_record_id', $log['uid'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $record = AttendanceRecord::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'device_id' => $device->id,
                    'device_record_id' => $log['uid'],
                    'punched_at' => $timestamp,
                    'type' => $type,
                    'status' => 'pending',
                    'raw_data' => $log,
                ]);

                // Dispatch event for real-time updates
                event(new AttendanceProcessed($record));
            }

            $this->import->update(['status' => 'processed']);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->import->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            Log::error("Failed to process attendance import {$this->import->id}: " . $e->getMessage());
            $this->fail($e);
        }
    }

    protected function mapLogType($state)
    {
        return match ((int)$state) {
            0, 4 => 'in',
            1, 5 => 'out',
            2 => 'break_start',
            3 => 'break_end',
            default => 'in',
        };
    }
}
