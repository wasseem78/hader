<?php

// =============================================================================
// ApiToken Model - Device and API Authentication Tokens
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_id',
        'device_id',
        'user_id',
        'name',
        'token',
        'token_prefix',
        'abilities',
        'last_used_at',
        'last_used_ip',
        'usage_count',
        'expires_at',
        'is_active',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    protected $hidden = [
        'token',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Generate a new token.
     */
    public static function generateToken(): array
    {
        $plainText = Str::random(64);
        return [
            'plain' => $plainText,
            'hash' => hash('sha256', $plainText),
            'prefix' => substr($plainText, 0, 8),
        ];
    }

    /**
     * Log token usage.
     */
    public function recordUsage(string $ip): void
    {
        $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
            'usage_count' => $this->usage_count + 1,
        ]);
    }

    /**
     * Check if token is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->revoked_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Revoke the token.
     */
    public function revoke(?User $by = null): void
    {
        $this->update([
            'revoked_at' => now(),
            'revoked_by' => $by?->id,
            'is_active' => false,
        ]);
    }

    /**
     * Check if token has ability.
     */
    public function hasAbility(string $ability): bool
    {
        if (in_array('*', $this->abilities ?? [])) {
            return true;
        }

        return in_array($ability, $this->abilities ?? []);
    }
}
