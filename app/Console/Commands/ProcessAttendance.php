<?php

// =============================================================================
// ProcessAttendance Command - Process pending attendance records
// Calculate late arrivals, overtime, work duration
// =============================================================================

namespace App\Console\Commands;

use App\Models\AttendanceRecord;
use App\Models\Shift;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessAttendance extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'attendance:process
                            {--date= : Process records for specific date (Y-m-d)}
                            {--company= : Only process for specific company ID}
                            {--reprocess : Reprocess already processed records}';

    /**
     * The console command description.
     */
    protected $description = 'Process pending attendance records to calculate lateness, overtime, and work duration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = AttendanceRecord::query()
            ->with(['user.shifts', 'shift']);

        // Status filter
        if (!$this->option('reprocess')) {
            $query->where('status', 'pending');
        }

        // Date filter
        if ($date = $this->option('date')) {
            $query->whereDate('punch_date', $date);
        } else {
            // Default: process yesterday and today
            $query->whereDate('punch_date', '>=', now()->subDay());
        }

        // Company filter
        if ($companyId = $this->option('company')) {
            $query->where('company_id', $companyId);
        }

        $records = $query->orderBy('user_id')
            ->orderBy('punch_date')
            ->orderBy('punched_at')
            ->get();

        if ($records->isEmpty()) {
            $this->info('No records to process.');
            return Command::SUCCESS;
        }

        $this->info("Processing {$records->count()} attendance record(s)...");

        $progressBar = $this->output->createProgressBar($records->count());
        $progressBar->start();

        // Group by user and date to process pairs
        $grouped = $records->groupBy(function ($record) {
            return $record->user_id . '_' . $record->punch_date->format('Y-m-d');
        });

        $processed = 0;
        $errors = 0;

        foreach ($grouped as $key => $userDayRecords) {
            try {
                $this->processUserDay($userDayRecords);
                $processed += $userDayRecords->count();
            } catch (\Exception $e) {
                $errors += $userDayRecords->count();
                $this->error("\nError processing {$key}: {$e->getMessage()}");
            }

            $progressBar->advance($userDayRecords->count());
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $processed],
                ['Errors', $errors],
                ['Total', $records->count()],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Process attendance records for a single user on a single day.
     */
    protected function processUserDay($records): void
    {
        $user = $records->first()->user;
        $date = $records->first()->punch_date;

        // Get the user's shift for this day
        $shift = $this->getUserShift($user, $date);

        // Separate ins and outs
        $punchIns = $records->where('type', 'in')->sortBy('punched_at');
        $punchOuts = $records->where('type', 'out')->sortBy('punched_at');

        $firstIn = $punchIns->first();
        $lastOut = $punchOuts->last();

        DB::transaction(function () use ($firstIn, $lastOut, $shift, $records) {
            // Process first punch-in (check lateness)
            if ($firstIn && $shift) {
                $isLate = $shift->isLate($firstIn->punched_at);
                $lateMinutes = $isLate ? $shift->calculateLateMinutes($firstIn->punched_at) : 0;

                $firstIn->update([
                    'shift_id' => $shift->id,
                    'is_late' => $isLate,
                    'late_minutes' => $lateMinutes,
                    'status' => 'processed',
                ]);
            } elseif ($firstIn) {
                $firstIn->update(['status' => 'processed']);
            }

            // Process last punch-out (check early departure and calculate work duration)
            if ($lastOut && $firstIn) {
                $workMinutes = $firstIn->punched_at->diffInMinutes($lastOut->punched_at);

                // Check early departure
                $isEarly = false;
                $earlyMinutes = 0;
                $overtimeMinutes = 0;

                if ($shift) {
                    $shiftEnd = \Carbon\Carbon::parse($shift->end_time)
                        ->setDate($lastOut->punch_date->year, $lastOut->punch_date->month, $lastOut->punch_date->day);

                    if ($shift->next_day_end) {
                        $shiftEnd->addDay();
                    }

                    $earlyThreshold = $shiftEnd->copy()->subMinutes($shift->early_departure_threshold);

                    if ($lastOut->punched_at < $earlyThreshold) {
                        $isEarly = true;
                        $earlyMinutes = $lastOut->punched_at->diffInMinutes($shiftEnd);
                    }

                    // Check overtime
                    $overtimeThreshold = $shiftEnd->copy()->addMinutes($shift->overtime_threshold_minutes);
                    if ($lastOut->punched_at > $overtimeThreshold) {
                        $overtimeMinutes = $overtimeThreshold->diffInMinutes($lastOut->punched_at);
                    }
                }

                $lastOut->update([
                    'shift_id' => $shift?->id,
                    'work_duration_minutes' => $workMinutes,
                    'is_early_departure' => $isEarly,
                    'early_minutes' => $earlyMinutes,
                    'overtime_minutes' => $overtimeMinutes,
                    'status' => 'processed',
                ]);
            } elseif ($lastOut) {
                $lastOut->update(['status' => 'processed']);
            }

            // Mark remaining records as processed
            $records->whereNotIn('id', [$firstIn?->id, $lastOut?->id]->filter())
                ->each(fn($r) => $r->update(['status' => 'processed', 'shift_id' => $shift?->id]));
        });
    }

    /**
     * Get the user's assigned shift for a specific date.
     */
    protected function getUserShift($user, $date): ?Shift
    {
        return $user->shifts()
            ->wherePivot('is_primary', true)
            ->where(function ($q) use ($date) {
                $q->wherePivotNull('effective_to')
                    ->orWherePivot('effective_to', '>=', $date);
            })
            ->first();
    }
}
