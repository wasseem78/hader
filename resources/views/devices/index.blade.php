@extends('layouts.tenant')

@section('title', __('messages.devices'))

@section('header')
    <h1>{{ __('messages.devices') }}</h1>
    <p>{{ __('messages.manage_zkteco_devices') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('devices.create') }}" class="btn btn-primary">
        <span>+</span> {{ __('messages.add_device') }}
    </a>
@endsection

@section('content')
<!-- Filters -->
@if(isset($branches) && $branches->count() > 0)
<div class="filter-card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('devices.index') }}" class="filter-row">
        <div class="form-group">
            <label for="branch_id" class="form-label">{{ __('messages.branch') }}</label>
            <select name="branch_id" id="branch_id" class="form-control">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
        @if(request('branch_id'))
            <a href="{{ route('devices.index') }}" class="btn btn-secondary">{{ __('messages.clear') }}</a>
        @endif
    </form>
</div>
@endif

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.name') }}</th>
                @if(isset($branches) && $branches->count() > 0)
                <th>{{ __('messages.branch') }}</th>
                @endif
                <th>{{ __('messages.connection_mode') }}</th>
                <th>{{ __('messages.ip_address') }} / {{ __('messages.serial_number') }}</th>
                <th>{{ __('messages.location') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.last_sync') }}</th>
                <th>{{ __('messages.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
                <tr>
                    <td><strong style="color: var(--text-primary);">{{ $device->name }}</strong></td>
                    @if(isset($branches) && $branches->count() > 0)
                    <td>{{ $device->branch->name ?? '-' }}</td>
                    @endif
                    <td>
                        @if(($device->connection_mode ?? 'pull') === 'push')
                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">📡 {{ __('messages.push') }}</span>
                        @else
                            <span class="badge" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">🔄 {{ __('messages.pull') }}</span>
                        @endif
                    </td>
                    <td>
                        @if(($device->connection_mode ?? 'pull') === 'push')
                            <code>{{ $device->serial_number ?? '-' }}</code>
                        @else
                            <code>{{ $device->ip_address }}</code>:{{ $device->port }}
                        @endif
                    </td>
                    <td>{{ $device->location ?? '-' }}</td>
                    <td>
                        @if(($device->connection_mode ?? 'pull') === 'push')
                            @if($device->last_push_received && $device->last_push_received->diffInMinutes(now()) < 10)
                                <span class="badge badge-success">{{ __('messages.online') }}</span>
                            @elseif($device->last_push_received)
                                <span class="badge badge-warning">{{ __('messages.idle') }}</span>
                            @else
                                <span class="badge badge-secondary">{{ __('messages.pending_setup') }}</span>
                            @endif
                        @else
                            @if($device->status === 'online')
                                <span class="badge badge-success">{{ __('messages.online') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('messages.offline') }}</span>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if(($device->connection_mode ?? 'pull') === 'push')
                            {{ $device->last_push_received ? $device->last_push_received->diffForHumans() : __('messages.never') }}
                        @else
                            {{ $device->last_sync ? $device->last_sync->diffForHumans() : __('messages.never') }}
                        @endif
                    </td>
                    <td>
                        <div class="action-btns">
                            @if(($device->connection_mode ?? 'pull') === 'pull')
                            <button onclick="quickAction('{{ route('devices.test', ['device' => $device->uuid]) }}', 'Test Connection')" class="btn btn-secondary btn-sm">
                                🔌 {{ __('messages.test') }}
                            </button>
                            <button onclick="quickAction('{{ route('devices.sync', ['device' => $device->uuid]) }}', 'Sync Logs')" class="btn btn-secondary btn-sm">
                                🔄 {{ __('messages.sync') }}
                            </button>
                            @else
                            <button onclick="quickAction('{{ route('devices.test', ['device' => $device->uuid]) }}', 'Push Status')" class="btn btn-secondary btn-sm">
                                📡 {{ __('messages.status') }}
                            </button>
                            @endif
                            <a href="{{ route('devices.show', ['device' => $device->uuid]) }}" class="btn btn-secondary btn-sm">
                                👁 {{ __('messages.view') }}
                            </a>
                            <a href="{{ route('devices.edit', ['device' => $device->uuid]) }}" class="btn btn-primary btn-sm">
                                ✏️ {{ __('messages.edit') }}
                            </a>
                            <form action="{{ route('devices.destroy', ['device' => $device->uuid]) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('messages.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    {{ __('messages.delete') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-state-icon">📱</div>
                            <p>{{ __('messages.no_devices_found') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
async function quickAction(url, label) {
    console.log(`[ZKTeco Debug] Starting ${label}...`);
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        console.log(`[ZKTeco Debug] ${label} Response:`, data);
        
        if (data.success || !data.error) {
            alert(`${label}: ${data.message || 'Success'}`);
        } else {
            alert(`${label} Failed: ${data.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error(`[ZKTeco Debug] ${label} Error:`, error);
        alert(`${label} Error: ${error.message}`);
    }
}
</script>
@endsection
