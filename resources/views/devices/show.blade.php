@extends('layouts.tenant')

@section('title', __('messages.device_details'))

@section('header')
    <h1>{{ $device->name }}</h1>
    <p>{{ $device->location ?? __('messages.no_location') }}</p>
@endsection

@section('header-actions')
    @if($device->connection_mode !== 'push')
    <form action="{{ route('devices.test', ['device' => $device->uuid]) }}" method="POST" style="display: inline;">
        @csrf
        <button type="submit" class="btn btn-secondary">
            🔌 {{ __('messages.test_connection') }}
        </button>
    </form>
    @endif
    <a href="{{ route('devices.edit', ['device' => $device->uuid]) }}" class="btn btn-primary">
        {{ __('messages.edit') }}
    </a>
    <a href="{{ route('devices.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back') }}
    </a>
@endsection

@section('content')
<div class="content-grid">
    {{-- Connection Mode Badge --}}
    <div class="card" style="grid-column: span 2;">
        <div class="card-body" style="display: flex; align-items: center; gap: 16px; padding: 16px;">
            @if(($device->connection_mode ?? 'pull') === 'push')
                <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                    📡 {{ __('messages.push_mode') }}
                </span>
                <span style="color: var(--text-secondary); font-size: 14px;">
                    {{ __('messages.push_mode_desc') }}
                </span>
                @if($device->last_push_received)
                    @if($device->last_push_received->diffInMinutes(now()) < 10)
                        <span class="badge badge-success" style="margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: auto;">🟢 {{ __('messages.receiving_data') }}</span>
                    @else
                        <span class="badge badge-warning" style="margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: auto;">⚠️ {{ __('messages.last_push') }}: {{ $device->last_push_received->diffForHumans() }}</span>
                    @endif
                @else
                    <span class="badge badge-secondary" style="margin-{{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: auto;">⏳ {{ __('messages.waiting_first_push') }}</span>
                @endif
            @else
                <span style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 8px 16px; border-radius: 20px; font-weight: 600; font-size: 14px;">
                    🔄 {{ __('messages.pull_mode') }}
                </span>
                <span style="color: var(--text-secondary); font-size: 14px;">
                    {{ __('messages.pull_mode_desc') }}
                </span>
            @endif
        </div>
    </div>

    <!-- Network Configuration -->
    <div class="card">
        <div class="card-header">
            <h3>🌐 {{ __('messages.network_configuration') ?? 'إعدادات الشبكة' }}</h3>
        </div>
        <div class="card-body">
            <div class="form-grid-2">
                @if(($device->connection_mode ?? 'pull') === 'pull')
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.ip_address') }}</label>
                    <div style="color: var(--text-primary); font-size: 15px;">
                        <code>{{ $device->ip_address }}</code>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.port') }}</label>
                    <div style="color: var(--text-primary); font-size: 15px;">{{ $device->port }}</div>
                </div>
                @endif
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.serial_number') ?? 'الرقم التسلسلي' }}</label>
                    <div style="color: var(--text-primary); font-size: 15px;">{{ $device->serial_number ?? '-' }}</div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.protocol') ?? 'البروتوكول' }}</label>
                    <div style="color: var(--text-primary); font-size: 15px;">
                        {{ ($device->connection_mode ?? 'pull') === 'push' ? 'ICLOCK / ADMS (HTTP)' : strtoupper($device->protocol ?? 'TCP') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Stats -->
    <div class="card">
        <div class="card-header">
            <h3>📊 {{ __('messages.status_stats') ?? 'الحالة والإحصائيات' }}</h3>
        </div>
        <div class="card-body">
            <div class="form-grid-2">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.status') }}</label>
                    <div>
                        @if(($device->connection_mode ?? 'pull') === 'push')
                            @if($device->last_push_received && $device->last_push_received->diffInMinutes(now()) < 10)
                                <span class="badge badge-success">{{ __('messages.online') }}</span>
                            @elseif($device->last_push_received)
                                <span class="badge badge-warning">{{ __('messages.idle') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('messages.pending_setup') }}</span>
                            @endif
                        @else
                            @if($device->isOnline())
                                <span class="badge badge-success">{{ __('messages.online') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('messages.offline') }}</span>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ ($device->connection_mode ?? 'pull') === 'push' ? (__('messages.last_push_received') ?? 'آخر استقبال') : (__('messages.last_seen') ?? 'آخر اتصال') }}</label>
                    <div style="color: var(--text-primary); font-size: 15px;">
                        @if(($device->connection_mode ?? 'pull') === 'push')
                            {{ $device->last_push_received ? $device->last_push_received->diffForHumans() : __('messages.never') }}
                        @else
                            {{ $device->last_seen ? $device->last_seen->diffForHumans() : __('messages.never') }}
                        @endif
                    </div>
                </div>
                @if(($device->connection_mode ?? 'pull') === 'push')
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.push_records_today') }}</label>
                    <div style="color: var(--text-primary); font-size: 15px; font-weight: 600;">{{ $device->push_records_today ?? 0 }}</div>
                </div>
                @endif
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.total_logs') ?? 'إجمالي السجلات' }}</label>
                    <div style="color: var(--text-primary); font-size: 15px; font-weight: 600;">{{ $device->total_logs ?? 0 }}</div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">{{ __('messages.total_users') ?? 'إجمالي المستخدمين' }}</label>
                    <div style="color: var(--text-primary); font-size: 15px; font-weight: 600;">{{ $device->total_users ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(($device->connection_mode ?? 'pull') === 'push')
    {{-- Push Mode Setup Instructions --}}
    <div class="card" style="grid-column: span 2;">
        <div class="card-header" style="background: linear-gradient(135deg, #ebf8ff 0%, #f0fff4 100%);">
            <h3>📋 {{ __('messages.push_setup_instructions') }}</h3>
        </div>
        <div class="card-body" style="font-size: 14px; line-height: 1.8;">
            <p style="margin-bottom: 12px; font-weight: 600;">{{ app()->getLocale() == 'ar' ? 'إعدادات خادم السحابة على جهاز ZKTeco:' : 'Cloud Server Settings on ZKTeco Device:' }}</p>
            <div class="form-grid-2">
                <div>
                    <p style="margin-bottom: 8px;">{{ app()->getLocale() == 'ar' ? 'على الجهاز، اذهب إلى:' : 'On the device, go to:' }} <strong>Menu → Comm. → Cloud Server Setting</strong></p>
                    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                        <tr><td style="padding: 6px 12px; color: var(--text-secondary);">Server Mode</td><td style="padding: 6px 12px; font-weight: 600;">ADMS</td></tr>
                        <tr style="background: rgba(0,0,0,0.02);"><td style="padding: 6px 12px; color: var(--text-secondary);">Enable Domain Name</td><td style="padding: 6px 12px; font-weight: 600; color: var(--primary);">ON</td></tr>
                        <tr><td style="padding: 6px 12px; color: var(--text-secondary);">Server Address</td><td style="padding: 6px 12px; font-weight: 600; color: var(--primary);">{{ request()->getHost() }}</td></tr>
                        <tr style="background: rgba(0,0,0,0.02);"><td style="padding: 6px 12px; color: var(--text-secondary);">Server Port</td><td style="padding: 6px 12px; font-weight: 600; color: var(--primary);">80</td></tr>
                        <tr><td style="padding: 6px 12px; color: var(--text-secondary);">Enable Proxy Server</td><td style="padding: 6px 12px;">OFF</td></tr>
                        <tr style="background: rgba(0,0,0,0.02);"><td style="padding: 6px 12px; color: var(--text-secondary);">HTTPS</td><td style="padding: 6px 12px;">OFF</td></tr>
                    </table>
                </div>
                <div style="background: #2d3748; border-radius: 8px; padding: 16px; color: #e2e8f0; font-family: monospace; font-size: 13px; direction: ltr; text-align: left;">
                    <div style="color: #68d391; margin-bottom: 8px;">// ADMS Push Endpoint</div>
                    <div><span style="color: #90cdf4;">URL:</span> http://{{ request()->getHost() }}/iclock/cdata</div>
                    <div><span style="color: #90cdf4;">Serial Number:</span> {{ $device->serial_number ?? 'N/A' }}</div>
                    <div><span style="color: #90cdf4;">Protocol:</span> ICLOCK / ADMS (HTTP)</div>
                    <div style="color: #68d391; margin-top: 8px;">// {{ app()->getLocale() == 'ar' ? 'الجهاز يرسل البيانات تلقائياً' : 'Device pushes data automatically' }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(($device->connection_mode ?? 'pull') === 'push')
    {{-- Push Commands Section --}}
    <div class="card" style="grid-column: span 2;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>🎯 {{ __('messages.push_commands') }}</h3>
            <span style="color: var(--text-secondary); font-size: 13px;">{{ __('messages.push_commands_desc') }}</span>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <button onclick="sendPushCommand('reboot')" class="btn btn-warning btn-sm" style="display: flex; align-items: center; gap: 6px;">
                    🔄 {{ __('messages.reboot_device') }}
                </button>
                <button onclick="sendPushCommand('info')" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                    ℹ️ {{ __('messages.request_info') }}
                </button>
                <button onclick="sendPushCommand('clear_log')" class="btn btn-danger btn-sm" style="display: flex; align-items: center; gap: 6px;">
                    🗑 {{ __('messages.clear_device_log') }}
                </button>
                <button onclick="sendPushCommand('check')" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 6px;">
                    🔍 {{ __('messages.check_device') }}
                </button>
            </div>
            <div id="push-command-result" style="margin-top: 12px; display: none; padding: 10px; border-radius: 6px; font-size: 14px;"></div>
        </div>
    </div>
    @endif

    <!-- Debug Console -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3>🛠 {{ __('Communication Debug') ?? 'تنقيح الاتصال' }}</h3>
            <div class="debug-actions">
                @if(($device->connection_mode ?? 'pull') === 'pull')
                <button onclick="runDebugAction('test')" class="btn btn-secondary btn-sm">🔌 Test</button>
                <button onclick="runDebugAction('info')" class="btn btn-secondary btn-sm">ℹ️ Info</button>
                <button onclick="runDebugAction('users')" class="btn btn-secondary btn-sm">👥 Users</button>
                <button onclick="runDebugAction('logs')" class="btn btn-secondary btn-sm">📝 Logs</button>
                @else
                <button onclick="runDebugAction('test')" class="btn btn-secondary btn-sm">📡 Status</button>
                @endif
                <button onclick="window.location.reload()" class="btn btn-primary btn-sm">🔄 Refresh UI</button>
                <button onclick="clearDebugConsole()" class="btn btn-danger btn-sm">🗑 Clear</button>
            </div>
        </div>
        <div class="card-body">
            <div id="debug-console" style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: 'Consolas', 'Monaco', monospace; font-size: 13px; min-height: 200px; max-height: 400px; overflow-y: auto; line-height: 1.5;">
                <div style="color: #6a9955;">// Biometric Device Debug Console initialized.</div>
                <div style="color: #6a9955;">// Mode: {{ ($device->connection_mode ?? 'pull') === 'push' ? 'PUSH (ICLOCK/ADMS)' : 'PULL (TCP)' }}</div>
                @if(($device->connection_mode ?? 'pull') === 'push')
                <div style="color: #6a9955;">// Push devices send data automatically. Use "Status" to check connectivity.</div>
                @else
                <div style="color: #6a9955;">// Results will be displayed here and in the browser's console.log()</div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function addToConsole(message, type = 'info') {
    const consoleEl = document.getElementById('debug-console');
    const entry = document.createElement('div');
    entry.style.marginBottom = '5px';
    
    const timestamp = new Date().toLocaleTimeString();
    let color = '#d4d4d4';
    let prefix = '●';
    
    if (type === 'error') { color = '#f44336'; prefix = '✖'; }
    if (type === 'success') { color = '#4caf50'; prefix = '✔'; }
    if (type === 'debug') { color = '#2196f3'; prefix = 'ℹ'; }
    
    entry.innerHTML = `<span style="color: #888;">[${timestamp}]</span> <span style="color: ${color}">${prefix} ${message}</span>`;
    consoleEl.appendChild(entry);
    consoleEl.scrollTop = consoleEl.scrollHeight;
}

async function runDebugAction(action) {
    const deviceUuid = '{{ $device->uuid }}';
    let url = '';
    let method = 'POST';
    
    addToConsole(`Running debug action: ${action}...`, 'debug');
    
    switch(action) {
        case 'test':
            url = '{{ route("devices.test", ["device" => $device->uuid]) }}';
            break;
        case 'info':
            url = '{{ route("devices.info", ["device" => $device->uuid]) }}';
            method = 'GET';
            break;
        case 'users':
            url = '{{ route("devices.users", ["device" => $device->uuid]) }}';
            method = 'GET';
            break;
        case 'logs':
            url = '{{ route("devices.sync", ["device" => $device->uuid]) }}';
            break;
    }
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        console.log(`[ZKTeco Debug] ${action.toUpperCase()} Response:`, data);
        
        if (data.success || !data.error) {
            addToConsole(`${action.toUpperCase()} response received. Check browser console for full payload.`, 'success');
            if (data.message) addToConsole(`Message: ${data.message}`, 'success');
            if (data.mode === 'push') {
                addToConsole(`Last Push: ${data.last_push || 'Never'}`, 'info');
                addToConsole(`Records Today: ${data.records_today || 0}`, 'info');
            }
            if (data.data) {
                const dataStr = JSON.stringify(data.data, null, 2).substring(0, 100) + '...';
                addToConsole(`Data: ${dataStr}`, 'info');
            }
        } else {
            addToConsole(`${action.toUpperCase()} failed: ${data.message || 'Unknown error'}`, 'error');
        }
    } catch (error) {
        console.error(`[ZKTeco Debug] Error:`, error);
        addToConsole(`Error: ${error.message}`, 'error');
    }
}

