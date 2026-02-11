<?php

// =============================================================================
// User Model - Extended for Multi-Tenant SaaS with Device Enrollment
// =============================================================================

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

use App\Traits\UsesTenantConnection;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes, UsesTenantConnection;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'department_id',
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'locale',
        'timezone',
        'employee_id',
        'department',
        'position',
        'hire_date',
        'device_user_id',
        'card_number',
        'fingerprint_count',
        'face_enrolled',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_active' => 'boolean',
        'face_enrolled' => 'boolean',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * User belongs to a company (tenant).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * User belongs to a branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * User belongs to a department.
     */
    public function departmentRelation(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * User's attendance records.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * User's time off requests.
     */
    public function timeOffRequests(): HasMany
    {
        return $this->hasMany(TimeOffRequest::class);
    }

    /**
     * User's assigned shifts.
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class)
            ->withPivot('effective_from', 'effective_to', 'is_primary')
            ->withTimestamps();
    }

    // TODO: Uncomment when LeaveBalance model is created
    // /**
    //  * User's leave balances.
    //  */
    // public function leaveBalances(): HasMany
    // {
    //     return $this->hasMany(LeaveBalance::class);
    // }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to active users only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to employees (non-admin users).
     */
    public function scopeEmployees($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'employee');
        });
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user is a company admin.
     */
    public function isCompanyAdmin(): bool
    {
        return $this->hasRole('company-admin');
    }

    /**
     * Get current shift for user.
     */
    public function getCurrentShift(): ?Shift
    {
        return $this->shifts()
            ->where('is_primary', true)
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now());
            })
            ->first();
    }

    /**
     * Get today's attendance records.
     */
    public function getTodayAttendance()
    {
        return $this->attendanceRecords()
            ->whereDate('punch_date', today())
            ->orderBy('punched_at')
            ->get();
    }
}
