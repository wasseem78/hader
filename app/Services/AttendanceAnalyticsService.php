<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AttendanceAnalyticsService
 * 
 * Provides comprehensive analysis of attendance data including:
 * - Late arrivals analysis
 * - Early departures analysis
 * - Absence tracking
 * - Working hours calculation
 * - Trend analysis
 */
class AttendanceAnalyticsService
{
    /**
     * Get comprehensive attendance statistics for a date range
     */
    public function getAttendanceStats(int $companyId, Carbon $startDate, Carbon $endDate, ?string $branchId = null): array
    {
        $query = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['shifts', 'attendanceRecords' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('punch_date', [$startDate, $endDate])
                  ->orderBy('punch_date')
                  ->orderBy('punch_time');
            }]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->get();
        
        $stats = [
            'total_employees' => $employees->count(),
            'total_working_days' => $this->calculateWorkingDays($startDate, $endDate),
            'expected_attendance' => 0,
            'actual_attendance' => 0,
            'late_count' => 0,
            'early_departure_count' => 0,
            'absence_count' => 0,
            'on_time_count' => 0,
            'total_late_minutes' => 0,
            'total_overtime_minutes' => 0,
            'attendance_rate' => 0,
            'punctuality_rate' => 0,
        ];

        foreach ($employees as $employee) {
            $employeeStats = $this->analyzeEmployeeAttendance($employee, $startDate, $endDate);
            
            $stats['expected_attendance'] += $employeeStats['expected_days'];
            $stats['actual_attendance'] += $employeeStats['present_days'];
            $stats['late_count'] += $employeeStats['late_days'];
            $stats['early_departure_count'] += $employeeStats['early_departure_days'];
            $stats['absence_count'] += $employeeStats['absent_days'];
            $stats['on_time_count'] += $employeeStats['on_time_days'];
            $stats['total_late_minutes'] += $employeeStats['total_late_minutes'];
            $stats['total_overtime_minutes'] += $employeeStats['total_overtime_minutes'];
        }

        // Calculate rates
        if ($stats['expected_attendance'] > 0) {
            $stats['attendance_rate'] = round(($stats['actual_attendance'] / $stats['expected_attendance']) * 100, 1);
        }
        
        if ($stats['actual_attendance'] > 0) {
            $stats['punctuality_rate'] = round(($stats['on_time_count'] / $stats['actual_attendance']) * 100, 1);
        }

