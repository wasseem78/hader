<?php

// =============================================================================
// Attendance API Controller - REST Endpoints for Attendance Records
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Get attendance records for a tenant.
     *
     * GET /api/tenants/{tenant}/attendance
     */
    public function index(Request $request, Company $tenant): JsonResponse
    {
        $query = $tenant->attendanceRecords()
            ->with(['user:id,uuid,name,employee_id', 'device:id,uuid,name', 'shift:id,name'])
            ->orderBy('punched_at', 'desc');

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('punch_date', '>=', $request->from);
        } else {
            $query->whereDate('punch_date', '>=', now()->subDays(7));
        }

        if ($request->has('to')) {
            $query->whereDate('punch_date', '<=', $request->to);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->whereHas('user', fn($q) => $q->where('uuid', $request->user_id));
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate($request->per_page ?? 50);

        return response()->json($records);
    }

    /**
     * Get today's attendance.
     *
     * GET /api/tenants/{tenant}/attendance/today
     */
    public function today(Company $tenant): JsonResponse
    {
        $records = $tenant->attendanceRecords()
            ->with(['user:id,uuid,name,employee_id,avatar', 'device:id,uuid,name'])
            ->whereDate('punch_date', today())
            ->orderBy('punched_at', 'desc')
            ->get();

        // Group by user
        $byUser = $records->groupBy('user_id')->map(function ($userRecords) {
            $user = $userRecords->first()->user;
            $firstIn = $userRecords->where('type', 'in')->first();
            $lastOut = $userRecords->where('type', 'out')->last();

            return [
                'user' => $user,
                'first_in' => $firstIn?->punched_at,
                'last_out' => $lastOut?->punched_at,
                'total_punches' => $userRecords->count(),
                'is_late' => $firstIn?->is_late ?? false,
                'late_minutes' => $firstIn?->late_minutes ?? 0,
                'records' => $userRecords,
            ];
        });

        return response()->json([
            'data' => $byUser->values(),
            'meta' => [
                'date' => today()->toDateString(),
                'total_employees' => $tenant->users()->employees()->count(),
                'present' => $byUser->count(),
                'late' => $byUser->where('is_late', true)->count(),
            ],
        ]);
    }

    /**
     * Get attendance timeline.
     *
     * GET /api/tenants/{tenant}/attendance/timeline
     */
    public function timeline(Request $request, Company $tenant): JsonResponse
    {
        $date = $request->date ? new \DateTime($request->date) : today();

        $records = $tenant->attendanceRecords()
            ->with(['user:id,uuid,name,avatar', 'device:id,name'])
            ->whereDate('punch_date', $date)
            ->orderBy('punched_at', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->uuid,
                    'user' => [
                        'id' => $record->user->uuid,
                        'name' => $record->user->name,
                        'avatar' => $record->user->avatar,
                    ],
                    'time' => $record->punched_at->format('H:i'),
                    'type' => $record->type,
                    'type_label' => $record->getTypeLabel(),
                    'device' => $record->device?->name,
                    'verification' => $record->getVerificationLabel(),
                    'is_late' => $record->is_late,
                    'late_minutes' => $record->late_minutes,
                ];
            });

        return response()->json([
            'data' => $records,
            'meta' => [
                'date' => $date->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get attendance summary/statistics.
     *
     * GET /api/tenants/{tenant}/attendance/summary
     */
    public function summary(Request $request, Company $tenant): JsonResponse
    {
        $startDate = $request->from ? new \DateTime($request->from) : now()->startOfMonth();
        $endDate = $request->to ? new \DateTime($request->to) : now();

        $records = $tenant->attendanceRecords()
            ->whereBetween('punch_date', [$startDate, $endDate])
            ->get();

        // Calculate statistics
        $totalDays = $startDate->diff($endDate)->days + 1;
        $uniqueDaysWithAttendance = $records->pluck('punch_date')->unique()->count();
        $lateCount = $records->where('is_late', true)->count();
        $totalLateMinutes = $records->sum('late_minutes');
        $totalOvertimeMinutes = $records->sum('overtime_minutes');

        // Daily breakdown
        $dailyStats = $records->groupBy(fn($r) => $r->punch_date->format('Y-m-d'))
            ->map(function ($dayRecords, $date) {
                return [
                    'date' => $date,
                    'present' => $dayRecords->pluck('user_id')->unique()->count(),
                    'late' => $dayRecords->where('is_late', true)->pluck('user_id')->unique()->count(),
                    'on_time' => $dayRecords->where('is_late', false)->pluck('user_id')->unique()->count(),
                ];
            })
            ->values();

        return response()->json([
            'data' => [
                'period' => [
                    'from' => $startDate->format('Y-m-d'),
                    'to' => $endDate->format('Y-m-d'),
                    'total_days' => $totalDays,
                ],
                'attendance' => [
                    'days_with_records' => $uniqueDaysWithAttendance,
                    'total_records' => $records->count(),
                ],
                'punctuality' => [
                    'late_count' => $lateCount,
                    'total_late_minutes' => $totalLateMinutes,
                    'average_late_minutes' => $lateCount > 0 ? round($totalLateMinutes / $lateCount) : 0,
                ],
                'overtime' => [
                    'total_minutes' => $totalOvertimeMinutes,
                    'total_hours' => round($totalOvertimeMinutes / 60, 1),
                ],
                'daily' => $dailyStats,
            ],
        ]);
    }

    /**
     * Export attendance records.
     *
     * GET /api/tenants/{tenant}/attendance/export
     */
    public function export(Request $request, Company $tenant): JsonResponse
    {
        $query = $tenant->attendanceRecords()
            ->with(['user:id,name,employee_id', 'device:id,name', 'shift:id,name'])
            ->orderBy('punch_date')
            ->orderBy('punched_at');

        if ($request->has('from')) {
            $query->whereDate('punch_date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('punch_date', '<=', $request->to);
        }

        $records = $query->get()->map(function ($record) {
            return [
                'date' => $record->punch_date->format('Y-m-d'),
                'time' => $record->punch_time,
                'employee_id' => $record->user->employee_id,
                'employee_name' => $record->user->name,
                'type' => $record->type,
                'device' => $record->device?->name,
                'verification' => $record->verification_type,
                'is_late' => $record->is_late ? 'Yes' : 'No',
                'late_minutes' => $record->late_minutes,
                'shift' => $record->shift?->name,
            ];
        });

        // TODO: Generate actual Excel/CSV file
        return response()->json([
            'data' => $records,
            'meta' => [
                'total' => $records->count(),
                'format' => $request->format ?? 'json',
            ],
        ]);
    }

    /**
     * Get attendance for a specific employee.
     *
     * GET /api/tenants/{tenant}/employees/{employee}/attendance
     */
    public function employeeAttendance(Request $request, Company $tenant, User $employee): JsonResponse
    {
        $query = $employee->attendanceRecords()
            ->with(['device:id,uuid,name', 'shift:id,name'])
            ->orderBy('punched_at', 'desc');

        if ($request->has('from')) {
            $query->whereDate('punch_date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('punch_date', '<=', $request->to);
        }

        $records = $query->paginate($request->per_page ?? 50);

        return response()->json($records);
    }
}
