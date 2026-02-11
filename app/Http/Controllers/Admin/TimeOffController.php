<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeOffRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimeOffController extends Controller
{
    public function index(Request $request)
    {
        $query = TimeOffRequest::where('company_id', auth()->user()->company_id)->with('user:id,name');
        
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('admin.time-off.index', [
            'requests' => $query->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.time-off.create');
    }

    public function store(Request $request)
    {
        TimeOffRequest::create([
            ...$request->only(['user_id', 'type', 'start_date', 'end_date', 'reason']),
            'company_id' => auth()->user()->company_id,
            'status' => 'pending',
        ]);
        return redirect()->route('admin.time-off.index');
    }

    public function approve(TimeOffRequest $timeOff)
    {
        $timeOff->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Request approved');
    }

    public function reject(Request $request, TimeOffRequest $timeOff)
    {
        $timeOff->update([
            'status' => 'rejected',
            'rejection_reason' => $request->get('reason'),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Request rejected');
    }
}
