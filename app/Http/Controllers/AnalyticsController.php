<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use App\Services\AttendanceAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AttendanceAnalyticsService $analyticsService;

    public function __construct(AttendanceAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Main analytics dashboard
     */
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $branchId = $request->get('branch_id');
        
        // Date range - default to current month
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now()->endOfMonth();

        // Get comprehensive statistics
        $stats = $this->analyticsService->getAttendanceStats($companyId, $startDate, $endDate, $branchId);
        
        // Get daily trend for chart
        $dailyTrend = $this->analyticsService->getDailyTrend($companyId, $startDate, $endDate, $branchId);
        
        // Get employee rankings
        $rankings = $this->analyticsService->getEmployeeRanking($companyId, $startDate, $endDate, $branchId);
        
        // Get department statistics
        $departmentStats = $this->analyticsService->getDepartmentStats($companyId, $startDate, $endDate, $branchId);
        
        // Get late breakdown
        $lateBreakdown = $this->analyticsService->getLateBreakdown($companyId, $startDate, $endDate, $branchId);
        
        // Get weekly pattern
        $weeklyPattern = $this->analyticsService->getWeeklyPattern($companyId, $startDate, $endDate, $branchId);

        // Get branches for filter
        $branches = Branch::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();

        return view('analytics.index', [
            'stats' => $stats,
            'dailyTrend' => $dailyTrend,
            'rankings' => $rankings,
            'departmentStats' => $departmentStats,
            'lateBreakdown' => $lateBreakdown,
            'weeklyPattern' => $weeklyPattern,
            'branches' => $branches,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'branch_id' => $branchId,
            ],
        ]);
    }

    /**
     * Employee detail analysis
     */
    public function employee(Request $request, User $employee)
    {
        $companyId = auth()->user()->company_id;
        
        // Ensure employee belongs to same company
        if ($employee->company_id !== $companyId) {
            abort(403);
        }

        // Date range - default to current month
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now()->endOfMonth();

        // Load relationships
        $employee->load(['shifts', 'branch', 'attendanceRecords' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('punch_date', [$startDate, $endDate])
              ->orderBy('punch_date')
              ->orderBy('punch_time');
        }]);

        // Get detailed analysis
        $analysis = $this->analyticsService->analyzeEmployeeAttendance($employee, $startDate, $endDate);

        // Calculate summary metrics
        $summary = [
            'attendance_rate' => $analysis['expected_days'] > 0 
                ? round(($analysis['present_days'] / $analysis['expected_days']) * 100, 1) 
                : 0,
            'punctuality_rate' => $analysis['present_days'] > 0 
                ? round(($analysis['on_time_days'] / $analysis['present_days']) * 100, 1) 
                : 0,
            'avg_late_minutes' => $analysis['late_days'] > 0 
                ? round($analysis['total_late_minutes'] / $analysis['late_days'], 1) 
                : 0,
            'avg_work_hours' => $analysis['present_days'] > 0 
                ? round($analysis['total_work_hours'] / $analysis['present_days'], 2) 
                : 0,
        ];

        return view('analytics.employee', [
            'employee' => $employee,
            'analysis' => $analysis,
            'summary' => $summary,
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ]);
    }

    /**
     * Export analytics report
     */
    public function export(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $branchId = $request->get('branch_id');
        $format = $request->get('format', 'csv');
        
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now()->endOfMonth();

        // Get employee rankings with full data
        $rankings = $this->analyticsService->getEmployeeRanking(
            $companyId, 
            $startDate, 
            $endDate, 
            $branchId, 
            1000 // Get all employees
        );

        $allEmployees = array_merge($rankings['best'], array_reverse($rankings['worst']));
        
        // Remove duplicates
        $uniqueEmployees = collect($allEmployees)->unique('employee_id')->values();

        if ($format === 'csv') {
            return $this->exportCsv($uniqueEmployees, $startDate, $endDate);
        }

        return back()->with('error', __('messages.unsupported_format'));
    }

    /**
     * Export to CSV
     */
    private function exportCsv($employees, Carbon $startDate, Carbon $endDate)
    {
        $filename = 'attendance_analytics_' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, [
                __('messages.employee_name'),
                __('messages.department'),
                __('messages.attendance_rate') . ' (%)',
                __('messages.punctuality_rate') . ' (%)',
                __('messages.late_days'),
                __('messages.absent_days'),
                __('messages.total_late_minutes'),
                __('messages.avg_late_minutes'),
            ]);

            foreach ($employees as $emp) {
                fputcsv($file, [
                    $emp['employee_name'],
                    $emp['department'] ?? '-',
                    $emp['attendance_rate'],
                    $emp['punctuality_rate'],
                    $emp['late_days'],
                    $emp['absent_days'],
                    $emp['total_late_minutes'],
                    $emp['avg_late_minutes'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API endpoint for chart data (AJAX)
     */
    public function chartData(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $branchId = $request->get('branch_id');
        $chartType = $request->get('type', 'daily');
        
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date')) 
            : Carbon::now()->startOfMonth();
        
        $endDate = $request->get('end_date') 
            ? Carbon::parse($request->get('end_date')) 
            : Carbon::now()->endOfMonth();

        $data = match ($chartType) {
            'daily' => $this->analyticsService->getDailyTrend($companyId, $startDate, $endDate, $branchId),
            'weekly' => $this->analyticsService->getWeeklyPattern($companyId, $startDate, $endDate, $branchId),
            'late' => $this->analyticsService->getLateBreakdown($companyId, $startDate, $endDate, $branchId),
            default => [],
        };

        return response()->json($data);
    }
}
