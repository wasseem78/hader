<?php

// =============================================================================
// Device Model - ZKTeco Fingerprint/Face Recognition Device
// Manages device configuration, connectivity, and synchronization status
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

use App\Traits\UsesTenantConnection;

class Device extends Model
{
    use HasFactory, SoftDeletes, UsesTenantConnection;

    protected $fillable = [
        'uuid',
        'company_id',
        'branch_id',
        'name',
        'serial_number',
        'model',
        'location',
        'ip_address',
        'port',
        'protocol',
        'connection_mode',
        'push_server_url',
        'push_port',
        'auth_key',
        'comm_password',
        'timezone',
        'sync_time',
        'sync_interval',
        'status',
        'last_seen',
        'last_sync',
        'last_push_received',
        'last_error',
        'total_users',
        'total_fingerprints',
        'total_logs',
        'push_records_today',
        'capabilities',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'port' => 'integer',
        'push_port' => 'integer',
        'sync_time' => 'boolean',
        'sync_interval' => 'integer',
        'last_seen' => 'datetime',
        'last_sync' => 'datetime',
        'last_push_received' => 'datetime',
        'total_users' => 'integer',
        'total_fingerprints' => 'integer',
        'total_logs' => 'integer',
        'push_records_today' => 'integer',
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $hidden = [
        'auth_key',
        'comm_password',
    ];

    /**
     * Boot function to auto-generate UUID.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
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
     * Device belongs to a company.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Device belongs to a branch.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Device's attendance records.
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Device's API tokens.
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Scope to active devices.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to online devices.
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope to devices needing sync.
     */
    public function scopeNeedsSync($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('last_sync')
                    ->orWhereRaw('last_sync < DATE_SUB(NOW(), INTERVAL sync_interval MINUTE)');
            });
    }

    // =========================================================================
    // Status Methods
    // =========================================================================

    /**
     * Check if device is online.
     */
    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    /**
     * Mark device as online.
     */
    public function markOnline(): void
    {
        $this->update([
            'status' => 'online',
            'last_seen' => now(),
            'last_error' => null,
        ]);
    }

    /**
     * Mark device as offline.
     */
    public function markOffline(?string $error = null): void
    {
        $this->update([
            'status' => 'offline',
            'last_error' => $error,
        ]);
    }

    /**
     * Mark device as syncing.
     */
    public function markSyncing(): void
    {
        $this->update(['status' => 'syncing']);
    }

    /**
     * Mark sync complete.
     */
    public function markSyncComplete(int $logsImported = 0): void
    {
        $this->update([
            'status' => 'online',
            'last_sync' => now(),
            'total_logs' => $this->total_logs + $logsImported,
        ]);
    }

    // =========================================================================
    // Connection Helpers
    // =========================================================================

    /**
     * Get connection address string.
     */
    public function getConnectionString(): string
    {
        return "{$this->ip_address}:{$this->port}";
    }

    /**
     * Get device capabilities.
     */
    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /**
     * Check if device supports fingerprint.
     */
    public function supportsFingerprint(): bool
    {
        return $this->hasCapability('fingerprint');
    }

    /**
     * Check if device supports face recognition.
     */
    public function supportsFace(): bool
    {
        return $this->hasCapability('face');
    }

    /**
     * Check if device supports RFID cards.
     */
    public function supportsCard(): bool
    {
        return $this->hasCapability('card');
    }

    // =========================================================================
    // Statistics
    // =========================================================================

    /**
     * Get today's attendance count.
     */
    public function getTodayAttendanceCount(): int
    {
        return $this->attendanceRecords()
            ->whereDate('punch_date', today())
            ->count();
    }

    /**
     * Get uptime percentage (last 30 days).
     */
    public function getUptimePercentage(): float
    {
        // This is a placeholder - implement actual uptime tracking if needed
        return $this->isOnline() ? 100.0 : 0.0;
    }

    // =========================================================================
    // Push Mode Methods
    // =========================================================================

    /**
     * Check if device uses push mode (ICLOCK/ADMS).
     */
    public function isPushMode(): bool
    {
        return $this->connection_mode === 'push';
    }

    /**
     * Check if device uses pull mode (server connects to device).
     */
    public function isPullMode(): bool
    {
        return $this->connection_mode === 'pull' || empty($this->connection_mode);
    }

    /**
     * Get the push URL that should be configured on the device.
     */
    public function getPushUrl(): string
    {
        $baseUrl = config('app.url', 'https://uhdor.com');
        return rtrim($baseUrl, '/') . '/api/iclock';
    }

    /**
     * Mark that push data was received.
     */
    public function markPushReceived(int $recordCount = 0): void
    {
        $this->update([
            'status' => 'online',
            'last_seen' => now(),
            'last_push_received' => now(),
            'last_sync' => now(),
            'push_records_today' => $this->push_records_today + $recordCount,
            'last_error' => null,
        ]);
    }
}
