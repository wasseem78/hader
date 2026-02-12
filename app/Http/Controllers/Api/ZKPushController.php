<?php

// =============================================================================
// ZKTeco Push Controller - ICLOCK / ADMS Protocol Receiver
// Handles real-time attendance data pushed from ZKTeco devices
//
// ZKTeco devices in "push mode" send HTTP requests to the server:
//   1. GET  /iclock/cdata?SN=xxx        → Handshake (device checks in)
//   2. POST /iclock/cdata?SN=xxx&table= → Push attendance/operation records
//   3. GET  /iclock/getrequest?SN=xxx   → Device polls for commands
//   4. POST /iclock/devicecmd?SN=xxx    → Device reports command result
//
// This is an alternative to "pull mode" where the server connects
// to the device via TCP/UDP to fetch logs.
//
// Production features:
//   - Central push_device_registry for O(1) SN→tenant lookup
//   - Multi-format ICLOCK line parser (tab/comma, multiple field orders)
//   - Deduplication of attendance records
//   - Device command queue (getrequest returns pending commands)
//   - Rate limiting via middleware
//   - Comprehensive logging with structured context
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ZKPushController extends Controller
{
    /**
     * Handle ICLOCK handshake (GET /iclock/cdata).
     *
     * The device sends this on boot and periodically.
     * Query params: SN (serial), options, pushver, language, pushflag
     *
     * We respond with device configuration commands.
     */
    public function handshake(Request $request)
    {
        $serialNumber = $this->extractSN($request);

        if (empty($serialNumber)) {
            Log::warning('ZK Push: Handshake with empty SN', [
                'ip' => $request->ip(),
                'query' => $request->query(),
            ]);
            return $this->plain('ERROR: No SN', 400);
        }

        Log::info('ZK Push: Handshake', [
            'sn' => $serialNumber,
            'ip' => $request->ip(),
            'pushver' => $request->query('pushver'),
            'language' => $request->query('language'),
        ]);

        $device = $this->findDeviceBySerial($serialNumber);

        if (!$device) {
            Log::warning('ZK Push: Unknown device SN', [
                'sn' => $serialNumber,
                'ip' => $request->ip(),
            ]);
            return $this->plain('ERROR: Unknown SN', 404);
        }

        // Update device status and IP
        $this->updateDeviceOnTenant($device, [
            'status' => 'online',
            'last_seen' => now(),
            'last_push_received' => now(),
            'last_error' => null,
        ]);

        // Update registry with last connection info
        $this->updateRegistryHeartbeat($serialNumber, $request->ip());

        $response = $this->buildHandshakeResponse($device);

        return $this->plain($response);
    }

    /**
     * Receive attendance records pushed from device (POST /iclock/cdata).
     *
     * The device POSTs data with table= query param:
     *   - table=ATTLOG → attendance records (tab-separated lines)
     *   - table=OPERLOG → operation logs (user/device changes)
     *   - table=ATTPHOTO → attendance photos (binary)
     *
     * Each ATTLOG line: "user_id\tpunch_time\tstate\tverify_mode\twork_code"
     */
    public function receiveRecords(Request $request)
    {
        $serialNumber = $this->extractSN($request);
        $table = strtoupper($request->query('table', ''));

        if (empty($serialNumber)) {
            return $this->plain('ERROR: No SN', 400);
        }

        $device = $this->findDeviceBySerial($serialNumber);
        if (!$device) {
            Log::warning('ZK Push: Data from unknown device', [
                'sn' => $serialNumber,
                'ip' => $request->ip(),
            ]);
            return $this->plain('ERROR: Unknown SN', 404);
        }

        $body = $request->getContent();
        $bodyLen = strlen($body);

        Log::info('ZK Push: Received data', [
            'sn' => $serialNumber,
            'table' => $table,
            'bytes' => $bodyLen,
            'preview' => substr($body, 0, 300),
        ]);

        // Update heartbeat
        $this->updateDeviceOnTenant($device, [
            'status' => 'online',
            'last_seen' => now(),
            'last_push_received' => now(),
        ]);

        // Track the stamp sent by the device (used for sync progress)
        $deviceStamp = $request->query('Stamp', '');

        // Route by table type
        if ($table === 'OPERLOG') {
            $this->processOperLog($body, $device);
            // Update OPERLOG stamp so device won't re-send these records
            if (!empty($deviceStamp)) {
                Cache::put("zk_stamp_op:{$serialNumber}", $deviceStamp, now()->addDays(30));
            }
            return $this->plain('OK');
        }

        if ($table === 'OPTIONS') {
            $this->processOptions($body, $device);
            return $this->plain('OK');
        }

        if ($table === 'ATTPHOTO') {
            // Photo data — acknowledge but skip processing
            Log::debug('ZK Push: Photo data received', [
                'sn' => $serialNumber,
                'bytes' => $bodyLen,
            ]);
            return $this->plain('OK');
        }

        // Default: ATTLOG — attendance records
        if (empty(trim($body))) {
            return $this->plain('OK');
        }

        $stats = $this->parseAndImportRecords($body, $device);

        Log::info('ZK Push: Import complete', [
            'sn' => $serialNumber,
            'stats' => $stats,
        ]);

        // Update ATTLOG stamp so device won't re-send these records
        if (!empty($deviceStamp)) {
            Cache::put("zk_stamp_att:{$serialNumber}", $deviceStamp, now()->addDays(30));
        }

        // Update device sync stats
        $updateData = [
            'last_sync' => now(),
        ];

        if ($stats['imported'] > 0) {
            $totalLogs = DB::connection($device->_tenant_connection)
                ->table('attendance_records')
                ->where('device_id', $device->id)
                ->count();
            $updateData['total_logs'] = $totalLogs;
            $updateData['push_records_today'] = DB::raw('push_records_today + ' . $stats['imported']);
        }

        $this->updateDeviceOnTenant($device, $updateData);

        return $this->plain('OK');
    }

    /**
     * Handle device command polling (GET /iclock/getrequest).
     *
     * The device periodically asks the server for pending commands.
     * If a command is queued, return it in ICLOCK format:
     *   C:{id}:{command_type} {parameters}
     *
     * Supported command types: REBOOT, INFO, CHECK, CLEAR LOG, etc.
     */
    public function getRequest(Request $request)
    {
        $serialNumber = $this->extractSN($request);

        if (empty($serialNumber)) {
            return $this->plain('OK');
        }

        $device = $this->findDeviceBySerial($serialNumber);
        if (!$device) {
            return $this->plain('OK');
        }

        // Update heartbeat
        $this->updateDeviceOnTenant($device, [
            'status' => 'online',
            'last_seen' => now(),
        ]);
        $this->updateRegistryHeartbeat($serialNumber, $request->ip());

        // Check for pending commands in cache
        $cacheKey = "zk_push_cmd:{$serialNumber}";
        $pendingCmd = Cache::pull($cacheKey);

        if ($pendingCmd) {
            Log::info('ZK Push: Sending command', [
                'sn' => $serialNumber,
                'command' => $pendingCmd,
            ]);
            return $this->plain($pendingCmd);
        }

        return $this->plain('OK');
    }

    /**
     * Handle device command result (POST /iclock/devicecmd).
     * Device reports back the result of a command we sent.
     */
    public function deviceCmd(Request $request)
    {
        $serialNumber = $this->extractSN($request);
        $body = $request->getContent();

        Log::info('ZK Push: Command result', [
            'sn' => $serialNumber,
            'body' => substr($body, 0, 500),
        ]);

        if (!empty($serialNumber)) {
            $device = $this->findDeviceBySerial($serialNumber);
            if ($device) {
                $this->updateDeviceOnTenant($device, [
                    'status' => 'online',
                    'last_seen' => now(),
                ]);
            }
        }

        return $this->plain('OK');
    }

    // =========================================================================
    // Public API: Queue Commands for Devices
    // =========================================================================

    /**
     * Queue a command for a push-mode device.
     * Called from DeviceController or admin actions.
     *
     * @param string $serialNumber Device serial number
     * @param string $command      ICLOCK command string
     * @param int    $ttl          Cache TTL in seconds (default 5 min)
     */
    public static function queueCommand(string $serialNumber, string $command, int $ttl = 300): void
    {
        Cache::put("zk_push_cmd:{$serialNumber}", $command, $ttl);
    }

    /**
     * Queue a REBOOT command for a push-mode device.
     */
    public static function queueReboot(string $serialNumber): void
    {
        $id = time();
        self::queueCommand($serialNumber, "C:{$id}:REBOOT");
    }

    /**
     * Queue an INFO request command.
     */
    public static function queueInfoRequest(string $serialNumber): void
    {
        $id = time();
        self::queueCommand($serialNumber, "C:{$id}:INFO");
    }

    // =========================================================================
    // Internal Methods
    // =========================================================================

    /**
     * Extract serial number from request (case-insensitive).
     */
    private function extractSN(Request $request): string
    {
        return trim(
            $request->query('SN',
                $request->query('sn',
                    $request->query('Sn', '')))
        );
    }

    /**
     * Return a plain text response (ICLOCK protocol).
     * Responses end with \r\n per ADMS spec.
     */
    private function plain(string $body, int $status = 200)
    {
        // ADMS protocol expects CRLF-terminated responses
        $body = rtrim($body) . "\r\n";

        return response($body, $status)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Connection', 'close')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'no-store, no-cache');
    }

    /**
     * Find a push-mode device by serial number across all tenant databases.
     *
     * Strategy:
     *   1. Check push_device_registry (central DB) — O(1) lookup
     *   2. Fallback: scan all tenant databases — O(N) but auto-registers
     *
     * Results are cached in-memory for the request lifecycle to avoid
     * repeated DB lookups when multiple endpoints are hit per request cycle.
     */
    private function findDeviceBySerial(string $serialNumber): ?object
    {
        // In-memory cache for this request
        static $cache = [];
        if (isset($cache[$serialNumber])) {
            return $cache[$serialNumber];
        }

        // 1. Fast lookup via central push_device_registry
        try {
            $registry = DB::connection('mysql')
                ->table('push_device_registry')
                ->where('serial_number', $serialNumber)
                ->where('is_active', true)
                ->first();
        } catch (\Exception $e) {
            Log::error('ZK Push: Registry lookup failed', ['error' => $e->getMessage()]);
            $registry = null;
        }

        if ($registry) {
            $tenant = DB::connection('mysql')
                ->table('tenants')
                ->find($registry->tenant_id);

            if ($tenant) {
                $connName = $this->configureTenantConnection($tenant);
                $device = DB::connection($connName)
                    ->table('devices')
                    ->where('id', $registry->device_id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($device) {
                    $device->_tenant_id = $tenant->id;
                    $device->_tenant_connection = $connName;
                    $device->_company_id = $device->company_id;
                    $cache[$serialNumber] = $device;
                    return $device;
                }
            }

            // Registry entry is stale — mark inactive
            DB::connection('mysql')
                ->table('push_device_registry')
                ->where('id', $registry->id)
                ->update(['is_active' => false, 'updated_at' => now()]);
        }

        // 2. Fallback: scan all active tenants
        $tenants = DB::connection('mysql')
            ->table('tenants')
            ->where('status', 'active')
            ->get();

        foreach ($tenants as $tenant) {
            try {
                $connName = $this->configureTenantConnection($tenant);
                $device = DB::connection($connName)
                    ->table('devices')
                    ->where('serial_number', $serialNumber)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($device) {
                    // Auto-register for future fast lookups
                    $this->registerPushDevice($serialNumber, $tenant->id, $device->id);

                    $device->_tenant_id = $tenant->id;
                    $device->_tenant_connection = $connName;
                    $device->_company_id = $device->company_id;
                    $cache[$serialNumber] = $device;
                    return $device;
                }
            } catch (\Exception $e) {
                // Skip tenants with DB issues
                continue;
            }
        }

        $cache[$serialNumber] = null;
        return null;
    }

    /**
     * Configure a temporary DB connection for a tenant.
     */
    private function configureTenantConnection(object $tenant): string
    {
        $connName = 'push_tenant_' . $tenant->id;

        if (!array_key_exists($connName, config('database.connections', []))) {
            $dbUser = \Illuminate\Support\Facades\Crypt::decryptString($tenant->db_username_enc);
            $dbPass = \Illuminate\Support\Facades\Crypt::decryptString($tenant->db_password_enc);

            config(["database.connections.{$connName}" => [
                'driver' => 'mysql',
                'host' => $tenant->db_host,
                'port' => $tenant->db_port,
                'database' => $tenant->db_name,
                'username' => $dbUser,
                'password' => $dbPass,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
            ]]);
        }

        return $connName;
    }

    /**
     * Register a push device in the central registry for fast future lookups.
     */
    private function registerPushDevice(string $serialNumber, int $tenantId, int $deviceId): void
    {
        try {
            DB::connection('mysql')->table('push_device_registry')->updateOrInsert(
                ['serial_number' => $serialNumber],
                [
                    'tenant_id' => $tenantId,
                    'device_id' => $deviceId,
                    'is_active' => true,
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::warning('ZK Push: Registry insert failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update registry heartbeat (last seen, IP).
     */
    private function updateRegistryHeartbeat(string $serialNumber, string $ip): void
    {
        try {
            DB::connection('mysql')
                ->table('push_device_registry')
                ->where('serial_number', $serialNumber)
                ->update([
                    'last_ip' => $ip,
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (\Exception $e) {
            // Non-critical — don't fail
        }
    }

    /**
     * Update device fields on its tenant database.
     */
    private function updateDeviceOnTenant(object $device, array $data): void
    {
        try {
            DB::connection($device->_tenant_connection)
                ->table('devices')
                ->where('id', $device->id)
                ->update($data);
        } catch (\Exception $e) {
            Log::warning('ZK Push: Device update failed', [
                'device_id' => $device->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process device OPTIONS/INFO data.
     * Contains device metadata: model, MAC, user count, fingerprint count, etc.
     * Format: ~Key=Value,Key2=Value2,...
     */
    private function processOptions(string $body, object $device): void
    {
        $info = [];

        // Parse comma-separated key=value pairs (with optional ~ prefix on keys)
        $pairs = preg_split('/,/', $body);
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (str_contains($pair, '=')) {
                [$key, $value] = explode('=', $pair, 2);
                $key = ltrim(trim($key), '~');
                $info[$key] = trim($value);
            }
        }

        Log::info('ZK Push: Device OPTIONS', [
            'sn' => $device->serial_number ?? '',
            'device_name' => $info['DeviceName'] ?? null,
            'mac' => $info['MAC'] ?? null,
            'user_count' => $info['UserCount'] ?? null,
            'fp_count' => $info['FPCount'] ?? null,
            'face_count' => $info['FaceCount'] ?? null,
            'transaction_count' => $info['TransactionCount'] ?? null,
        ]);

        // Update device record with reported info
        $updateData = [];

        if (!empty($info['DeviceName'])) {
            $updateData['model'] = $info['DeviceName'];
        }
        if (isset($info['UserCount'])) {
            $updateData['total_users'] = (int) $info['UserCount'];
        }
        if (isset($info['FPCount'])) {
            $updateData['total_fingerprints'] = (int) $info['FPCount'];
        }
        if (isset($info['TransactionCount'])) {
            $updateData['total_logs'] = (int) $info['TransactionCount'];
        }

        // Store full device info in settings JSON
        if (!empty($info)) {
            $existingSettings = json_decode(
                DB::connection($device->_tenant_connection)
                    ->table('devices')
                    ->where('id', $device->id)
                    ->value('settings') ?? '{}',
                true
            ) ?: [];
            $existingSettings['device_info'] = $info;
            $updateData['settings'] = json_encode($existingSettings);
        }

        if (!empty($updateData)) {
            $this->updateDeviceOnTenant($device, $updateData);
        }
    }

    /**
     * Process operation log data (OPERLOG).
     * Contains user enrollment changes, device config changes, etc.
     */
    private function processOperLog(string $body, object $device): void

    {
        $lines = preg_split('/\r?\n/', trim($body));
        $connName = $device->_tenant_connection;
        $userChanges = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // OPERLOG format varies: "OPLOG {operation} ..." or key=value pairs
            // Common: USER PIN=xxx Name=xxx Pri=xxx Passwd=xxx Card=xxx Grp=xxx
            if (preg_match('/USER\s+PIN=(\d+)\s+Name=(.+?)(?:\s+Pri=|\s*$)/i', $line, $m)) {
                $deviceUserId = trim($m[1]);
                $userName = trim($m[2]);

                // Auto-map user if device_user_id matches
                $user = DB::connection($connName)
                    ->table('users')
                    ->where('company_id', $device->_company_id)
                    ->where('device_user_id', $deviceUserId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($user) {
                    $userChanges++;
                }

                Log::debug('ZK Push: OPERLOG user', [
                    'pin' => $deviceUserId,
                    'name' => $userName,
                    'matched' => $user ? true : false,
                ]);
            }
        }

        if ($userChanges > 0) {
            // Refresh user count
            $totalUsers = DB::connection($connName)
                ->table('users')
                ->where('company_id', $device->_company_id)
                ->whereNotNull('device_user_id')
                ->whereNull('deleted_at')
                ->count();

            $this->updateDeviceOnTenant($device, ['total_users' => $totalUsers]);
        }
    }

    /**
     * Parse raw ICLOCK attendance data and import records.
     *
     * ZKTeco ICLOCK format (tab-separated):
     *   PIN \t DateTime \t Status \t Verify \t WorkCode [\t Reserved1 \t Reserved2]
     *
     * Some firmware variants:
     *   PIN \t DateTime \t Verify \t InOutState \t WorkCode
     *   PIN,DateTime,Verify,InOutState,WorkCode (comma-separated)
     */
    private function parseAndImportRecords(string $body, object $device): array
    {
        $stats = [
            'total_lines' => 0,
            'parsed' => 0,
            'imported' => 0,
            'duplicates' => 0,
            'user_not_found' => 0,
            'errors' => 0,
        ];

        $lines = preg_split('/\r?\n/', trim($body));
        $stats['total_lines'] = count($lines);

        $connName = $device->_tenant_connection;

        // Pre-fetch device users for batch efficiency
        $deviceUsers = DB::connection($connName)
            ->table('users')
            ->where('company_id', $device->_company_id)
            ->whereNotNull('device_user_id')
            ->whereNull('deleted_at')
            ->pluck('id', 'device_user_id')
            ->toArray();

        // Batch insert buffer
        $insertBatch = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $record = $this->parseLine($line);
            if (!$record) {
                $stats['errors']++;
                continue;
            }

            $stats['parsed']++;

            // Find user by device_user_id (from pre-fetched map)
            $userId = $deviceUsers[$record['user_id']] ?? null;

            if (!$userId) {
                $stats['user_not_found']++;
                continue;
            }

            // Map ZKTeco state → attendance type
            $recordType = match ((int) ($record['state'] ?? 0)) {
                0 => 'in',
                1 => 'out',
                2 => 'break_start',
                3 => 'break_end',
                4 => 'overtime_start',
                5 => 'overtime_end',
                default => 'in',
            };

            // Map verification mode → type
            // ZKTeco ADMS verify codes (vary by model/firmware):
            //   0  = Password
            //   1  = Fingerprint
            //   2  = Card (proximity/NFC)
            //   3  = Card (ID/RFID) — F22/ID and similar models
            //   4  = Card (secondary slot) — F22/ID and similar models
            //   5  = Fingerprint + Password (multi-auth)
            //   6  = Fingerprint + Card (multi-auth)
            //   7  = Face
            //   8  = Multi-factor
            //   9  = Other biometric
            //  15  = Face (alternate code)
            $verifyType = match ((int) ($record['verify'] ?? 1)) {
                0, 5 => 'password',
                1, 9 => 'fingerprint',
                2, 3, 4 => 'card',
                6 => 'fingerprint',  // FP+Card → primary is fingerprint
                7, 8, 15 => 'face',
                default => 'other',
            };

            // Deduplication check
            $exists = DB::connection($connName)
                ->table('attendance_records')
                ->where('device_id', $device->id)
                ->where('user_id', $userId)
                ->where('punched_at', $record['timestamp'])
                ->exists();

            if ($exists) {
                $stats['duplicates']++;
                continue;
            }

            try {
                $punchedAt = Carbon::parse($record['timestamp']);

                $insertBatch[] = [
                    'uuid' => (string) Str::uuid(),
                    'company_id' => $device->_company_id,
                    'branch_id' => $device->branch_id,
                    'user_id' => $userId,
                    'device_id' => $device->id,
                    'punched_at' => $punchedAt,
                    'punch_date' => $punchedAt->toDateString(),
                    'punch_time' => $punchedAt->toTimeString(),
                    'type' => $recordType,
                    'verification_type' => $verifyType,
                    'status' => 'pending',
                    'raw_data' => json_encode($record),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $stats['imported']++;

                // Flush batch every 50 records
                if (count($insertBatch) >= 50) {
                    DB::connection($connName)->table('attendance_records')->insert($insertBatch);
                    $insertBatch = [];
                }

            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('ZK Push: Record parse error', [
                    'error' => $e->getMessage(),
                    'line' => $line,
                ]);
            }
        }

        // Flush remaining batch
        if (!empty($insertBatch)) {
            try {
                DB::connection($connName)->table('attendance_records')->insert($insertBatch);
            } catch (\Exception $e) {
                // If batch fails (e.g. duplicate), fall back to individual inserts
                foreach ($insertBatch as $row) {
                    try {
                        DB::connection($connName)->table('attendance_records')->insert($row);
                    } catch (\Exception $e2) {
                        $stats['imported']--;
                        $stats['errors']++;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Parse a single ICLOCK attendance line.
     *
     * Supports multiple ZKTeco firmware formats:
     * Format A (common): PIN\tDateTime\tStatus\tVerify\tWorkCode[\tReserved1\tReserved2]
     * Format B (some FW): PIN\tDateTime\tVerify\tStatus\tWorkCode
     * Format C (comma):   PIN,DateTime,Status,Verify,WorkCode
     */
    private function parseLine(string $line): ?array
    {
        // Remove BOM and clean
        $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
        $line = trim($line);

        // Skip headers and empty lines
        if (empty($line) || preg_match('/^(USER|PIN|No\.|#)/i', $line)) {
            return null;
        }

        // Try tab-separated first (standard ICLOCK)
        $parts = preg_split('/\t+/', $line);
        if (count($parts) < 2) {
            // Fallback: comma-separated
            $parts = explode(',', $line);
        }

        if (count($parts) < 2) {
            return null;
        }

        $parts = array_map('trim', $parts);

        $userId = $parts[0] ?? '';
        $timestamp = $parts[1] ?? '';

        if (empty($userId) || empty($timestamp)) {
            return null;
        }

        // Validate timestamp format
        try {
            Carbon::parse($timestamp);
        } catch (\Exception $e) {
            return null;
        }

        // Determine field order:
        // Most ICLOCK: PIN, DateTime, Status(0-5), Verify(0-15), WorkCode
        // Some FW:     PIN, DateTime, Verify(0-15), Status(0-5), WorkCode
        $field2 = (int) ($parts[2] ?? 0);
        $field3 = (int) ($parts[3] ?? 0);

        // Heuristic: Status is 0-5, Verify can be 0-15
        // If field2 > 5 it's likely Verify, not Status
        if ($field2 <= 5) {
            $state = $field2;
            $verify = $field3;
        } else {
            $state = $field3;
            $verify = $field2;
        }

        return [
            'user_id' => $userId,
            'timestamp' => $timestamp,
            'state' => $state,
            'verify' => $verify,
            'work_code' => (int) ($parts[4] ?? 0),
            'raw_line' => $line,
        ];
    }

    /**
     * Build handshake response for device configuration.
     *
     * These ICLOCK options control device behavior:
     *   Stamp        – Last received attendance log stamp (9999 = get all)
     *   OpStamp      – Last received operation log stamp
     *   ErrorDelay   – Seconds to wait before retrying on error
     *   Delay        – Normal heartbeat/poll interval in seconds
     *   TransInterval– Minutes between data transfer batches
     *   Realtime     – 1=push immediately on each punch, 0=batch only
     */
    private function buildHandshakeResponse(object $device): string
    {
        $sn = $device->serial_number ?? 'DEVICE';

        // Retrieve last-seen stamps from cache (persisted per device)
        // Stamps tell the device which records we already have, so it only sends new ones.
        // Using 0 = "send me everything"; the device increments stamps as it sends.
        $attStamp  = Cache::get("zk_stamp_att:{$sn}", 0);
        $opStamp   = Cache::get("zk_stamp_op:{$sn}", 0);

        $lines = [];
        $lines[] = 'GET OPTION FROM: ' . $sn;

        // Stamp / OpStamp: last received log stamp (0 = send all new)
        $lines[] = "Stamp={$attStamp}";
        $lines[] = "OpStamp={$opStamp}";
        $lines[] = 'PhotoStamp=9999';

        // Timing: ErrorDelay=30s on error, Delay=10s normal poll interval
        $lines[] = 'ErrorDelay=30';
        $lines[] = 'Delay=10';

        // TransTimes: schedule for bulk data transfers.
        // Use 00:00;23:59 to allow transfers at any time of day.
        $lines[] = 'TransTimes=00:00;23:59';
        $lines[] = 'TransInterval=1';

        // TransFlag: which data tables to transfer
        $lines[] = "TransFlag=TransData AttLog\tOpLog\tAttPhoto\tEnrollUser\tChgUser\tEnrollFP\tChgFP\tFACE";

        // Realtime=1: device pushes each punch immediately (don't wait for TransTimes)
        $lines[] = 'Realtime=1';
        $lines[] = 'Encrypt=0';
        $lines[] = 'ServerVer=2.4.1';
        $lines[] = 'PushProtVer=2.4.1';

        // Per-table stamps (more specific than Stamp/OpStamp on newer firmware)
        $lines[] = "ATTLOGStamp={$attStamp}";
        $lines[] = "OPERLOGStamp={$opStamp}";
        $lines[] = 'ATTPHOTOStamp=9999';

        // TimeZone: ensure device knows server timezone
        $lines[] = 'TimeZone=3';

        return implode("\r\n", $lines);
    }
}
