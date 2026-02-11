<?php

// =============================================================================
// Company Model - Tenant Entity for Multi-Tenant Architecture
// Includes billing, subscription management, and tenant configuration
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;
use Stancl\Tenancy\Database\Concerns;
use Stancl\Tenancy\Database\TenantCollection;
use Stancl\Tenancy\Events;

class Company extends Model implements TenantContract
{
    use HasFactory, SoftDeletes;
    use Concerns\CentralConnection,
        Concerns\HasDataColumn,
        Concerns\HasInternalKeys,
        Concerns\TenantRun,
        Concerns\InvalidatesResolverCache;
    use Concerns\HasDomains;

    protected $table = 'companies';
    protected $guarded = [];

    // Force auto-incrementing integer ID
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'domain',
        'subdomain',
        'email',
        'phone',
        'address',
        'logo',
        'timezone',
        'locale',
        'database',
        'plan_id',
        'trial_ends_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_subscription_status',
        'subscription_ends_at',
        'max_devices',
        'max_employees',
        'is_active',
        'suspended_at',
        'suspension_reason',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $dispatchesEvents = [
        'saving' => Events\SavingTenant::class,
        'saved' => Events\TenantSaved::class,
        'creating' => Events\CreatingTenant::class,
        'created' => Events\TenantCreated::class,
        'updating' => Events\UpdatingTenant::class,
        'updated' => Events\TenantUpdated::class,
        'deleting' => Events\DeletingTenant::class,
        'deleted' => Events\TenantDeleted::class,
    ];

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey()
    {
        return $this->getAttribute($this->getTenantKeyName());
    }

    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }

    /**
     * Get the name of the custom columns.
     *
     * @return array
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'uuid',
            'name',
            'slug',
            'domain',
            'subdomain',
            'email',
            'phone',
            'address',
            'logo',
            'timezone',
            'locale',
            'database',
            'plan_id',
            'trial_ends_at',
            'stripe_customer_id',
            'stripe_subscription_id',
            'stripe_subscription_status',
            'subscription_ends_at',
            'max_devices',
            'max_employees',
            'is_active',
            'suspended_at',
            'suspension_reason',
            'settings',
        ];
    }

    /**
     * Boot function to auto-generate UUID and slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
            if (empty($model->subdomain)) {
                $model->subdomain = $model->slug;
            }
            // Auto-create database name
            if (empty($model->database)) {
                $prefix = config('tenancy.database.prefix', 'tenant_');
                $model->database = $prefix . $model->slug;
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
     * Company's subscription plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Company's users (employees and admins).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Company's devices.
     */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    /**
     * Company's shifts.
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    /**
     * Company's attendance records.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Company's invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Company's API tokens.
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to active companies only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('suspended_at');
    }

    /**
     * Find company by domain.
     */
    public function scopeByDomain($query, string $domain)
    {
        return $query->where('domain', $domain);
    }

    /**
     * Find company by subdomain.
     */
    public function scopeBySubdomain($query, string $subdomain)
    {
        return $query->where('subdomain', $subdomain);
    }

    // =========================================================================
    // Billing Methods
    // =========================================================================

    /**
     * Check if company is on trial.
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if company has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->onTrial()) {
            return true;
        }

        // Free plan is always active
        if ($this->onFreePlan()) {
            return true;
        }

        // Check subscription status
        $activeStatuses = ['active', 'trialing', 'free'];
        if (!in_array($this->stripe_subscription_status, $activeStatuses)) {
            // Cancelled but still within period
            if ($this->stripe_subscription_status === 'cancelled'
                && $this->subscription_ends_at
                && $this->subscription_ends_at->isFuture()) {
                return true;
            }
            return false;
        }

        // If subscription has end date, check it hasn't expired
        if ($this->subscription_ends_at && $this->subscription_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if company is on free plan.
     */
    public function onFreePlan(): bool
    {
        return $this->plan?->slug === 'free';
    }

    /**
     * Get days remaining in trial.
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    // =========================================================================
    // Plan Limits
    // =========================================================================

    /**
     * Check if company can add more devices.
     */
    public function canAddDevice(): bool
    {
        return $this->devices()->count() < $this->max_devices;
    }

    /**
     * Check if company can add more employees.
     */
    public function canAddEmployee(): bool
    {
        return $this->users()->employees()->count() < $this->max_employees;
    }

    /**
     * Get device usage percentage.
     */
    public function deviceUsagePercent(): int
    {
        if ($this->max_devices === 0) {
            return 0;
        }

        return (int) (($this->devices()->count() / $this->max_devices) * 100);
    }

    /**
     * Get employee usage percentage.
     */
    public function employeeUsagePercent(): int
    {
        if ($this->max_employees === 0) {
            return 0;
        }

        return (int) (($this->users()->employees()->count() / $this->max_employees) * 100);
    }

    // =========================================================================
    // Settings Helpers
    // =========================================================================

    /**
     * Get a specific setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a specific setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
        $this->save();
    }
}
