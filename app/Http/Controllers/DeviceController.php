<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Device;
use App\Services\ZKTeco\ZKTecoClient;
use App\Http\Controllers\Api\ZKPushController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = Device::where('company_id', auth()->user()->company_id)
            ->with('branch:id,name');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $devices = $query->get();
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);

        return view('devices.index', [
            'devices' => $devices,
            'branches' => $branches,
        ]);
    }

    public function create()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);
        return view('devices.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $connectionMode = $request->input('connection_mode', 'pull');

        $rules = [
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'connection_mode' => 'required|in:pull,push',
            'serial_number' => 'nullable|string|max:100',
        ];

        // Pull mode requires IP
        if ($connectionMode === 'pull') {
            $rules['ip_address'] = 'required|ip';
            $rules['port'] = 'required|integer|min:1|max:65535';
        } else {
            $rules['ip_address'] = 'nullable|ip';
            $rules['port'] = 'nullable|integer|min:1|max:65535';
            $rules['serial_number'] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);

        $device = Device::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'ip_address' => $validated['ip_address'] ?? '0.0.0.0',
            'port' => $validated['port'] ?? 4370,
        ]);

        // If push mode with serial number, register in central push_device_registry
        if ($connectionMode === 'push' && !empty($validated['serial_number'])) {
            $this->registerPushDevice($device, $validated['serial_number']);
        }

        return redirect()->route('devices.index')->with('success', __('messages.device_created'));
    }

    public function show(Device $device)
    {
        $device->load('branch');
        return view('devices.show', ['device' => $device]);
    }

    public function edit(Device $device)
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)->active()->get(['id', 'name']);
        return view('devices.edit', ['device' => $device, 'branches' => $branches]);
    }

    public function update(Request $request, Device $device)
    {
        $connectionMode = $request->input('connection_mode', 'pull');

        $rules = [
            'name' => 'required|string|max:100',
            'location' => 'nullable|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'connection_mode' => 'required|in:pull,push',
            'serial_number' => 'nullable|string|max:100',
        ];

        if ($connectionMode === 'pull') {
            $rules['ip_address'] = 'required|ip';
            $rules['port'] = 'required|integer|min:1|max:65535';
        } else {
            $rules['ip_address'] = 'nullable|ip';
            $rules['port'] = 'nullable|integer|min:1|max:65535';
            $rules['serial_number'] = 'required|string|max:100';
        }

        $validated = $request->validate($rules);
        $validated['port'] = $validated['port'] ?? 4370;
        $validated['ip_address'] = $validated['ip_address'] ?? '0.0.0.0';

        $oldMode = $device->connection_mode;
        $device->update($validated);

        // Update push device registry if switching modes or changing serial
        if ($connectionMode === 'push' && !empty($validated['serial_number'])) {
            $this->registerPushDevice($device, $validated['serial_number']);
        } elseif ($connectionMode === 'pull' && $oldMode === 'push') {
            // Switched from push to pull — deactivate registry
            \DB::connection('mysql')->table('push_device_registry')
                ->where('device_id', $device->id)
                ->update(['is_active' => false]);
        }

        return redirect()->route('devices.index')->with('success', __('messages.device_updated'));
    }

    public function destroy(Device $device)
    {
        // Remove from push registry if push device
        if ($device->isPushMode()) {
            \DB::connection('mysql')->table('push_device_registry')
                ->where('device_id', $device->id)
                ->delete();
        }
        $device->delete();
        return redirect()->route('devices.index')->with('success', __('messages.device_deleted'));
    }

    public function testConnection(Device $device, Request $request)
    {
        // Push mode devices don't support test connection from server
        if ($device->isPushMode()) {
            $isOnline = $device->last_push_received &&
                        $device->last_push_received->diffInMinutes(now()) < 10;
            $result = [
                'success' => true,
                'message' => $isOnline
                    ? __('messages.push_device_online', ['time' => $device->last_push_received->diffForHumans()])
                    : __('messages.push_device_waiting'),
                'mode' => 'push',
                'last_push' => $device->last_push_received?->toDateTimeString(),
                'records_today' => $device->push_records_today ?? 0,
            ];
            return $request->ajax() || $request->wantsJson()
                ? response()->json($result)
                : back()->with('info', $result['message']);
        }

        $client = new ZKTecoClient($device);
        $result = $client->testConnection();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function syncLogs(Device $device, Request $request)
    {
        // Get the latest attendance record timestamp for this device
        $lastRecord = \App\Models\AttendanceRecord::where('device_id', $device->id)
            ->orderBy('punched_at', 'desc')
            ->first();
        
        $since = $lastRecord ? $lastRecord->punched_at->toDateTimeString() : null;

        $client = new ZKTecoClient($device);
        $result = $client->fetchAttendanceLogs($since);

        $stats = [
            'total_received' => 0,
            'user_not_found' => 0,
            'duplicates' => 0,
            'imported' => 0,
            'debug_info' => []
        ];

        if ($result['success'] && !empty($result['data'])) {
            $stats['total_received'] = count($result['data']);
            
            foreach ($result['data'] as $index => $log) {
                // Find user by device_user_id
                $user = \App\Models\User::where('company_id', $device->company_id)
                    ->where('device_user_id', $log['user_id'])
                    ->first();

                if (!$user) {
                    $stats['user_not_found']++;
                    if ($index < 3) $stats['debug_info'][] = "User ID {$log['user_id']} not found in local database.";
                    continue;
                }

                // Map ZKTeco state to our type
                $typeMapping = [
                    0 => 'in',
                    1 => 'out',
                    2 => 'break_start',
                    3 => 'break_end',
                    4 => 'overtime_start',
                    5 => 'overtime_end',
                ];
                $recordType = $typeMapping[$log['state']] ?? 'in';

                // Map ZKTeco verification type
                $vTypeMapping = [
                    0 => 'password',
                    1 => 'fingerprint',
                    2 => 'card',
                    15 => 'face',
                ];
                $verifyType = $vTypeMapping[$log['type']] ?? 'fingerprint';

                // Check for duplicate record
                $exists = \App\Models\AttendanceRecord::where('device_id', $device->id)
                    ->where('user_id', $user->id)
                    ->where('punched_at', $log['timestamp'])
                    ->exists();

                if ($exists) {
                    $stats['duplicates']++;
                    continue;
                }

                try {
                    \App\Models\AttendanceRecord::create([
                        'company_id' => $device->company_id,
                        'branch_id' => $device->branch_id,
                        'user_id' => $user->id,
                        'device_id' => $device->id,
                        'punched_at' => $log['timestamp'],
                        'type' => $recordType,
                        'verification_type' => $verifyType,
                        'status' => 'pending',
                        'raw_data' => $log,
                    ]);
                    $stats['imported']++;
                } catch (\Exception $e) {
                    $stats['errors'] = ($stats['errors'] ?? 0) + 1;
                    $stats['debug_info'][] = "Error saving log for User {$user->id}: " . $e->getMessage();
                    Log::error("Attendance Import Error: " . $e->getMessage(), ['log' => $log]);
                }
            }
            
            // Mark sync complete and update totals
            $device->markSyncComplete($stats['imported']);
            
            // Update total logs count
            $currentTotal = \App\Models\AttendanceRecord::where('device_id', $device->id)->count();
            $device->update(['total_logs' => $currentTotal]);
            
            $result['message'] = "Sync completed: {$stats['imported']} imported, {$stats['duplicates']} duplicates, {$stats['user_not_found']} users skipped.";
            $result['stats'] = $stats;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message'] ?? __('messages.sync_started'));
    }

    public function getDeviceInfo(Device $device, Request $request)
    {
        $client = new ZKTecoClient($device);
        $result = $client->getInfo();

        if ($result['success'] && isset($result['data'])) {
            $device->update([
                'serial_number' => $result['data']['serial_number'] ?? $device->serial_number,
                'model' => $result['data']['platform'] ?? $device->model,
                'total_users' => $result['data']['user_count'] ?? $device->total_users,
                'total_logs' => $result['data']['log_count'] ?? $device->total_logs,
            ]);
        }

        return response()->json($result);
    }

    public function getDeviceUsers(Device $device, Request $request)
    {
        $client = new ZKTecoClient($device);
        $result = $client->fetchUsers();

        if ($result['success'] && !empty($result['data'])) {
            $enrolledCount = 0;
            foreach ($result['data'] as $userData) {
                // Update local user info if they exist
                $user = \App\Models\User::where('company_id', $device->company_id)
                    ->where('device_user_id', $userData['user_id'])
                    ->first();
                
                if ($user) {
                    $user->update([
                        'card_number' => $userData['card_number'] ?? $user->card_number,
                    ]);
                    $enrolledCount++;
                }
            }
            $device->update(['total_users' => count($result['data'])]);
        }

        return response()->json($result);
    }

    /**
     * Register device in central push_device_registry for fast SN→tenant lookup
     */
    private function registerPushDevice(Device $device, string $serialNumber): void
    {
        $tenantId = tenant('id') ?? null;
        if (!$tenantId) return;

        \DB::connection('mysql')->table('push_device_registry')->updateOrInsert(
            ['serial_number' => $serialNumber],
            [
                'tenant_id' => $tenantId,
                'device_id' => $device->id,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Send a command to a push-mode device.
     * The command will be queued and delivered on the device's next poll.
     */
    public function sendPushCommand(Device $device, Request $request)
    {
        if (!$device->isPushMode()) {
            return response()->json(['success' => false, 'message' => 'Device is not in push mode'], 400);
        }

        $validated = $request->validate([
            'command' => 'required|in:reboot,info,clear_log,check',
        ]);

        $sn = $device->serial_number;
        if (empty($sn)) {
            return response()->json(['success' => false, 'message' => 'Device has no serial number'], 400);
        }

        switch ($validated['command']) {
            case 'reboot':
                ZKPushController::queueReboot($sn);
                $message = __('messages.push_command_queued_reboot');
                break;
            case 'info':
                ZKPushController::queueInfoRequest($sn);
                $message = __('messages.push_command_queued_info');
                break;
            case 'clear_log':
                ZKPushController::queueCommand($sn, 'C:' . time() . ':CLEAR LOG');
                $message = __('messages.push_command_queued_clear');
                break;
            case 'check':
                ZKPushController::queueCommand($sn, 'C:' . time() . ':CHECK');
                $message = __('messages.push_command_queued_check');
                break;
            default:
                $message = __('messages.push_command_queued');
        }

        return response()->json([
            'success' => true,
            'message' => $message ?? 'Command queued',
        ]);
    }

    /**
     * Sync users from a push-mode device.
     * Sends a DATA QUERY USERINFO command, device replies with user data via OPERLOG.
     * Results are tracked in cache and available via getSyncUsersStatus.
     */
    public function syncDeviceUsers(Device $device, Request $request)
    {
        if (!$device->isPushMode()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.device_not_push_mode'),
            ], 400);
        }

        $sn = $device->serial_number;
        if (empty($sn)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.device_no_serial'),
            ], 400);
        }

        // Check if a sync is already pending
        $existing = ZKPushController::getUserSyncStatus($sn);
        if ($existing && $existing['status'] === 'pending') {
            return response()->json([
                'success' => false,
                'message' => __('messages.sync_users_already_pending'),
                'status' => $existing,
            ]);
        }

        // Queue the user sync command
        ZKPushController::queueUserSync($sn);

        return response()->json([
            'success' => true,
            'message' => __('messages.sync_users_command_sent'),
        ]);
    }

    /**
     * Get the status of a pending user sync operation.
     * Polled by the UI to check if the device has responded yet.
     */
    public function getSyncUsersStatus(Device $device, Request $request)
    {
        $sn = $device->serial_number;
        if (empty($sn)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.device_no_serial'),
            ]);
        }

        $status = ZKPushController::getUserSyncStatus($sn);

        if (!$status) {
            return response()->json([
                'success' => true,
                'status' => 'none',
                'message' => __('messages.no_sync_in_progress'),
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $status['status'],
            'data' => $status,
            'message' => $status['status'] === 'completed'
                ? __('messages.sync_users_completed', [
                    'created' => $status['stats']['created'] ?? 0,
                    'updated' => $status['stats']['updated'] ?? 0,
                    'skipped' => $status['stats']['skipped'] ?? 0,
                    'total' => $status['stats']['total'] ?? 0,
                  ])
                : __('messages.sync_users_waiting'),
        ]);
    }
}
