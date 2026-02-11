<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function summary(Request $request, $tenant)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $summary = [
            'period' => [
                'from' => $from,
                'to' => $to,
                'total_days' => now()->parse($from)->diffInDays(now()->parse($to)) + 1,
            ],
            'attendance' => [
                'total_records' => AttendanceRecord::where('company_id', $tenant)
                    ->whereBetween('punch_time', [$from, $to])
                    ->count(),
                'unique_employees' => AttendanceRecord::where('company_id', $tenant)
                    ->whereBetween('punch_time', [$from, $to])
                    ->distinct('user_id')
                    ->count(),
            ],
            'punctuality' => [
                'on_time' => AttendanceRecord::where('company_id', $tenant)
                    ->whereBetween('punch_time', [$from, $to])
                    ->where('late_minutes', 0)
                    ->count(),
                'late_count' => AttendanceRecord::where('company_id', $tenant)
                    ->whereBetween('punch_time', [$from, $to])
                    ->where('late_minutes', '>', 0)
                    ->count(),
                'average_late_minutes' => round(AttendanceRecord::where('company_id', $tenant)
                    ->whereBetween('punch_time', [$from, $to])
                    ->where('late_minutes', '>', 0)
                    ->avg('late_minutes') ?? 0),
            ],
            'overtime' => [
                'total_hours' => round(AttendanceRecord::where('company_id', $tenant)
                    ->whereBetween('punch_time', [$from, $to])
                    ->sum('overtime_minutes') / 60, 2),
            ],
        ];

        return response()->json($summary);
    }

    public function daily(Request $request, $tenant)
    {
        $from = $request->get('from', now()->subDays(30)->toDateString());
        $to = $request->get('to', now()->toDateString());

        $dailyData = AttendanceRecord::where('company_id', $tenant)
            ->whereBetween('punch_time', [$from, $to])
            ->selectRaw('DATE(punch_time) as date, COUNT(*) as total, SUM(late_minutes > 0) as late')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($day) => [
                'date' => $day->date,
                'present' => $day->total,
                'late' => $day->late,
                'on_time' => $day->total - $day->late,
            ]);

        return response()->json(['data' => $dailyData]);
    }

    public function employee(Request $request, $tenant, $employee)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $records = AttendanceRecord::where('company_id', $tenant)
            ->where('user_id', $employee)
            ->whereBetween('punch_time', [$from, $to])
            ->orderBy('punch_time', 'desc')
            ->get();

        return response()->json(['data' => $records]);
    }
}
