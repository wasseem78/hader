<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = Department::where('company_id', $companyId)
            ->with(['branch', 'manager', 'parent', 'children'])
            ->withCount(['employees' => function ($q) {
                $q->where('is_active', true);
            }]);

        // Filter by branch
        if ($branchId = $request->get('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        // Filter by parent (show root or children)
        if ($request->has('parent_id')) {
            if ($request->get('parent_id') === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->get('parent_id'));
            }
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->ordered()->get();

        // Build hierarchical structure for display
        $hierarchicalDepartments = $this->buildHierarchy($departments);

        // Get branches for filter
        $branches = Branch::where('company_id', $companyId)->active()->get();

        // Statistics
        $stats = [
            'total' => Department::where('company_id', $companyId)->count(),
            'active' => Department::where('company_id', $companyId)->active()->count(),
            'root' => Department::where('company_id', $companyId)->root()->count(),
            'with_employees' => Department::where('company_id', $companyId)
                ->whereHas('employees', function ($q) {
                    $q->where('is_active', true);
                })->count(),
        ];

        return view('departments.index', compact(
            'departments',
            'hierarchicalDepartments',
            'branches',
            'stats'
        ));
    }

    /**
     * Build hierarchical structure from flat list.
     */
    private function buildHierarchy($departments, $parentId = null, $level = 0): array
    {
        $result = [];
        
        foreach ($departments as $department) {
            if ($department->parent_id == $parentId) {
                $department->hierarchy_level = $level;
                $result[] = $department;
                
                // Add children recursively
                $children = $this->buildHierarchy($departments, $department->id, $level + 1);
                $result = array_merge($result, $children);
            }
        }
        
        return $result;
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;

        $branches = Branch::where('company_id', $companyId)->active()->get();
        $departments = Department::where('company_id', $companyId)->active()->ordered()->get();
        $employees = User::where('company_id', $companyId)->where('is_active', true)->get();

        // Build hierarchical options for parent select
        $parentOptions = $this->buildSelectOptions($departments);

        return view('departments.create', compact('branches', 'departments', 'employees', 'parentOptions'));
    }

    /**
     * Build select options with hierarchy indication.
     */
    private function buildSelectOptions($departments, $parentId = null, $prefix = ''): array
    {
        $options = [];
        
        foreach ($departments as $department) {
            if ($department->parent_id == $parentId) {
                $options[] = [
                    'id' => $department->id,
                    'name' => $prefix . $department->name,
                    'code' => $department->code,
                ];
                
                // Add children with increased prefix
                $children = $this->buildSelectOptions($departments, $department->id, $prefix . '── ');
                $options = array_merge($options, $children);
            }
        }
        
        return $options;
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('departments')->where('company_id', $companyId),
            ],
            'branch_id' => 'nullable|integer|exists:branches,id',
            'parent_id' => 'nullable|integer|exists:departments,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert empty strings to null for nullable foreign keys
        $validated['branch_id'] = !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null;
        $validated['parent_id'] = !empty($validated['parent_id']) ? (int)$validated['parent_id'] : null;
        $validated['manager_id'] = !empty($validated['manager_id']) ? (int)$validated['manager_id'] : null;
        
        $validated['company_id'] = $companyId;
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // If parent is set, inherit branch from parent if not specified
        if (!empty($validated['parent_id']) && empty($validated['branch_id'])) {
            $parent = Department::find($validated['parent_id']);
            if ($parent) {
                $validated['branch_id'] = $parent->branch_id;
            }
        }

        $department = Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', __('messages.department_created'));
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $this->authorize('view', $department);

        $department->load(['branch', 'manager', 'parent', 'children.employees', 'employees']);

        // Get department statistics
        $stats = $department->getStatistics();

        // Get recent attendance for department employees
        $recentAttendance = \App\Models\AttendanceRecord::whereIn('user_id', $department->employees->pluck('id'))
            ->with('user')
            ->orderBy('punch_time', 'desc')
            ->limit(10)
            ->get();

        return view('departments.show', compact('department', 'stats', 'recentAttendance'));
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        $companyId = Auth::user()->company_id;

        // Check ownership
        if ($department->company_id !== $companyId) {
            abort(403);
        }

        $branches = Branch::where('company_id', $companyId)->active()->get();
        
        // Exclude this department and its descendants from parent options
        $excludeIds = $department->getAllDescendantIds();
        $excludeIds[] = $department->id;
        
        $departments = Department::where('company_id', $companyId)
            ->whereNotIn('id', $excludeIds)
            ->active()
            ->ordered()
            ->get();
        
        $employees = User::where('company_id', $companyId)->where('is_active', true)->get();

        // Build hierarchical options for parent select
        $parentOptions = $this->buildSelectOptions($departments);

        return view('departments.edit', compact('department', 'branches', 'departments', 'employees', 'parentOptions'));
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $companyId = Auth::user()->company_id;

        // Check ownership
        if ($department->company_id !== $companyId) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('departments')->where('company_id', $companyId)->ignore($department->id),
            ],
            'branch_id' => 'nullable|integer|exists:branches,id',
            'parent_id' => 'nullable|integer|exists:departments,id',
            'manager_id' => 'nullable|integer|exists:users,id',
            'color' => 'nullable|string|max:7',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'location' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Convert empty strings to null for nullable foreign keys
        $validated['branch_id'] = !empty($validated['branch_id']) ? (int)$validated['branch_id'] : null;
        $validated['parent_id'] = !empty($validated['parent_id']) ? (int)$validated['parent_id'] : null;
        $validated['manager_id'] = !empty($validated['manager_id']) ? (int)$validated['manager_id'] : null;

        // Prevent circular reference
        if (!empty($validated['parent_id'])) {
            $descendantIds = $department->getAllDescendantIds();
            if (in_array($validated['parent_id'], $descendantIds) || $validated['parent_id'] == $department->id) {
                return back()->withErrors(['parent_id' => __('messages.circular_reference_error')]);
            }
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', __('messages.department_updated'));
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        $companyId = Auth::user()->company_id;

        // Check ownership
        if ($department->company_id !== $companyId) {
            abort(403);
        }

        // Check if can be deleted
        if (!$department->canBeDeleted()) {
            $message = $department->employees()->exists() 
                ? __('messages.department_has_employees')
                : __('messages.department_has_children');
            
            return back()->with('error', $message);
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', __('messages.department_deleted'));
    }

    /**
     * Get departments for AJAX requests.
     */
    public function getByBranch(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = $request->get('branch_id');

        $query = Department::where('company_id', $companyId)->active();
        
        if ($branchId) {
            $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
            });
        }

        $departments = $query->ordered()->get(['id', 'name', 'code', 'color', 'parent_id']);

        return response()->json($departments);
    }

    /**
     * Reorder departments.
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:departments,id',
            'order.*.sort_order' => 'required|integer|min:0',
        ]);

        $companyId = Auth::user()->company_id;

        foreach ($request->order as $item) {
            Department::where('id', $item['id'])
                ->where('company_id', $companyId)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Move employees to another department.
     */
    public function moveEmployees(Request $request, Department $department)
    {
        $companyId = Auth::user()->company_id;

        if ($department->company_id !== $companyId) {
            abort(403);
        }

        $request->validate([
            'target_department_id' => 'required|exists:departments,id',
        ]);

        $targetDepartment = Department::where('id', $request->target_department_id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Move all employees
        $department->employees()->update(['department_id' => $targetDepartment->id]);

        return back()->with('success', __('messages.employees_moved'));
    }
}