function clearDebugConsole() {
    document.getElementById('debug-console').innerHTML = '<div style="color: #6a9955;">// Console cleared.</div>';
}

async function sendPushCommand(command) {
    const resultEl = document.getElementById('push-command-result');
    resultEl.style.display = 'block';
    resultEl.style.background = '#f0f4f8';
    resultEl.style.color = '#4a5568';
    resultEl.textContent = '{{ __("messages.send_command") }}...';

    addToConsole(`Sending push command: ${command}...`, 'debug');

    try {
        const response = await fetch('{{ route("devices.push-command", ["device" => $device->uuid]) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ command: command })
        });

        const data = await response.json();

        if (data.success) {
            resultEl.style.background = '#f0fff4';
            resultEl.style.color = '#276749';
            resultEl.textContent = '✔ ' + data.message;
            addToConsole(`Command '${command}' queued successfully.`, 'success');
        } else {
            resultEl.style.background = '#fff5f5';
            resultEl.style.color = '#c53030';
            resultEl.textContent = '✖ ' + (data.message || '{{ __("messages.command_failed") }}');
            addToConsole(`Command '${command}' failed: ${data.message}`, 'error');
        }
    } catch (error) {
        resultEl.style.background = '#fff5f5';
        resultEl.style.color = '#c53030';
        resultEl.textContent = '✖ ' + error.message;
        addToConsole(`Command error: ${error.message}`, 'error');
    }

    setTimeout(() => { resultEl.style.display = 'none'; }, 5000);
}
</script>
@endsection
