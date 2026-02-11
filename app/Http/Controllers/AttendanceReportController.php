<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use App\Services\AttendanceAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    protected AttendanceAnalyticsService $analyticsService;

    public function __construct(AttendanceAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Advanced Attendance Report with comprehensive filtering
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        // Date range - default to current month
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now();

        // Build query for daily attendance summary
        $query = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['branch', 'shifts']);

        // Filter by branch
        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Filter by shift
        if ($shiftId = $request->get('shift_id')) {
            $query->whereHas('shifts', function ($q) use ($shiftId) {
                $q->where('shifts.id', $shiftId);
            });
        }

        // Filter by department (new system - using department_id)
        if ($departmentId = $request->get('department_id')) {
            $query->where('department_id', $departmentId);
        }
        
        // Legacy filter by department name
        if ($department = $request->get('department')) {
            $query->where('department', $department);
        }

        $employees = $query->orderBy('name')->get();

        // Get attendance data for date range
        $attendanceData = $this->buildAttendanceReport($employees, $startDate, $endDate, $request);

        // Apply late-only filter
        if ($request->get('late_only')) {
            $attendanceData = array_filter($attendanceData, fn($row) => $row['total_late_days'] > 0);
        }

        // Get summary statistics
        $summary = $this->calculateSummary($attendanceData);

        // Get filter options
        $branches = Branch::where('company_id', $companyId)->active()->orderBy('name')->get();
        $shifts = Shift::where('company_id', $companyId)->active()->orderBy('name')->get();
        $departments = Department::where('company_id', $companyId)->active()->ordered()->get();

        return view('reports.attendance-report', [
            'attendanceData' => $attendanceData,
            'summary' => $summary,
            'branches' => $branches,
            'shifts' => $shifts,
            'departments' => $departments,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'branch_id' => $request->get('branch_id'),
                'shift_id' => $request->get('shift_id'),
                'department' => $request->get('department'),
                'late_only' => $request->get('late_only'),
            ],
        ]);
    }

    /**
     * Daily attendance detail view
     */
    public function daily(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $date = $request->get('date') ? Carbon::parse($request->get('date')) : Carbon::today();

        // Get all employees with their attendance for this day
        $query = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['branch', 'shifts', 'attendanceRecords' => function ($q) use ($date) {
                $q->whereDate('punch_date', $date)->orderBy('punch_time');
            }]);

        // Filter by shift
        if ($shiftId = $request->get('shift_id')) {
            $query->whereHas('shifts', function ($q) use ($shiftId) {
                $q->where('shifts.id', $shiftId);
            });
        }

        // Filter by branch
        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->orderBy('name')->get();

        // Process attendance data
        $dailyData = [];
        foreach ($employees as $employee) {
            $shift = $employee->shifts->first();
            $records = $employee->attendanceRecords;
            
            $checkIn = $records->where('type', 'in')->first();
            $checkOut = $records->where('type', 'out')->first();
            
            $status = 'absent';
            $isLate = false;
            $lateMinutes = 0;
            $isEarlyDeparture = false;
            $earlyMinutes = 0;
            $workHours = 0;
            $overtime = 0;

            if ($checkIn) {
                $status = 'present';
                $isLate = $checkIn->is_late ?? false;
                $lateMinutes = $checkIn->late_minutes ?? 0;
            }

            if ($checkOut) {
                $isEarlyDeparture = $checkOut->is_early_departure ?? false;
                $earlyMinutes = $checkOut->early_minutes ?? 0;
                $workHours = round(($checkOut->work_duration_minutes ?? 0) / 60, 2);
                $overtime = $checkOut->overtime_minutes ?? 0;
            }

            // Determine severity level for styling
            $severityLevel = 'normal';
            if ($status === 'absent') {
                $severityLevel = 'critical';
            } elseif ($isLate && $lateMinutes > 30) {
                $severityLevel = 'high';
            } elseif ($isLate) {
                $severityLevel = 'medium';
            } elseif ($isEarlyDeparture) {
                $severityLevel = 'low';
            }

            $dailyData[] = [
                'employee' => $employee,
                'shift' => $shift,
                'check_in' => $checkIn?->punch_time,
                'check_out' => $checkOut?->punch_time,
                'status' => $status,
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'is_early_departure' => $isEarlyDeparture,
                'early_minutes' => $earlyMinutes,
                'work_hours' => $workHours,
                'overtime' => $overtime,
                'severity_level' => $severityLevel,
            ];
        }

        // Apply late-only filter
        if ($request->get('late_only')) {
            $dailyData = array_filter($dailyData, fn($row) => $row['is_late']);
        }

        // Apply absent filter
        if ($request->get('absent_only')) {
            $dailyData = array_filter($dailyData, fn($row) => $row['status'] === 'absent');
        }

        // Calculate day summary
        $daySummary = [
            'total' => count($employees),
            'present' => count(array_filter($dailyData, fn($r) => $r['status'] === 'present')),
            'absent' => count(array_filter($dailyData, fn($r) => $r['status'] === 'absent')),
            'late' => count(array_filter($dailyData, fn($r) => $r['is_late'])),
            'on_time' => count(array_filter($dailyData, fn($r) => $r['status'] === 'present' && !$r['is_late'])),
            'early_departure' => count(array_filter($dailyData, fn($r) => $r['is_early_departure'])),
        ];

        // Get filter options
        $branches = Branch::where('company_id', $companyId)->active()->get();
        $shifts = Shift::where('company_id', $companyId)->active()->get();

        return view('reports.daily-attendance', [
            'dailyData' => array_values($dailyData),
            'summary' => $daySummary,
            'date' => $date,
            'branches' => $branches,
            'shifts' => $shifts,
            'filters' => [
                'date' => $date->toDateString(),
                'shift_id' => $request->get('shift_id'),
                'branch_id' => $request->get('branch_id'),
                'late_only' => $request->get('late_only'),
                'absent_only' => $request->get('absent_only'),
            ],
        ]);
    }

    /**
     * Build comprehensive attendance report
     */
    private function buildAttendanceReport($employees, Carbon $startDate, Carbon $endDate, Request $request): array
    {
        $report = [];
        
        foreach ($employees as $employee) {
            $shift = $employee->shifts->first();
            
            if (!$shift) {
                continue; // Skip employees without shifts
            }

            // Get attendance records for this employee
            $records = AttendanceRecord::where('user_id', $employee->id)
                ->whereBetween('punch_date', [$startDate, $endDate])
                ->orderBy('punch_date')
                ->orderBy('punch_time')
                ->get();

            // Group by date
            $recordsByDate = $records->groupBy(fn($r) => $r->punch_date->toDateString());

            $workingDays = $this->countWorkingDays($startDate, $endDate, $shift);
            $presentDays = 0;
            $lateDays = 0;
            $totalLateMinutes = 0;
            $absentDays = 0;
            $earlyDepartureDays = 0;
            $totalWorkHours = 0;
            $totalOvertimeMinutes = 0;
            $dailyDetails = [];

            // Analyze each working day
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($this->isWorkingDay($currentDate, $shift)) {
                    $dateStr = $currentDate->toDateString();
                    $dayRecords = $recordsByDate->get($dateStr);
                    
                    if ($dayRecords && $dayRecords->count() > 0) {
                        $presentDays++;
                        $checkIn = $dayRecords->where('type', 'in')->first();
                        $checkOut = $dayRecords->where('type', 'out')->first();
                        
                        $isLate = $checkIn?->is_late ?? false;
                        $lateMin = $checkIn?->late_minutes ?? 0;
                        
                        if ($isLate) {
                            $lateDays++;
                            $totalLateMinutes += $lateMin;
                        }
                        
                        if ($checkOut?->is_early_departure) {
                            $earlyDepartureDays++;
                        }
                        
                        $workMins = $checkOut?->work_duration_minutes ?? 0;
                        $totalWorkHours += $workMins / 60;
                        $totalOvertimeMinutes += $checkOut?->overtime_minutes ?? 0;

                        $dailyDetails[$dateStr] = [
                            'status' => 'present',
                            'check_in' => $checkIn?->punch_time,
                            'check_out' => $checkOut?->punch_time,
                            'is_late' => $isLate,
                            'late_minutes' => $lateMin,
                            'work_hours' => round($workMins / 60, 2),
                        ];
                    } else {
                        $absentDays++;
                        $dailyDetails[$dateStr] = [
                            'status' => 'absent',
                            'check_in' => null,
                            'check_out' => null,
                            'is_late' => false,
                            'late_minutes' => 0,
                            'work_hours' => 0,
                        ];
                    }
                }
                $currentDate->addDay();
            }

            // Calculate rates
            $attendanceRate = $workingDays > 0 ? round(($presentDays / $workingDays) * 100, 1) : 0;
            $punctualityRate = $presentDays > 0 ? round((($presentDays - $lateDays) / $presentDays) * 100, 1) : 0;
            $avgLateMinutes = $lateDays > 0 ? round($totalLateMinutes / $lateDays) : 0;
            $avgWorkHours = $presentDays > 0 ? round($totalWorkHours / $presentDays, 2) : 0;

            $report[] = [
                'employee' => $employee,
                'shift' => $shift,
                'working_days' => $workingDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'total_late_days' => $lateDays,
                'total_late_minutes' => $totalLateMinutes,
                'avg_late_minutes' => $avgLateMinutes,
                'early_departure_days' => $earlyDepartureDays,
                'total_work_hours' => round($totalWorkHours, 2),
                'avg_work_hours' => $avgWorkHours,
                'total_overtime_minutes' => $totalOvertimeMinutes,
                'attendance_rate' => $attendanceRate,
                'punctuality_rate' => $punctualityRate,
                'daily_details' => $dailyDetails,
                'severity' => $this->calculateSeverity($attendanceRate, $punctualityRate, $avgLateMinutes),
            ];
        }

        // Sort by severity (worst first)
        usort($report, fn($a, $b) => $b['severity']['score'] <=> $a['severity']['score']);

        return $report;
    }

    /**
     * Count working days based on shift
     */
    private function countWorkingDays(Carbon $start, Carbon $end, Shift $shift): int
    {
        $count = 0;
        $current = $start->copy();
        
        while ($current <= $end) {
            if ($this->isWorkingDay($current, $shift)) {
                $count++;
            }
            $current->addDay();
        }
        
        return $count;
    }

    /**
     * Check if date is a working day
     */
    private function isWorkingDay(Carbon $date, Shift $shift): bool
    {
        $dayName = strtolower($date->format('l'));
        $workingDays = $shift->working_days ?? ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];
        
        return in_array($dayName, $workingDays);
    }

    /**
     * Calculate severity level for styling
     */
    private function calculateSeverity(float $attendanceRate, float $punctualityRate, int $avgLateMinutes): array
    {
        $score = 0;
        $level = 'good';
        $color = '#34d399'; // Green
        
        // Attendance rate scoring
        if ($attendanceRate < 80) {
            $score += 30;
        } elseif ($attendanceRate < 90) {
            $score += 15;
        }
        
        // Punctuality rate scoring
        if ($punctualityRate < 70) {
            $score += 30;
        } elseif ($punctualityRate < 85) {
            $score += 15;
        }
        
        // Average late minutes scoring
        if ($avgLateMinutes > 45) {
            $score += 25;
        } elseif ($avgLateMinutes > 20) {
            $score += 10;
        }
        
        // Determine level
        if ($score >= 50) {
            $level = 'critical';
            $color = '#dc2626'; // Red
        } elseif ($score >= 30) {
            $level = 'warning';
            $color = '#f59e0b'; // Orange
        } elseif ($score >= 15) {
            $level = 'attention';
            $color = '#fbbf24'; // Yellow
        }
        
        return [
            'score' => $score,
            'level' => $level,
            'color' => $color,
        ];
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(array $data): array
    {
        $totalEmployees = count($data);
        
        if ($totalEmployees === 0) {
            return [
                'total_employees' => 0,
                'avg_attendance_rate' => 0,
                'avg_punctuality_rate' => 0,
                'total_late_incidents' => 0,
                'total_absences' => 0,
                'total_late_hours' => 0,
                'critical_count' => 0,
                'warning_count' => 0,
            ];
        }

        return [
            'total_employees' => $totalEmployees,
            'avg_attendance_rate' => round(array_sum(array_column($data, 'attendance_rate')) / $totalEmployees, 1),
            'avg_punctuality_rate' => round(array_sum(array_column($data, 'punctuality_rate')) / $totalEmployees, 1),
            'total_late_incidents' => array_sum(array_column($data, 'total_late_days')),
            'total_absences' => array_sum(array_column($data, 'absent_days')),
            'total_late_hours' => round(array_sum(array_column($data, 'total_late_minutes')) / 60, 1),
            'critical_count' => count(array_filter($data, fn($r) => $r['severity']['level'] === 'critical')),
            'warning_count' => count(array_filter($data, fn($r) => $r['severity']['level'] === 'warning')),
        ];
    }

    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now();

        $employees = User::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['shifts'])
            ->orderBy('name')
            ->get();

        $data = $this->buildAttendanceReport($employees, $startDate, $endDate, $request);

        $filename = 'attendance_report_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            
            // Header
            fputcsv($file, [
                __('messages.employee_name'),
                __('messages.department'),
                __('messages.shift'),
                __('messages.working_days'),
                __('messages.present_days'),
                __('messages.absent_days'),
                __('messages.late_days'),
                __('messages.total_late_minutes'),
                __('messages.avg_late_minutes'),
                __('messages.attendance_rate') . ' %',
                __('messages.punctuality_rate') . ' %',
                __('messages.status'),
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['employee']->name,
                    $row['employee']->department ?? '-',
                    $row['shift']->name ?? '-',
                    $row['working_days'],
                    $row['present_days'],
                    $row['absent_days'],
                    $row['total_late_days'],
                    $row['total_late_minutes'],
                    $row['avg_late_minutes'],
                    $row['attendance_rate'],
                    $row['punctuality_rate'],
                    $row['severity']['level'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