        return $stats;
    }

    /**
     * Analyze individual employee attendance
     *
     * Works in two modes:
     * 1. With shifts: Full analysis including late/early/overtime calculations
     * 2. Without shifts: Basic analysis based on actual attendance records
     *    (counts present days from records, uses working days as expected)
     */
    public function analyzeEmployeeAttendance(User $employee, Carbon $startDate, Carbon $endDate): array
    {
        $shifts = $employee->shifts;
        $records = $employee->attendanceRecords;
        
        // Filter records to the date range (in case eager load wasn't filtered)
        $records = $records->filter(function ($record) use ($startDate, $endDate) {
            $punchDate = $record->punch_date instanceof Carbon 
                ? $record->punch_date 
                : Carbon::parse($record->punch_date);
            return $punchDate->between($startDate, $endDate);
        });
        
        $analysis = [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'expected_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'late_days' => 0,
            'early_departure_days' => 0,
            'on_time_days' => 0,
            'total_late_minutes' => 0,
            'total_early_minutes' => 0,
            'total_overtime_minutes' => 0,
            'total_work_hours' => 0,
            'daily_records' => [],
        ];

        // If no shifts assigned, do basic record-based analysis
        if ($shifts->isEmpty()) {
            return $this->analyzeWithoutShifts($analysis, $records, $startDate, $endDate);
        }

        // Analyze each day in the range (shift-based)
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate && $currentDate <= Carbon::today()) {
            $dayOfWeek = strtolower($currentDate->format('l'));
            $dateString = $currentDate->toDateString();
            
            // Check if this day is a working day for any of the employee's shifts
            $workingShift = $this->getApplicableShift($shifts, $dayOfWeek);
            
            if ($workingShift) {
                $analysis['expected_days']++;
                
                // Get records for this day
                $dayRecords = $records->filter(function ($record) use ($dateString) {
                    $pd = $record->punch_date instanceof Carbon 
                        ? $record->punch_date->toDateString() 
                        : (string) $record->punch_date;
                    return $pd === $dateString;
                });
                
                $dayAnalysis = $this->analyzeDayAttendance($dayRecords, $workingShift, $currentDate);
                $analysis['daily_records'][$dateString] = $dayAnalysis;
                
                if ($dayAnalysis['status'] === 'present') {
                    $analysis['present_days']++;
                    
                    if ($dayAnalysis['is_late']) {
                        $analysis['late_days']++;
                        $analysis['total_late_minutes'] += $dayAnalysis['late_minutes'];
                    } else {
                        $analysis['on_time_days']++;
                    }
                    
                    if ($dayAnalysis['is_early_departure']) {
                        $analysis['early_departure_days']++;
                        $analysis['total_early_minutes'] += $dayAnalysis['early_minutes'];
                    }
                    
                    $analysis['total_overtime_minutes'] += $dayAnalysis['overtime_minutes'];
                    $analysis['total_work_hours'] += $dayAnalysis['work_hours'];
                } else {
                    $analysis['absent_days']++;
                }
            }
            
            $currentDate->addDay();
        }

        return $analysis;
    }

    /**
     * Basic attendance analysis when no shifts are assigned.
     * Counts unique days with records as "present" and estimates expected days.
     */
    private function analyzeWithoutShifts(array $analysis, Collection $records, Carbon $startDate, Carbon $endDate): array
    {
        // Group records by date
        $groupedByDate = $records->groupBy(function ($record) {
            return $record->punch_date instanceof Carbon 
                ? $record->punch_date->toDateString() 
                : (string) $record->punch_date;
        });

        // Expected days = working days up to today (not future)
        $effectiveEnd = $endDate->copy()->min(Carbon::today());
        $analysis['expected_days'] = $this->calculateWorkingDays($startDate, $effectiveEnd);
        $analysis['present_days'] = $groupedByDate->count();
        $analysis['absent_days'] = max(0, $analysis['expected_days'] - $analysis['present_days']);
        
        // Without shifts we can't determine late, so count all present as on-time
        $analysis['on_time_days'] = $analysis['present_days'];

        // Calculate work hours from in/out pairs where available
        foreach ($groupedByDate as $dateString => $dayRecords) {
            $checkIns = $dayRecords->where('type', 'in')->sortBy('punch_time');
            $checkOuts = $dayRecords->where('type', 'out')->sortByDesc('punch_time');

            $firstIn = $checkIns->first();
            $lastOut = $checkOuts->first();

            $dayAnalysis = [
                'date' => $dateString,
                'shift_name' => null,
                'shift_start' => null,
                'shift_end' => null,
                'status' => 'present',
                'check_in' => $firstIn?->punch_time,
                'check_out' => $lastOut?->punch_time,
                'is_late' => false,
                'late_minutes' => 0,
                'is_early_departure' => false,
                'early_minutes' => 0,
                'overtime_minutes' => 0,
                'work_hours' => 0,
                'records_count' => $dayRecords->count(),
            ];

            if ($firstIn && $lastOut && $firstIn->id !== $lastOut->id) {
                $inTime = Carbon::parse($firstIn->punched_at);
                $outTime = Carbon::parse($lastOut->punched_at);
                $dayAnalysis['work_hours'] = round($inTime->diffInMinutes($outTime) / 60, 2);
                $analysis['total_work_hours'] += $dayAnalysis['work_hours'];
            }

            $analysis['daily_records'][$dateString] = $dayAnalysis;
        }

        return $analysis;
    }

    /**
     * Get the applicable shift for a given day
     */
    private function getApplicableShift(Collection $shifts, string $dayOfWeek): ?Shift
    {
        // Map day names to numbers
        $dayMap = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6,
        ];
        
        $dayNumber = $dayMap[$dayOfWeek] ?? null;

        foreach ($shifts as $shift) {
            $workingDays = $shift->working_days ?? [];
            
            // Check both string and numeric format
            if (in_array($dayOfWeek, $workingDays) || in_array($dayNumber, $workingDays)) {
                return $shift;
            }
        }

        return null;
    }

    /**
     * Analyze attendance for a single day
     */
    private function analyzeDayAttendance(Collection $records, Shift $shift, Carbon $date): array
    {
        $result = [
            'date' => $date->toDateString(),
            'shift_name' => $shift->name,
            'shift_start' => $shift->start_time,
            'shift_end' => $shift->end_time,
            'status' => 'absent',
            'check_in' => null,
            'check_out' => null,
            'is_late' => false,
            'late_minutes' => 0,
            'is_early_departure' => false,
            'early_minutes' => 0,
            'overtime_minutes' => 0,
            'work_hours' => 0,
        ];

        if ($records->isEmpty()) {
            return $result;
        }

        // Get first check-in and last check-out
        $checkIns = $records->where('type', 'in');
        $checkOuts = $records->where('type', 'out');

        $firstCheckIn = $checkIns->sortBy('punch_time')->first();
        $lastCheckOut = $checkOuts->sortByDesc('punch_time')->first();

        if ($firstCheckIn) {
            $result['status'] = 'present';
            $result['check_in'] = $firstCheckIn->punch_time;

            // Calculate late minutes
            $shiftStart = Carbon::parse($shift->start_time);
            $graceEnd = $shiftStart->copy()->addMinutes($shift->grace_period_minutes ?? 0);
            $actualCheckIn = Carbon::parse($firstCheckIn->punch_time);

            if ($actualCheckIn->format('H:i:s') > $graceEnd->format('H:i:s')) {
                $result['is_late'] = true;
                $result['late_minutes'] = $graceEnd->diffInMinutes($actualCheckIn);
            }
        }

        if ($lastCheckOut) {
            $result['check_out'] = $lastCheckOut->punch_time;

            // Calculate early departure
            $shiftEnd = Carbon::parse($shift->end_time);
            $actualCheckOut = Carbon::parse($lastCheckOut->punch_time);

            // Handle next-day end time
            if ($shift->next_day_end) {
                $shiftEnd->addDay();
            }

            $earlyThreshold = $shift->early_departure_threshold ?? 0;
            $expectedEnd = $shiftEnd->copy()->subMinutes($earlyThreshold);

            if ($actualCheckOut->format('H:i:s') < $expectedEnd->format('H:i:s')) {
                $result['is_early_departure'] = true;
                $result['early_minutes'] = $actualCheckOut->diffInMinutes($expectedEnd);
            }

            // Calculate overtime
            if ($actualCheckOut->format('H:i:s') > $shiftEnd->format('H:i:s')) {
                $overtimeThreshold = $shift->overtime_threshold_minutes ?? 0;
                $actualOvertime = $shiftEnd->diffInMinutes($actualCheckOut);
                
                if ($actualOvertime > $overtimeThreshold) {
                    $result['overtime_minutes'] = $actualOvertime - $overtimeThreshold;
                }
            }
        }

        // Calculate total work hours
        if ($result['check_in'] && $result['check_out']) {
            $checkInTime = Carbon::parse($result['check_in']);
            $checkOutTime = Carbon::parse($result['check_out']);
            $workMinutes = $checkInTime->diffInMinutes($checkOutTime);
            
            // Deduct break if applicable
            if ($shift->break_deducted && $shift->break_duration_minutes) {
                $workMinutes -= $shift->break_duration_minutes;
            }
            
            $result['work_hours'] = round($workMinutes / 60, 2);
        }

        return $result;
    }

    /**
     * Calculate working days between two dates (excluding weekends based on locale)
     */
    private function calculateWorkingDays(Carbon $start, Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();
        
        while ($current <= $end) {
            // Consider Sunday-Thursday as working days (Middle East)
            // Adjust this based on company settings
            $dayOfWeek = $current->dayOfWeek;
            if ($dayOfWeek >= 0 && $dayOfWeek <= 4) { // Sunday to Thursday
                $days++;
            }
            $current->addDay();
        }
        
        return $days;
    }

    /**
     * Get daily attendance trend data for charts.
     *
     * Counts unique users per day from actual attendance records.
     * Uses the first punch per user per day to determine presence.
     */
    public function getDailyTrend(int $companyId, Carbon $startDate, Carbon $endDate, ?string $branchId = null): array
    {
        $query = AttendanceRecord::where('company_id', $companyId)
            ->whereBetween('punch_date', [$startDate, $endDate])
            ->whereNull('deleted_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $dailyData = $query->selectRaw('
                DATE(punch_date) as date,
                COUNT(DISTINCT user_id) as present_count,
                SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count
            ')
            ->groupBy(DB::raw('DATE(punch_date)'))
            ->orderBy('date')
            ->get();

        $totalEmployees = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->count();

        $labels = [];
        $presentData = [];
        $absentData = [];
        $lateData = [];

        // Fill all dates in range (not just ones with data)
        $current = $startDate->copy();
        $effectiveEnd = $endDate->copy()->min(Carbon::today());
        $dailyMap = $dailyData->keyBy('date');
        
        while ($current <= $effectiveEnd) {
            $dateKey = $current->toDateString();
            $day = $dailyMap->get($dateKey);
            
            $labels[] = $current->format('M d');
            $presentCount = $day ? (int) $day->present_count : 0;
            $presentData[] = $presentCount;
            $absentData[] = max(0, $totalEmployees - $presentCount);
            $lateData[] = $day ? (int) $day->late_count : 0;
            
            $current->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('messages.present'),
                    'data' => $presentData,
                    'borderColor' => 'rgb(52, 211, 153)',
                    'backgroundColor' => 'rgba(52, 211, 153, 0.5)',
                ],
                [
                    'label' => __('messages.absent'),
                    'data' => $absentData,
                    'borderColor' => 'rgb(248, 113, 113)',
                    'backgroundColor' => 'rgba(248, 113, 113, 0.5)',
                ],
                [
                    'label' => __('messages.late'),
                    'data' => $lateData,
                    'borderColor' => 'rgb(251, 191, 36)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.5)',
                ],
            ],
        ];
    }

    /**
     * Get employee ranking by punctuality
     */
    public function getEmployeeRanking(int $companyId, Carbon $startDate, Carbon $endDate, ?string $branchId = null, int $limit = 10): array
    {
        $query = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->with([
                'shifts',
                'departmentRelation',
                'attendanceRecords' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('punch_date', [$startDate, $endDate]);
                }
            ]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->get();
        
        $rankings = [];
        
        foreach ($employees as $employee) {
            $analysis = $this->analyzeEmployeeAttendance($employee, $startDate, $endDate);
            
            if ($analysis['expected_days'] > 0) {
                $punctualityRate = $analysis['present_days'] > 0 
                    ? round(($analysis['on_time_days'] / $analysis['present_days']) * 100, 1)
                    : 0;
                
                $attendanceRate = round(($analysis['present_days'] / $analysis['expected_days']) * 100, 1);
                
                $rankings[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'department' => $employee->departmentRelation?->name,
                    'attendance_rate' => $attendanceRate,
                    'punctuality_rate' => $punctualityRate,
                    'late_days' => $analysis['late_days'],
                    'absent_days' => $analysis['absent_days'],
                    'total_late_minutes' => $analysis['total_late_minutes'],
                    'avg_late_minutes' => $analysis['late_days'] > 0 
                        ? round($analysis['total_late_minutes'] / $analysis['late_days'], 1)
                        : 0,
                ];
            }
        }

        // Sort by punctuality rate descending
        usort($rankings, fn($a, $b) => $b['punctuality_rate'] <=> $a['punctuality_rate']);

        return [
            'best' => array_slice($rankings, 0, $limit),
            'worst' => array_slice(array_reverse($rankings), 0, $limit),
        ];
    }

    /**
     * Get department-wise statistics
     */
    public function getDepartmentStats(int $companyId, Carbon $startDate, Carbon $endDate, ?string $branchId = null): array
    {
        $query = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('department_id')
            ->with([
                'shifts',
                'departmentRelation',
                'attendanceRecords' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('punch_date', [$startDate, $endDate]);
                }
            ]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->get();
        
        $departmentStats = [];

        foreach ($employees as $employee) {
            $department = $employee->departmentRelation;
            if (!$department) {
                continue;
            }

            $deptId = $department->id;

            if (!isset($departmentStats[$deptId])) {
                $departmentStats[$deptId] = [
                    'id' => $deptId,
                    'name' => $department->name,
                    'employee_count' => 0,
                    'total_late_minutes' => 0,
                    'total_late_days' => 0,
                    'total_absent_days' => 0,
                    'total_present_days' => 0,
                    'total_expected_days' => 0,
                ];
            }

            $analysis = $this->analyzeEmployeeAttendance($employee, $startDate, $endDate);
            
            $departmentStats[$deptId]['employee_count']++;
            $departmentStats[$deptId]['total_late_minutes'] += $analysis['total_late_minutes'];
            $departmentStats[$deptId]['total_late_days'] += $analysis['late_days'];
            $departmentStats[$deptId]['total_absent_days'] += $analysis['absent_days'];
            $departmentStats[$deptId]['total_present_days'] += $analysis['present_days'];
            $departmentStats[$deptId]['total_expected_days'] += $analysis['expected_days'];
        }

        // Calculate rates for each department
        foreach ($departmentStats as &$dept) {
            $dept['attendance_rate'] = $dept['total_expected_days'] > 0 
                ? round(($dept['total_present_days'] / $dept['total_expected_days']) * 100, 1)
                : 0;
            
            $dept['avg_late_minutes'] = $dept['total_late_days'] > 0
                ? round($dept['total_late_minutes'] / $dept['total_late_days'], 1)
                : 0;
        }

        return array_values($departmentStats);
    }

    /**
     * Get late arrivals breakdown by time ranges
     */
    public function getLateBreakdown(int $companyId, Carbon $startDate, Carbon $endDate, ?string $branchId = null): array
    {
        $query = AttendanceRecord::where('company_id', $companyId)
            ->whereBetween('punch_date', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->where('is_late', true);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $lateRecords = $query->get();

        $breakdown = [
            '1-15' => 0,    // 1-15 minutes late
            '16-30' => 0,   // 16-30 minutes late
            '31-60' => 0,   // 31-60 minutes late
            '60+' => 0,     // More than 60 minutes late
        ];

        foreach ($lateRecords as $record) {
            $lateMinutes = $record->late_minutes ?? 0;
            
            if ($lateMinutes <= 15) {
                $breakdown['1-15']++;
            } elseif ($lateMinutes <= 30) {
                $breakdown['16-30']++;
            } elseif ($lateMinutes <= 60) {
                $breakdown['31-60']++;
            } else {
                $breakdown['60+']++;
            }
        }

        return [
            'labels' => [
                __('messages.minutes_1_15'),
                __('messages.minutes_16_30'),
                __('messages.minutes_31_60'),
                __('messages.minutes_60_plus'),
            ],
            'data' => array_values($breakdown),
            'colors' => ['#fbbf24', '#fb923c', '#f87171', '#dc2626'],
        ];
    }

    /**
     * Get weekly pattern analysis
     */
    public function getWeeklyPattern(int $companyId, Carbon $startDate, Carbon $endDate, ?string $branchId = null): array
    {
        $query = AttendanceRecord::where('company_id', $companyId)
            ->whereBetween('punch_date', [$startDate, $endDate])
            ->whereNull('deleted_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $weeklyData = $query->selectRaw('
                DAYOFWEEK(punch_date) as day_of_week,
                COUNT(DISTINCT user_id, DATE(punch_date)) as total_attendance,
                SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_count
            ')
            ->groupBy(DB::raw('DAYOFWEEK(punch_date)'))
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        $days = [
            1 => __('messages.sunday'),
            2 => __('messages.monday'),
            3 => __('messages.tuesday'),
            4 => __('messages.wednesday'),
            5 => __('messages.thursday'),
            6 => __('messages.friday'),
            7 => __('messages.saturday'),
        ];

        $labels = [];
        $attendanceData = [];
        $lateData = [];

        foreach ($days as $num => $name) {
            $labels[] = $name;
            $dayData = $weeklyData->get($num);
            $attendanceData[] = $dayData->total_attendance ?? 0;
            $lateData[] = $dayData->late_count ?? 0;
        }

        return [
            'labels' => $labels,
            'attendance' => $attendanceData,
            'late' => $lateData,
        ];
    }
}
