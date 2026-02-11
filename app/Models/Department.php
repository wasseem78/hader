<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'branch_id',
        'manager_id',
        'parent_id',
        'name',
        'code',
        'color',
        'description',
        'phone',
        'email',
        'location',
        'sort_order',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($department) {
            // Auto-generate code if not provided
            if (empty($department->code)) {
                $department->code = static::generateCode($department->name, $department->company_id);
            }
        });
    }

    /**
     * Generate a unique department code from name
     */
    public static function generateCode(string $name, int $companyId): string
    {
        // Extract first letters of each word
        $words = preg_split('/\s+/', trim($name));
        $code = '';
        foreach ($words as $word) {
            $code .= mb_strtoupper(mb_substr($word, 0, 1));
        }
        
        // Ensure minimum 2 characters
        if (mb_strlen($code) < 2) {
            $code = mb_strtoupper(mb_substr($name, 0, 3));
        }
        
        // Check for uniqueness and add number if needed
        $baseCode = $code;
        $counter = 1;
        while (static::where('company_id', $companyId)->where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Get the branch that owns the department.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the manager of the department.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the parent department.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get the child departments.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants (recursive children).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all employees in this department.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    /**
     * Get active employees count.
     */
    public function activeEmployeesCount(): int
    {
        return $this->employees()->where('is_active', true)->count();
    }

    /**
     * Get all employees including sub-departments.
     */
    public function allEmployees()
    {
        $departmentIds = $this->getAllDescendantIds();
        $departmentIds[] = $this->id;
        
        return User::whereIn('department_id', $departmentIds);
    }

    /**
     * Get all descendant department IDs.
     */
    public function getAllDescendantIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        return $ids;
    }

    /**
     * Get the full hierarchy path (e.g., "Company > Division > Department")
     */
    public function getHierarchyPath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get depth level in hierarchy (0 = root)
     */
    public function getDepthLevel(): int
    {
        $level = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        
        return $level;
    }

    /**
     * Scope to filter active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter root departments (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to order by sort order then name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if this department can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Cannot delete if has employees
        if ($this->employees()->exists()) {
            return false;
        }
        
        // Cannot delete if has child departments
        if ($this->children()->exists()) {
            return false;
        }
        
        return true;
    }

    /**
     * Get statistics for this department.
     */
    public function getStatistics(): array
    {
        $employees = $this->employees()->where('is_active', true);
        
        return [
            'total_employees' => $employees->count(),
            'sub_departments' => $this->children()->count(),
            'total_with_sub' => $this->allEmployees()->where('is_active', true)->count(),
        ];
    }
}
