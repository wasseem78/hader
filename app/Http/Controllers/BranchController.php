<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    /**
     * Display a listing of branches.
     */
    public function index(Request $request)
    {
        $query = Branch::where('company_id', auth()->user()->company_id)
            ->withCount('employees', 'devices');

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $branches = $query->orderBy('is_headquarters', 'desc')
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('branches.index', [
            'branches' => $branches,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new branch.
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('branches')->where('company_id', auth()->user()->company_id),
            ],
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'timezone' => 'nullable|string|max:50',
            'is_headquarters' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['is_headquarters'] = $request->boolean('is_headquarters');
        $validated['is_active'] = $request->boolean('is_active', true);

        // If this is set as headquarters, unset other headquarters
        if ($validated['is_headquarters']) {
            Branch::where('company_id', $validated['company_id'])
                ->where('is_headquarters', true)
                ->update(['is_headquarters' => false]);
        }

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', __('messages.branch_created'));
    }

    /**
     * Display the specified branch.
     */
    public function show(Branch $branch)
    {
        $this->authorize('view', $branch);

        $branch->load(['employees', 'devices']);

        return view('branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch)
    {
        $this->authorize('update', $branch);

        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('branches')
                    ->where('company_id', auth()->user()->company_id)
                    ->ignore($branch->id),
            ],
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'work_start_time' => 'nullable|date_format:H:i',
            'work_end_time' => 'nullable|date_format:H:i',
            'timezone' => 'nullable|string|max:50',
            'is_headquarters' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_headquarters'] = $request->boolean('is_headquarters');
        $validated['is_active'] = $request->boolean('is_active', true);

        // If this is set as headquarters, unset other headquarters
        if ($validated['is_headquarters'] && !$branch->is_headquarters) {
            Branch::where('company_id', $branch->company_id)
                ->where('is_headquarters', true)
                ->where('id', '!=', $branch->id)
                ->update(['is_headquarters' => false]);
        }

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', __('messages.branch_updated'));
    }

    /**
     * Remove the specified branch.
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);

        // Check if branch has employees
        if ($branch->employees()->count() > 0) {
            return redirect()->route('branches.index')
                ->with('error', __('messages.branch_has_employees'));
        }

        // Check if branch has devices
        if ($branch->devices()->count() > 0) {
            return redirect()->route('branches.index')
                ->with('error', __('messages.branch_has_devices'));
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', __('messages.branch_deleted'));
    }
}
