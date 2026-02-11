<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // In multi-database tenancy, we don't need to filter by company_id
        // as the database connection is already scoped to the tenant.

        $stats = [
            'totalEmployees' => User::count(),
            'totalDevices' => Device::count(),
            'onlineDevices' => Device::where('status', 'online')->count(),
            'todayPresent' => AttendanceRecord::whereDate('punched_at', today())
                ->distinct('user_id')
                ->count(),
            'todayLate' => 0, // 'late_minutes' column missing, temporarily set to 0
        ];

        $timeline = AttendanceRecord::whereDate('punched_at', today())
            ->with('user:id,name')
            ->orderBy('punched_at', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($record) => [
                'id' => $record->id,
                'user' => ['name' => $record->user->name ?? 'Unknown'],
                'time' => $record->punched_at->format('H:i'),
                'type' => $record->type ?? 'in',
                'device' => $record->device?->name ?? 'Device',
            ]);

        return view('dashboard', [
            'stats' => $stats,
            'timeline' => $timeline,
        ]);
    }
}
