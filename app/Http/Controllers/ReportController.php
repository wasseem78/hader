<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
// use Barryvdh\DomPDF\Facade\Pdf; // Assuming installed

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');
        
        // In multi-tenant setup, the DB is already scoped to the tenant
        $query = AttendanceRecord::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        
        // Basic Summary Stats
        $summary = [
            'attendance' => [
                'total_records' => (clone $query)->count(),
            ],
            'punctuality' => [
                'on_time' => (clone $query)->where('late_minutes', 0)->count(),
                'late_count' => (clone $query)->where('late_minutes', '>', 0)->count(),
            ]
        ];

        $branches = Branch::active()->get(['id', 'name']);

        return view('reports.index', [
            'summary' => $summary,
            'branches' => $branches,
            'selectedBranch' => $branchId,
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'csv');
        $reportType = $request->input('report_type', 'daily');
        $date = $request->input('date', today()->toDateString());
        $branchId = $request->input('branch_id');
        
        $query = AttendanceRecord::query()
            ->with(['user', 'branch']);

        // Branch filter
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($reportType === 'daily') {
            $query->whereDate('punch_date', $date);
        } elseif ($reportType === 'monthly') {
            $query->whereMonth('punch_date', Carbon::parse($date)->month)
                  ->whereYear('punch_date', Carbon::parse($date)->year);
        }

        $records = $query->orderBy('punched_at')->get();

        if ($type === 'pdf') {
            // PDF Export Logic (Simulated)
            // $pdf = Pdf::loadView('reports.pdf', ['records' => $records, 'date' => $date]);
            // return $pdf->download("report-{$reportType}-{$date}.pdf");
            
            return response()->streamDownload(function () use ($records, $date, $reportType) {
                echo "PDF Export for $reportType on $date\n";
                echo "Total Records: " . $records->count() . "\n";
                // In real app, render Blade view to HTML and pass to DomPDF
            }, "report-{$reportType}-{$date}.txt"); // Fallback to txt for now
        } else {
            // CSV Export
            $fileName = "report-{$reportType}-{$date}.csv";
            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            return response()->stream(function() use ($records) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Employee', 'Date', 'Time', 'Type', 'Status']);
                foreach ($records as $record) {
                    fputcsv($file, [
                        $record->user->name ?? 'Unknown',
                        $record->punch_date,
                        $record->punch_time,
                        $record->type,
                        $record->status
                    ]);
                }
                fclose($file);
            }, 200, $headers);
        }
    }
}
