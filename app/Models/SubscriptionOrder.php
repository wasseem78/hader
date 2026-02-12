<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SubscriptionOrder extends Model
{
    protected $connection = 'mysql'; // Central DB

    protected $fillable = [
        'uuid',
        'company_id',
        'plan_id',
        'type',
        'billing_cycle',
        'currency',
        'amount',
        'previous_plan_id',
        'status',
        'approved_by',
        'approved_at',
        'admin_notes',
        'rejection_reason',
        'customer_notes',
        'payment_reference',
        'invoice_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
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

    // ── Relationships ──────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function previousPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'previous_plan_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── Status checks ──────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // ── Helpers ─────────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'new' => __('messages.order_type_new'),
            'upgrade' => __('messages.order_type_upgrade'),
            'downgrade' => __('messages.order_type_downgrade'),
            'renewal' => __('messages.order_type_renewal'),
            default => $this->type,
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending' => __('messages.pending'),
            'approved' => __('messages.approved'),
            'rejected' => __('messages.rejected'),
            'cancelled' => __('messages.cancelled'),
            'expired' => __('messages.expired'),
            default => $this->status,
        };
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'info',
            'expired' => 'danger',
            default => 'info',
        };
    }

    public function getFormattedAmount(): string
    {
        return number_format($this->amount, 2) . ' ' . strtoupper($this->currency);
    }
}
