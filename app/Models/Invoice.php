<?php

// =============================================================================
// Invoice Model - Billing Invoices
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mysql'; // Central connection (invoices FK to companies)

    protected $fillable = [
        'uuid',
        'company_id',
        'plan_id',
        'stripe_invoice_id',
        'stripe_payment_intent_id',
        'number',
        'invoice_date',
        'due_date',
        'paid_date',
        'currency',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'payment_method',
        'receipt_url',
        'pdf_url',
        'period_start',
        'period_end',
        'line_items',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'line_items' => 'array',
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markPaid(?string $paymentIntentId = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
            'stripe_payment_intent_id' => $paymentIntentId ?? $this->stripe_payment_intent_id,
        ]);
    }

    public function getFormattedTotal(): string
    {
        return number_format($this->total, 2) . ' ' . strtoupper($this->currency);
    }
}
