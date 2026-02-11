<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRecord;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceProcessor
{
    /**
     * Process attendance for a specific user and date.
     */
    public function process(User $user, Carbon $date): void
    {
        $dateStr = $date->toDateString();
        
        // 1. Get all punches for the user on this date
        $punches = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('punch_date', $dateStr)
            ->orderBy('punch_time')
            ->get();

        if ($punches->isEmpty()) {
            // Check if it was a working day and mark as absent if needed
            // (Logic for absent generation can be separate)
            return;
        }

        // 2. Determine Shift
        // For simplicity, we assume one shift per day. 
        // In complex scenarios, we might need to find the best matching shift.
        $shift = $this->determineShift($user, $date);

        if (!$shift) {
            // No shift assigned, treat as unstructured work or error
            return;
        }

        // 3. Group Punches (Simple First-In, Last-Out for single shift)
        // Or pairs for multiple entries. Let's assume First-In, Last-Out for the main shift record.
        $checkIn = $punches->first(fn($p) => $p->type === 'in' || $p->type === 'check_in');
        $checkOut = $punches->last(fn($p) => $p->type === 'out' || $p->type === 'check_out');

        if (!$checkIn) {
            // Missing punch in
            return;
        }

        // 4. Calculate Metrics
        $this->calculateMetrics($checkIn, $checkOut, $shift);
    }

    protected function determineShift(User $user, Carbon $date): ?Shift
    {
        // Logic to find assigned shift. 
        // For now, return the first active shift for the company or user's specific shift.
        // This is a placeholder for complex shift assignment logic.
        return Shift::where('company_id', $user->company_id)->active()->first();
    }

    protected function calculateMetrics(AttendanceRecord $checkIn, ?AttendanceRecord $checkOut, Shift $shift): void
    {
        $shiftStart = Carbon::parse($shift->start_time)->setDateFrom($checkIn->punched_at);
        $shiftEnd = Carbon::parse($shift->end_time)->setDateFrom($checkIn->punched_at);

        // Handle overnight shifts
        if ($shiftEnd->lessThan($shiftStart)) {
            $shiftEnd->addDay();
        }

        // 1. Late Arrival
        $graceTime = $shiftStart->copy()->addMinutes($shift->grace_period_minutes ?? 0);
        $isLate = $checkIn->punched_at->greaterThan($graceTime);
        $lateMinutes = $isLate ? $checkIn->punched_at->diffInMinutes($shiftStart) : 0;

        // 2. Early Departure
        $isEarly = false;
        $earlyMinutes = 0;
        $workDuration = 0;
        $overtime = 0;

        if ($checkOut) {
            // Adjust checkOut date if it's next day
            // (Assuming checkOut record has correct full datetime)
            
            $isEarly = $checkOut->punched_at->lessThan($shiftEnd->copy()->subMinutes($shift->early_departure_threshold ?? 0));
            $earlyMinutes = $isEarly ? $shiftEnd->diffInMinutes($checkOut->punched_at) : 0;

            // 3. Work Duration
            $workDuration = $checkOut->punched_at->diffInMinutes($checkIn->punched_at);

            // 4. Overtime
            // Simple logic: time worked beyond shift duration
            $shiftDuration = $shiftStart->diffInMinutes($shiftEnd);
            if ($workDuration > $shiftDuration) {
                $overtime = $workDuration - $shiftDuration;
            }
        }

        // Update Check-In Record with summary (or create a separate summary record)
        // Here we update the Check-In record to act as the "Master" record for the day
        $checkIn->update([
            'shift_id' => $shift->id,
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
            'is_early_departure' => $isEarly,
            'early_minutes' => $earlyMinutes,
            'work_duration_minutes' => $workDuration,
            'overtime_minutes' => $overtime,
            'status' => $checkOut ? 'processed' : 'missing_punch_out',
        ]);

        if ($checkOut && $checkOut->id !== $checkIn->id) {
            $checkOut->update(['status' => 'processed']);
        }
    }
}
