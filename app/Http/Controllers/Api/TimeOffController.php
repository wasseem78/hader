<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeOffRequest;
use Illuminate\Http\Request;

class TimeOffController extends Controller
{
    public function index(Request $request, $tenant)
    {
        $query = TimeOffRequest::where('company_id', $tenant);
        
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return response()->json(['data' => $query->paginate(20)]);
    }

    public function store(Request $request, $tenant)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $timeOff = TimeOffRequest::create([
            ...$validated,
            'company_id' => $tenant,
            'status' => 'pending',
        ]);

        return response()->json(['data' => $timeOff], 201);
    }

    public function approve(Request $request, $tenant, $timeOff)
    {
        $request = TimeOffRequest::where('company_id', $tenant)->findOrFail($timeOff);
        $request->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return response()->json(['data' => $request]);
    }

    public function reject(Request $request, $tenant, $timeOff)
    {
        $timeOffRequest = TimeOffRequest::where('company_id', $tenant)->findOrFail($timeOff);
        $timeOffRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->get('reason'),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return response()->json(['data' => $timeOffRequest]);
    }
}
