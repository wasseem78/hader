<?php

// =============================================================================
// AttendanceRecord Model - Stores Punch In/Out Records
// Includes processing status, location data, and calculated work hours
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Traits\UsesTenantConnection;

class AttendanceRecord extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'user_id',
        'device_id',
        'shift_id',
        'punched_at',
        'punch_date',
        'punch_time',
        'type',
        'verification_type',
        'device_record_id',
        'latitude',
        'longitude',
        'location_name',
        'status',
        'is_late',
        'is_early_departure',
        'late_minutes',
        'early_minutes',
        'overtime_minutes',
        'work_duration_minutes',
        'break_duration_minutes',
        'notes',
        'adjusted_by',
        'adjusted_at',
        'adjustment_reason',
        'raw_data',
    ];

    protected $casts = [
        'punched_at' => 'datetime',
        'punch_date' => 'date',
        'adjusted_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_late' => 'boolean',
        'is_early_departure' => 'boolean',
        'late_minutes' => 'integer',
        'early_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'work_duration_minutes' => 'integer',
        'break_duration_minutes' => 'integer',
        'raw_data' => 'array',
    ];

    /**
     * Boot function to auto-generate UUID and derive date/time.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            // Auto-derive punch_date and punch_time from punched_at
            if ($model->punched_at) {
                $model->punch_date = $model->punched_at->toDateString();
                $model->punch_time = $model->punched_at->toTimeString();
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function adjustedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to today's records.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('punch_date', today());
    }

    /**
     * Scope to date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('punch_date', [$startDate, $endDate]);
    }

    /**
     * Scope to punch-ins only.
     */
    public function scopePunchIns($query)
    {
        return $query->where('type', 'in');
    }

    /**
     * Scope to punch-outs only.
     */
    public function scopePunchOuts($query)
    {
        return $query->where('type', 'out');
    }

    /**
     * Scope to late arrivals.
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Scope to processed records.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope to pending records.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Check if this is a punch-in.
     */
    public function isPunchIn(): bool
    {
        return $this->type === 'in';
    }

    /**
     * Check if this is a punch-out.
     */
    public function isPunchOut(): bool
    {
        return $this->type === 'out';
    }

    /**
     * Get formatted punched time.
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->punched_at?->format('h:i A') ?? '';
    }

    /**
     * Get verification type label.
     */
    public function getVerificationLabel(): string
    {
        return match ($this->verification_type) {
            'fingerprint' => __('Fingerprint'),
            'face' => __('Face Recognition'),
            'card' => __('RFID Card'),
            'password' => __('Password'),
            'manual' => __('Manual Entry'),
            default => __('Unknown'),
        };
    }

    /**
     * Get type label with icon.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'in' => __('Punch In'),
            'out' => __('Punch Out'),
            'break_start' => __('Break Start'),
            'break_end' => __('Break End'),
            'overtime_start' => __('Overtime Start'),
            'overtime_end' => __('Overtime End'),
            default => __('Unknown'),
        };
    }

    /**
     * Get work duration formatted.
     */
    public function getWorkDurationFormatted(): string
    {
        if (!$this->work_duration_minutes) {
            return '-';
        }

        $hours = floor($this->work_duration_minutes / 60);
        $minutes = $this->work_duration_minutes % 60;

        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Check if record has location.
     */
    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Mark record as processed.
     */
    public function markProcessed(array $data = []): void
    {
        $this->update(array_merge(['status' => 'processed'], $data));
    }
}
