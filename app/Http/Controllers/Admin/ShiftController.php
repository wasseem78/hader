<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $query = Shift::where('company_id', auth()->user()->company_id)
            ->with('branch:id,name');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $shifts = $query->get();
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);

        return view('admin.shifts.index', [
            'shifts' => $shifts,
            'branches' => $branches,
        ]);
    }

    public function create()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);
        return view('admin.shifts.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'working_days' => 'nullable|array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        Shift::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
        ]);
        return redirect()->route('admin.shifts.index')->with('success', __('messages.shift_created'));
    }

    public function show(Shift $shift)
    {
        // Verify ownership
        if ($shift->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        $shift->load('branch');
        return view('admin.shifts.show', ['shift' => $shift]);
    }

    public function edit(Shift $shift)
    {
        // Verify ownership
        if ($shift->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);
        return view('admin.shifts.edit', ['shift' => $shift, 'branches' => $branches]);
    }

    public function update(Request $request, Shift $shift)
    {
        // Verify ownership
        if ($shift->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'working_days' => 'nullable|array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
        
        $shift->update($validated);
        return redirect()->route('admin.shifts.index')->with('success', __('messages.shift_updated'));
    }

    public function destroy(Shift $shift)
    {
        // Verify ownership
        if ($shift->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        $shift->delete();
        return redirect()->route('admin.shifts.index')->with('success', __('messages.shift_deleted'));
    }
}
