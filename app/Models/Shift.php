<?php

// =============================================================================
// Shift Model - Work Schedule Definition
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Traits\UsesTenantConnection;

class Shift extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'name',
        'code',
        'description',
        'start_time',
        'end_time',
        'next_day_end',
        'work_hours',
        'break_start',
        'break_end',
        'break_duration_minutes',
        'break_deducted',
        'grace_period_minutes',
        'early_departure_threshold',
        'overtime_threshold_minutes',
        'working_days',
        'is_default',
        'is_active',
        'color',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'break_start' => 'datetime:H:i',
        'break_end' => 'datetime:H:i',
        'next_day_end' => 'boolean',
        'break_deducted' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'working_days' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('effective_from', 'effective_to', 'is_primary')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if a given day is a working day.
     */
    public function isWorkingDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->working_days ?? [1, 2, 3, 4, 5]);
    }

    /**
     * Check if a punch time is late.
     */
    public function isLate(\DateTime $punchTime): bool
    {
        $shiftStart = \Carbon\Carbon::parse($this->start_time);
        $graceEnd = $shiftStart->copy()->addMinutes($this->grace_period_minutes);

        return $punchTime > $graceEnd;
    }

    /**
     * Calculate late minutes.
     */
    public function calculateLateMinutes(\DateTime $punchTime): int
    {
        $shiftStart = \Carbon\Carbon::parse($this->start_time);
        $graceEnd = $shiftStart->copy()->addMinutes($this->grace_period_minutes);

        if ($punchTime <= $graceEnd) {
            return 0;
        }

        return $graceEnd->diffInMinutes($punchTime);
    }
}
