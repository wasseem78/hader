<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        
        $query = User::where('company_id', $companyId)
            ->with(['branch', 'shifts', 'departmentRelation']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by department (new system)
        if ($departmentId = $request->get('department_id')) {
            $query->where('department_id', $departmentId);
        }
        
        // Legacy department filter (text field)
        if ($department = $request->get('department')) {
            $query->where('department', $department);
        }

        // Filter by branch
        if ($branchId = $request->get('branch')) {
            $query->where('branch_id', $branchId);
        }

        $employees = $query->paginate(20)->appends($request->query());
        
        // Get branches for filter dropdown
        $branches = Branch::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();
        
        // Get departments for filter dropdown
        $departments = Department::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('employees.index', [
            'employees' => $employees,
            'branches' => $branches,
            'departments' => $departments,
            'filters' => $request->only(['search', 'department', 'department_id', 'branch']),
        ]);
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        
        $branches = Branch::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $shifts = Shift::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();
        
        $departments = Department::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('employees.create', compact('branches', 'shifts', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'employee_id' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'device_user_id' => 'nullable|integer',
            'shifts' => 'nullable|array',
            'shifts.*' => 'exists:shifts,id',
        ]);

        $shiftIds = $validated['shifts'] ?? [];
        unset($validated['shifts']);

        $employee = User::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'password' => bcrypt('password'),
        ]);

        $employee->assignRole('employee');

        // Sync shifts with the employee
        if (!empty($shiftIds)) {
            $syncData = [];
            foreach ($shiftIds as $index => $shiftId) {
                $syncData[$shiftId] = [
                    'is_primary' => $index === 0, // First shift is primary
                    'effective_from' => now()->toDateString(),
                ];
            }
            $employee->shifts()->sync($syncData);
        }

        return redirect()->route('employees.index')->with('success', __('messages.employee_created'));
    }

    public function show(User $employee)
    {
        $employee->load(['branch', 'shifts']);
        return view('employees.show', ['employee' => $employee]);
    }

    public function edit(User $employee)
    {
        $companyId = auth()->user()->company_id;
        
        $employee->load(['shifts', 'departmentRelation']);
        
        $branches = Branch::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();

        $shifts = Shift::where('company_id', $companyId)
            ->active()
            ->orderBy('name')
            ->get();
        
        $departments = Department::where('company_id', $companyId)
            ->active()
            ->ordered()
            ->get();

        return view('employees.edit', [
            'employee' => $employee,
            'branches' => $branches,
            'shifts' => $shifts,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'device_user_id' => 'nullable|integer',
            'shifts' => 'nullable|array',
            'shifts.*' => 'exists:shifts,id',
        ]);

        $shiftIds = $validated['shifts'] ?? [];
        unset($validated['shifts']);
        
        // If department_id is set, also update the legacy department field
        if (!empty($validated['department_id'])) {
            $dept = Department::find($validated['department_id']);
            if ($dept) {
                $validated['department'] = $dept->name;
            }
        }

        $employee->update($validated);

        // Sync shifts with the employee
        $syncData = [];
        foreach ($shiftIds as $index => $shiftId) {
            $syncData[$shiftId] = [
                'is_primary' => $index === 0, // First shift is primary
                'effective_from' => now()->toDateString(),
            ];
        }
        $employee->shifts()->sync($syncData);
        
        return redirect()->route('employees.index')->with('success', __('messages.employee_updated'));
    }

    public function destroy(User $employee)
    {
        $employee->shifts()->detach(); // Remove shift associations
        $employee->delete();
        return redirect()->route('employees.index')->with('success', __('messages.employee_deleted'));
    }

    public function syncToDevice(User $employee)
    {
        return back()->with('success', __('messages.user_synced_to_device'));
    }
}
