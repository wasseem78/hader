@extends('layouts.tenant')

@section('title', __('messages.timeline'))

@section('header')
    <h1>{{ __('messages.timeline') }}</h1>
    <p>{{ app()->getLocale() == 'ar' ? 'عرض سجل الحضور بشكل زمني' : 'View attendance records chronologically' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('attendance.index') }}" class="btn btn-secondary">
        ← {{ app()->getLocale() == 'ar' ? 'سجل الحضور' : 'Attendance List' }}
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}</h3>
        <form action="{{ route('attendance.timeline') }}" method="GET">
            <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="form-control" style="max-width: 180px;">
        </form>
    </div>
    <div class="card-body">
        @if($records->count() > 0)
            <div class="timeline-container">
                @foreach($records as $record)
                    <div class="timeline-row">
                        <div class="timeline-time">
                            {{ $record->punch_time->format('H:i') }}
                        </div>
                        <div class="timeline-marker {{ $record->type === 'in' ? 'marker-in' : 'marker-out' }}"></div>
                        <div class="timeline-content">
                            <div class="timeline-user">
                                <div class="avatar-sm">{{ substr($record->user->name ?? 'U', 0, 1) }}</div>
                                <div>
                                    <strong>{{ $record->user->name ?? 'Unknown' }}</strong>
                                    <span class="device-name">{{ $record->device->name ?? 'Manual' }}</span>
                                </div>
                            </div>
                            <div class="timeline-badge">
                                @if($record->type === 'in')
                                    <span class="badge badge-success">{{ app()->getLocale() == 'ar' ? 'دخول' : 'IN' }}</span>
                                @else
                                    <span class="badge badge-danger">{{ app()->getLocale() == 'ar' ? 'خروج' : 'OUT' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">📅</div>
                <p>{{ __('messages.no_records') }}</p>
            </div>
        @endif
    </div>
</div>

<style>
    .timeline-container {
        position: relative;
        padding: 20px 0;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 80px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--glass-border);
    }

    .timeline-row {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        position: relative;
    }

    .timeline-time {
        width: 60px;
        text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
        font-weight: 600;
        color: var(--text-secondary);
        font-size: 14px;
        font-family: 'Inter', monospace;
    }

    .timeline-marker {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin: 0 20px;
        z-index: 1;
        box-shadow: 0 0 0 4px var(--bg-card);
        flex-shrink: 0;
    }
    .marker-in { background: #10b981; box-shadow: 0 0 0 4px var(--bg-card), 0 0 10px rgba(16, 185, 129, 0.4); }
    .marker-out { background: #ef4444; box-shadow: 0 0 0 4px var(--bg-card), 0 0 10px rgba(239, 68, 68, 0.4); }

    .timeline-content {
        flex: 1;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s;
    }
    .timeline-content:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(99, 102, 241, 0.3);
    }

    .timeline-user {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .timeline-user strong {
        display: block;
        color: var(--text-primary);
        font-size: 14px;
    }
    .timeline-user .device-name {
        font-size: 12px;
        color: var(--text-muted);
    }
</style>
@endsection
