@extends('layouts.tenant')

@section('title', __('messages.employee_analytics') . ' - ' . $employee->name)

@section('header')
    <h1>{{ __('messages.employee_analytics') ?? 'تحليل حضور الموظف' }}</h1>
    <p>{{ $employee->name }} - {{ $employee->employee_id ?? '' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('analytics.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back_to_analytics') ?? 'العودة للتحليلات' }}
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="filter-card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('analytics.employee', $employee) }}" class="filter-row">
        <div class="form-group">
            <label class="form-label">{{ __('messages.start_date') }}</label>
            <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.end_date') }}</label>
            <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
        </div>

        <button type="submit" class="btn btn-primary">{{ __('messages.analyze') ?? 'تحليل' }}</button>
    </form>
</div>

<!-- Employee Info Card -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="avatar-lg">{{ substr($employee->name, 0, 1) }}</div>
            <div>
                <h2 style="margin: 0; color: var(--text-primary);">{{ $employee->name }}</h2>
                <p style="margin: 4px 0; color: var(--text-secondary);">
                    {{ $employee->position ?? __('messages.employee') }}
                    @if($employee->departmentRelation) - {{ $employee->departmentRelation->name }} @endif
                </p>
                @if($employee->branch)
                    <span class="badge badge-secondary">{{ $employee->branch->name }}</span>
                @endif
                @if($employee->shifts->count() > 0)
                    @foreach($employee->shifts as $shift)
                        <span class="badge badge-primary">{{ $shift->name }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 211, 153, 0.15); color: #34d399;">
            📈
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['attendance_rate'] }}%</div>
            <div class="stat-label">{{ __('messages.attendance_rate') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--primary-light);">
            ⏱️
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['punctuality_rate'] }}%</div>
            <div class="stat-label">{{ __('messages.punctuality_rate') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            ⏰
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['avg_late_minutes'] }} {{ __('messages.min') }}</div>
            <div class="stat-label">{{ __('messages.avg_late_time') ?? 'متوسط التأخير' }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(139, 92, 246, 0.15); color: #8b5cf6;">
            🕐
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['avg_work_hours'] }}h</div>
            <div class="stat-label">{{ __('messages.avg_work_hours') ?? 'متوسط ساعات العمل' }}</div>
        </div>
    </div>
</div>

<!-- Detailed Stats -->
<div class="stats-grid stats-grid-6" style="margin-bottom: 24px;">
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ $analysis['expected_days'] }}</div>
            <div class="stat-label">{{ __('messages.expected_days') ?? 'أيام متوقعة' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value text-success">{{ $analysis['present_days'] }}</div>
            <div class="stat-label">{{ __('messages.present_days') ?? 'أيام الحضور' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value text-danger">{{ $analysis['absent_days'] }}</div>
            <div class="stat-label">{{ __('messages.absent_days') ?? 'أيام الغياب' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value text-warning">{{ $analysis['late_days'] }}</div>
            <div class="stat-label">{{ __('messages.late_days') }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ $analysis['on_time_days'] }}</div>
            <div class="stat-label">{{ __('messages.on_time_days') ?? 'أيام منضبطة' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ number_format($analysis['total_work_hours'], 1) }}h</div>
            <div class="stat-label">{{ __('messages.total_hours') ?? 'إجمالي الساعات' }}</div>
        </div>
    </div>
</div>

<!-- Daily Records Calendar View -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3>📅 {{ __('messages.daily_details') ?? 'التفاصيل اليومية' }}</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>{{ __('messages.day') }}</th>
                        <th>{{ __('messages.shift') }}</th>
                        <th>{{ __('messages.check_in') }}</th>
                        <th>{{ __('messages.check_out') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.late') }}</th>
                        <th>{{ __('messages.work_hours') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($analysis['daily_records'] as $date => $record)
                        <tr class="{{ $record['status'] === 'absent' ? 'row-absent' : ($record['is_late'] ? 'row-late' : '') }}">
                            <td>{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}</td>
                            <td>{{ __('messages.' . strtolower(\Carbon\Carbon::parse($date)->format('l'))) }}</td>
                            <td>
                                <small>{{ $record['shift_name'] }}</small>
                                <br>
                                <code style="font-size: 11px;">{{ \Carbon\Carbon::parse($record['shift_start'])->format('H:i') }} - {{ \Carbon\Carbon::parse($record['shift_end'])->format('H:i') }}</code>
                            </td>
                            <td>
                                @if($record['check_in'])
                                    <strong>{{ \Carbon\Carbon::parse($record['check_in'])->format('H:i') }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record['check_out'])
                                    <strong>{{ \Carbon\Carbon::parse($record['check_out'])->format('H:i') }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record['status'] === 'present')
                                    @if($record['is_late'])
                                        <span class="badge badge-warning">{{ __('messages.late') }}</span>
                                    @else
                                        <span class="badge badge-success">{{ __('messages.on_time') }}</span>
                                    @endif
                                @else
                                    <span class="badge badge-danger">{{ __('messages.absent') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($record['is_late'])
                                    <span class="text-warning">+{{ $record['late_minutes'] }} {{ __('messages.min') }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($record['work_hours'] > 0)
                                    {{ $record['work_hours'] }}h
                                    @if($record['overtime_minutes'] > 0)
                                        <span class="badge badge-primary" style="font-size: 10px;">+{{ round($record['overtime_minutes']/60, 1) }}h OT</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                <p>{{ __('messages.no_records_found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar-lg {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .stats-grid-6 {
        grid-template-columns: repeat(6, 1fr) !important;
    }
    
    .stat-card-sm {
        padding: 16px !important;
    }
    
    .stat-card-sm .stat-value {
        font-size: 1.5rem !important;
    }
    
    .text-success { color: #34d399 !important; }
    .text-danger { color: #f87171 !important; }
    .text-warning { color: #fbbf24 !important; }
    .text-muted { color: var(--text-secondary); }
    
    .row-absent {
        background: rgba(248, 113, 113, 0.05);
    }
    
    .row-late {
        background: rgba(251, 191, 36, 0.05);
    }
    
    @media (max-width: 1024px) {
        .stats-grid-6 {
            grid-template-columns: repeat(3, 1fr) !important;
        }
    }
    
    @media (max-width: 768px) {
        .stats-grid-6 {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>
@endpush
