<?php

// =============================================================================
// Branch Model - Multi-Branch Support for Companies
// =============================================================================

namespace App\Models;

use App\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Branch extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'company_id',
        'name',
        'code',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'manager_name',
        'work_start_time',
        'work_end_time',
        'timezone',
        'is_headquarters',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_headquarters' => 'boolean',
        'is_active' => 'boolean',
        'work_start_time' => 'datetime:H:i',
        'work_end_time' => 'datetime:H:i',
    ];

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            // Auto-generate code if not provided
            if (empty($model->code)) {
                $count = static::withTrashed()->count();
                $model->code = 'BR' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    /**
     * Get the company that owns the branch.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all employees (users) assigned to this branch.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all devices assigned to this branch.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Get all attendance records for this branch.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to filter active branches.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to get headquarters.
     */
    public function scopeHeadquarters($query)
    {
        return $query->where('is_headquarters', true);
    }

    // =========================================================================
    // Accessors & Helpers
    // =========================================================================

    /**
     * Get full address.
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get employee count.
     */
    public function getEmployeeCountAttribute(): int
    {
        return $this->employees()->count();
    }

    /**
     * Get device count.
     */
    public function getDeviceCountAttribute(): int
    {
        return $this->devices()->count();
    }

    /**
     * Get display name with code.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->code) {
            return "{$this->name} ({$this->code})";
        }
        return $this->name;
    }
}
