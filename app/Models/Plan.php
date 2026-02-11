<?php

// =============================================================================
// Plan Model - Subscription Tier Definition
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql'; // Force central connection (assuming 'mysql' is central)

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'stripe_price_monthly_id',
        'stripe_price_yearly_id',
        'stripe_product_id',
        'max_devices',
        'max_employees',
        'max_users',
        'retention_days',
        'api_access',
        'advanced_reports',
        'custom_branding',
        'priority_support',
        'features',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_devices' => 'integer',
        'max_employees' => 'integer',
        'max_users' => 'integer',
        'retention_days' => 'integer',
        'api_access' => 'boolean',
        'advanced_reports' => 'boolean',
        'custom_branding' => 'boolean',
        'priority_support' => 'boolean',
        'features' => 'array',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

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
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function isFree(): bool
    {
        return $this->price_monthly == 0;
    }

    public function getYearlySavings(): float
    {
        return ($this->price_monthly * 12) - $this->price_yearly;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }
}
