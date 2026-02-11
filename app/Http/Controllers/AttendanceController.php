<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::where('company_id', auth()->user()->company_id)
            ->with(['user:id,name', 'device:id,name', 'branch:id,name']);

        // Filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('punch_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('punch_time', '<=', $request->date_to);
        }

        $records = $query->orderBy('punch_time', 'desc')->paginate(20)->withQueryString();
        
        $employees = User::where('company_id', auth()->user()->company_id)->get(['id', 'name']);
        $devices = Device::where('company_id', auth()->user()->company_id)->get(['id', 'name']);
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);

        return view('attendance.index', [
            'records' => $records,
            'employees' => $employees,
            'devices' => $devices,
            'branches' => $branches,
        ]);
    }

    public function timeline(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $companyId = auth()->user()->company_id;

        $query = AttendanceRecord::where('company_id', $companyId)
            ->whereDate('punch_time', $date)
            ->with(['user', 'device', 'branch']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $records = $query->orderBy('punch_time')->get();

        $branches = Branch::where('company_id', $companyId)->active()->get(['id', 'name']);

        return view('attendance.timeline', [
            'records' => $records,
            'date' => $date,
            'branches' => $branches,
        ]);
    }

    public function export(Request $request)
    {
        // Dispatch job for large exports
        // \App\Jobs\ExportReportJob::dispatch(auth()->user(), $request->all());
        
        // For now, direct CSV download
        $fileName = 'attendance-export-' . now()->format('Y-m-d') . '.csv';
        
        $query = AttendanceRecord::where('company_id', auth()->user()->company_id)
            ->with(['user', 'device', 'branch']);

        // Apply filters (same as index)
        if ($request->filled('branch_id')) $query->where('branch_id', $request->branch_id);
        if ($request->filled('employee_id')) $query->where('user_id', $request->employee_id);
        if ($request->filled('date_from')) $query->whereDate('punch_time', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('punch_time', '<=', $request->date_to);

        $records = $query->orderBy('punch_time', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Employee', 'Time', 'Type', 'Device', 'Status']);

            foreach ($records as $record) {
                fputcsv($file, [
                    $record->id,
                    $record->user->name ?? 'Unknown',
                    $record->punch_time,
                    $record->type,
                    $record->device->name ?? 'Manual',
                    $record->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
