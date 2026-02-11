<?php

// =============================================================================
// Device API Controller - REST Endpoints for Device Management
// =============================================================================

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Device;
use App\Services\ZKTeco\ZKTecoClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * List all devices for a tenant.
     *
     * GET /api/tenants/{tenant}/devices
     */
    public function index(Company $tenant): JsonResponse
    {
        $this->authorize('viewAny', [Device::class, $tenant]);

        $devices = $tenant->devices()
            ->with('apiTokens:id,device_id,name,last_used_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $devices,
            'meta' => [
                'total' => $devices->count(),
                'online' => $devices->where('status', 'online')->count(),
                'max_allowed' => $tenant->max_devices,
            ],
        ]);
    }

    /**
     * Create a new device.
     *
     * POST /api/tenants/{tenant}/devices
     */
    public function store(Request $request, Company $tenant): JsonResponse
    {
        $this->authorize('create', [Device::class, $tenant]);

        // Check plan limits
        if (!$tenant->canAddDevice()) {
            return response()->json([
                'message' => __('Device limit reached. Please upgrade your plan.'),
                'error' => 'plan_limit_exceeded',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'port' => 'integer|min:1|max:65535',
            'protocol' => 'in:tcp,udp,http',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|timezone',
            'auth_key' => 'nullable|string|max:100',
            'comm_password' => 'nullable|string|max:100',
            'sync_interval' => 'integer|min:1|max:60',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check for duplicate IP:Port
        $exists = $tenant->devices()
            ->where('ip_address', $request->ip_address)
            ->where('port', $request->port ?? 4370)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => __('A device with this IP address and port already exists.'),
            ], 422);
        }

        $device = $tenant->devices()->create([
            'name' => $request->name,
            'ip_address' => $request->ip_address,
            'port' => $request->port ?? config('zkteco.default_port', 4370),
            'protocol' => $request->protocol ?? 'tcp',
            'model' => $request->model,
            'serial_number' => $request->serial_number,
            'location' => $request->location,
            'timezone' => $request->timezone ?? $tenant->timezone,
            'auth_key' => $request->auth_key,
            'comm_password' => $request->comm_password,
            'sync_interval' => $request->sync_interval ?? 5,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => __('Device created successfully.'),
            'data' => $device,
        ], 201);
    }

    /**
     * Show a specific device.
     *
     * GET /api/tenants/{tenant}/devices/{device}
     */
    public function show(Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('view', $device);

        $device->load(['attendanceRecords' => function ($query) {
            $query->whereDate('punch_date', today())->orderBy('punched_at', 'desc');
        }]);

        return response()->json([
            'data' => $device,
        ]);
    }

    /**
     * Update a device.
     *
     * PUT /api/tenants/{tenant}/devices/{device}
     */
    public function update(Request $request, Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'ip_address' => 'ip',
            'port' => 'integer|min:1|max:65535',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|timezone',
            'auth_key' => 'nullable|string|max:100',
            'comm_password' => 'nullable|string|max:100',
            'sync_interval' => 'integer|min:1|max:60',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $device->update($request->only([
            'name', 'ip_address', 'port', 'model', 'serial_number',
            'location', 'timezone', 'auth_key', 'comm_password',
            'sync_interval', 'is_active',
        ]));

        return response()->json([
            'message' => __('Device updated successfully.'),
            'data' => $device->fresh(),
        ]);
    }

    /**
     * Delete a device.
     *
     * DELETE /api/tenants/{tenant}/devices/{device}
     */
    public function destroy(Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('delete', $device);

        $device->delete();

        return response()->json([
            'message' => __('Device deleted successfully.'),
        ]);
    }

    /**
     * Test device connectivity.
     *
     * POST /api/tenants/{tenant}/devices/{device}/test
     */
    public function testConnection(Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $client = new ZKTecoClient($device);
        $result = $client->testConnection();

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Sync attendance logs from device.
     *
     * POST /api/tenants/{tenant}/devices/{device}/sync
     */
    public function syncLogs(Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $device->markSyncing();

        // Dispatch sync job (async)
        \App\Jobs\SyncDeviceLogs::dispatch($device);

        return response()->json([
            'message' => __('Sync started. Logs will be imported shortly.'),
            'data' => [
                'status' => 'syncing',
            ],
        ]);
    }

    /**
     * Get device logs (attendance records).
     *
     * GET /api/tenants/{tenant}/devices/{device}/logs
     */
    public function getLogs(Request $request, Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('view', $device);

        $query = $device->attendanceRecords()
            ->with('user:id,uuid,name,employee_id')
            ->orderBy('punched_at', 'desc');

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('punch_date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('punch_date', '<=', $request->to);
        }

        $logs = $query->paginate($request->per_page ?? 50);

        return response()->json($logs);
    }

    /**
     * Push users to device.
     *
     * POST /api/tenants/{tenant}/devices/{device}/push-users
     */
    public function pushUsers(Request $request, Company $tenant, Device $device): JsonResponse
    {
        $this->authorize('update', $device);

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,uuid',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Dispatch job to push users
        \App\Jobs\PushUsersToDevice::dispatch($device, $request->user_ids);

        return response()->json([
            'message' => __('Users are being pushed to the device.'),
        ]);
    }
}
