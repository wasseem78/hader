@extends('layouts.tenant')

@section('title', __('messages.dashboard'))

@section('header')
    <h1>{{ __('messages.dashboard') }}</h1>
    <p>{{ now()->translatedFormat('l، j F Y') }}</p>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">{{ __('messages.total_employees') }}</div>
                <div class="stat-value">{{ $stats['totalEmployees'] ?? 0 }}</div>
            </div>
            <div class="stat-icon primary">👥</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">{{ __('messages.online_devices') }}</div>
                <div class="stat-value">{{ $stats['onlineDevices'] ?? 0 }}<span>/{{ $stats['totalDevices'] ?? 0 }}</span></div>
            </div>
            <div class="stat-icon success">📱</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">{{ __('messages.present_today') }}</div>
                <div class="stat-value">{{ $stats['todayPresent'] ?? 0 }}</div>
            </div>
            <div class="stat-icon primary">✅</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div>
                <div class="stat-label">{{ __('messages.late_today') }}</div>
                <div class="stat-value">{{ $stats['todayLate'] ?? 0 }}</div>
            </div>
            <div class="stat-icon warning">⚠️</div>
        </div>
    </div>
</div>

<!-- Content Grid -->
<div class="content-grid">
    <!-- Timeline Card -->
    <div class="card">
        <div class="card-header">
            <h3>{{ __('messages.todays_timeline') }}</h3>
            <a href="/attendance" class="link">{{ __('messages.view_all') }} →</a>
        </div>
        <div class="card-body">
            @if(isset($timeline) && count($timeline) > 0)
                @foreach($timeline as $entry)
                    <div class="timeline-item">
                        <div class="timeline-avatar">
                            {{ substr($entry['user']['name'] ?? 'U', 0, 1) }}
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-name">{{ $entry['user']['name'] ?? 'Unknown' }}</div>
                            <div class="timeline-meta">{{ $entry['device'] ?? __('messages.manual') }}</div>
                        </div>
                        <div class="timeline-time">
                            <div class="timeline-time-value">{{ $entry['time'] }}</div>
                            <span class="badge {{ ($entry['type'] ?? 'in') == 'in' ? 'badge-success' : 'badge-danger' }}">
                                {{ strtoupper($entry['type'] ?? 'in') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>{{ __('messages.no_records_today') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="card" style="height: fit-content;">
        <div class="card-header">
            <h3>{{ __('messages.quick_actions') }}</h3>
        </div>
        <div class="card-body">
            <a href="/devices/create" class="quick-action">
                <div class="quick-action-icon">📱</div>
                <span>{{ __('messages.add_device') }}</span>
            </a>
            
            <a href="/employees/create" class="quick-action">
                <div class="quick-action-icon">👤</div>
                <span>{{ __('messages.add_employee') }}</span>
            </a>
            
            <a href="/reports" class="quick-action">
                <div class="quick-action-icon">📊</div>
                <span>{{ __('messages.generate_report') }}</span>
            </a>
            
            <a href="/admin/shifts" class="quick-action">
                <div class="quick-action-icon">📅</div>
                <span>{{ __('messages.manage_shifts') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.Echo) {
            const companyId = "{{ auth()->user()->company_id }}";
            
            window.Echo.private(`company.${companyId}`)
                .listen('.attendance.processed', (e) => {
                    console.log('Attendance Processed:', e);
                    location.reload();
                });
        }
    });
</script>
@endsection
