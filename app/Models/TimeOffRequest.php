<?php

// =============================================================================
// TimeOffRequest Model - Leave/Vacation Requests
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TimeOffRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'company_id',
        'user_id',
        'type',
        'start_date',
        'end_date',
        'is_half_day',
        'half_day_period',
        'total_days',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_half_day' => 'boolean',
        'total_days' => 'decimal:2',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            // Calculate total days
            if ($model->start_date && $model->end_date) {
                $days = $model->start_date->diffInDays($model->end_date) + 1;
                $model->total_days = $model->is_half_day ? $days - 0.5 : $days;
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function approve(User $approver, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function reject(User $approver, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'annual_leave' => __('Annual Leave'),
            'sick_leave' => __('Sick Leave'),
            'personal_leave' => __('Personal Leave'),
            'maternity_leave' => __('Maternity Leave'),
            'paternity_leave' => __('Paternity Leave'),
            'bereavement_leave' => __('Bereavement Leave'),
            'unpaid_leave' => __('Unpaid Leave'),
            'work_from_home' => __('Work From Home'),
            default => __('Other'),
        };
    }
}
