@extends('layouts.tenant')

@section('title', __('messages.edit_device'))

@section('header')
    <h1>{{ __('messages.edit_device') }}</h1>
    <p>{{ $device->name }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('devices.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form method="POST" action="{{ route('devices.update', ['device' => $device->uuid]) }}">
        @csrf
        @method('PUT')

        <div class="form-grid-2">
            <div class="form-group">
                <label for="name" class="form-label">{{ __('messages.device_name') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $device->name) }}" class="form-control" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="location" class="form-label">{{ __('messages.location') }}</label>
                <input type="text" name="location" id="location" value="{{ old('location', $device->location) }}" class="form-control">
            </div>
        </div>

        @if(isset($branches) && $branches->count() > 0)
        <div class="form-group">
            <label class="form-label">{{ __('messages.branch') }}</label>
            <select name="branch_id" class="form-control">
                <option value="">{{ __('messages.select_branch') ?? (app()->getLocale() == 'ar' ? 'اختر الفرع' : 'Select Branch') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $device->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Connection Mode Toggle --}}
        <div class="form-group">
            <label class="form-label">{{ __('messages.connection_mode') }}</label>
            <div style="display: flex; gap: 12px; margin-top: 6px;">
                <label style="display: flex; align-items: center; gap: 8px; padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="mode-option" id="mode-pull-label">
                    <input type="radio" name="connection_mode" value="pull" {{ old('connection_mode', $device->connection_mode ?? 'pull') === 'pull' ? 'checked' : '' }} onchange="toggleConnectionMode()">
                    <div>
                        <strong>🔄 {{ __('messages.pull_mode') }}</strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">{{ __('messages.pull_mode_desc') }}</div>
                    </div>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: all 0.2s;" class="mode-option" id="mode-push-label">
                    <input type="radio" name="connection_mode" value="push" {{ old('connection_mode', $device->connection_mode ?? 'pull') === 'push' ? 'checked' : '' }} onchange="toggleConnectionMode()">
                    <div>
                        <strong>📡 {{ __('messages.push_mode') }}</strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">{{ __('messages.push_mode_desc') }}</div>
                    </div>
                </label>
            </div>
            @error('connection_mode') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        {{-- Pull Mode Fields --}}
        <div id="pull-fields">
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="ip_address" class="form-label">{{ __('messages.ip_address') }}</label>
                    <input type="text" name="ip_address" id="ip_address" value="{{ old('ip_address', $device->ip_address) }}" class="form-control">
                    @error('ip_address') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="port" class="form-label">{{ __('messages.port') }}</label>
                    <input type="number" name="port" id="port" value="{{ old('port', $device->port) }}" class="form-control">
                </div>
            </div>
        </div>

        {{-- Push Mode Fields --}}
        <div id="push-fields" style="display: none;">
            <div class="form-group">
                <label for="serial_number" class="form-label">{{ __('messages.serial_number') }} <span style="color: #e53e3e;">*</span></label>
                <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $device->serial_number) }}" class="form-control" placeholder="e.g. BFKJ203560012">
                <p class="form-hint">{{ __('messages.serial_number_hint') }}</p>
                @error('serial_number') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            {{-- Push Setup Instructions --}}
            <div class="card" style="background: linear-gradient(135deg, #ebf8ff 0%, #f0fff4 100%); border: 1px solid #90cdf4; margin-top: 12px;">
                <div class="card-header" style="border-bottom: 1px solid #bee3f8;">
                    <h3 style="margin: 0; font-size: 15px;">📋 {{ __('messages.push_setup_instructions') }}</h3>
                </div>
                <div class="card-body" style="font-size: 14px; line-height: 1.8;">
                    <p style="margin-bottom: 10px; font-weight: 600;">{{ __('messages.push_setup_intro') }}</p>
                    <p style="margin-bottom: 8px;">{{ app()->getLocale() == 'ar' ? 'على جهاز ZKTeco، اذهب إلى:' : 'On the ZKTeco device, go to:' }} <strong>Menu → Comm. → Cloud Server Setting</strong></p>
                    <div style="background: #2d3748; border-radius: 8px; padding: 16px; color: #e2e8f0; font-family: monospace; font-size: 13px; direction: ltr; text-align: left; margin-top: 8px;">
                        <div style="color: #68d391; margin-bottom: 8px;">// {{ app()->getLocale() == 'ar' ? 'إعدادات خادم السحابة على الجهاز' : 'Cloud Server Settings on Device' }}</div>
                        <div style="margin-bottom: 4px;"><span style="color: #90cdf4;">Server Mode      :</span> <span style="color: #fbd38d; font-weight: bold;">ADMS</span></div>
                        <div style="margin-bottom: 4px;"><span style="color: #90cdf4;">Enable Domain Name:</span> <span style="color: #68d391; font-weight: bold;">ON</span></div>
                        <div style="margin-bottom: 4px;"><span style="color: #90cdf4;">Server Address   :</span> <span style="color: #68d391; font-weight: bold;">{{ request()->getHost() }}</span></div>
                        <div style="margin-bottom: 4px;"><span style="color: #90cdf4;">Server Port      :</span> <span style="color: #68d391; font-weight: bold;">80</span></div>
                        <div style="margin-bottom: 4px;"><span style="color: #90cdf4;">Enable Proxy     :</span> <span style="color: #fc8181;">OFF</span></div>
                        <div><span style="color: #90cdf4;">HTTPS            :</span> <span style="color: #fc8181;">OFF</span></div>
                    </div>
                    <p style="margin-top: 10px; font-size: 13px; color: var(--text-secondary);">
                        {{ app()->getLocale() == 'ar' ? 'الجهاز سيرسل بيانات الحضور تلقائياً إلى الخادم عبر بروتوكول ADMS/ICLOCK.' : 'The device will automatically push attendance data to the server via ADMS/ICLOCK protocol.' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="form-actions" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">{{ __('messages.update_device') }}</button>
            <a href="{{ route('devices.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>

<script>
function toggleConnectionMode() {
    const mode = document.querySelector('input[name="connection_mode"]:checked').value;
    const pullFields = document.getElementById('pull-fields');
    const pushFields = document.getElementById('push-fields');
    const ipInput = document.getElementById('ip_address');
    const portInput = document.getElementById('port');
    const serialInput = document.getElementById('serial_number');

    if (mode === 'push') {
        pullFields.style.display = 'none';
        pushFields.style.display = 'block';
        if (ipInput) ipInput.removeAttribute('required');
        if (portInput) portInput.removeAttribute('required');
        if (serialInput) serialInput.setAttribute('required', 'required');
    } else {
        pullFields.style.display = 'block';
        pushFields.style.display = 'none';
        if (ipInput) ipInput.setAttribute('required', 'required');
        if (portInput) portInput.setAttribute('required', 'required');
        if (serialInput) serialInput.removeAttribute('required');
    }

    document.querySelectorAll('.mode-option').forEach(el => {
        el.style.borderColor = 'var(--border-color)';
        el.style.background = 'transparent';
    });
    const activeLabel = mode === 'push' ? document.getElementById('mode-push-label') : document.getElementById('mode-pull-label');
    activeLabel.style.borderColor = 'var(--primary)';
    activeLabel.style.background = 'rgba(var(--primary-rgb, 59, 130, 246), 0.05)';
}

document.addEventListener('DOMContentLoaded', toggleConnectionMode);
</script>
@endsection
