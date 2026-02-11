<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request, $tenant)
    {
        $shifts = Shift::where('company_id', $tenant)->get();
        return response()->json(['data' => $shifts]);
    }

    public function store(Request $request, $tenant)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'working_days' => 'nullable|array',
        ]);

        $shift = Shift::create([
            ...$validated,
            'company_id' => $tenant,
        ]);

        return response()->json(['data' => $shift], 201);
    }

    public function show($tenant, $shift)
    {
        $shift = Shift::where('company_id', $tenant)->findOrFail($shift);
        return response()->json(['data' => $shift]);
    }

    public function update(Request $request, $tenant, $shift)
    {
        $shift = Shift::where('company_id', $tenant)->findOrFail($shift);
        $shift->update($request->only(['name', 'code', 'start_time', 'end_time', 'working_days']));
        return response()->json(['data' => $shift]);
    }

    public function destroy($tenant, $shift)
    {
        $shift = Shift::where('company_id', $tenant)->findOrFail($shift);
        $shift->delete();
        return response()->json(['message' => 'Shift deleted']);
    }
}
