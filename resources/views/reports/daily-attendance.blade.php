@extends('layouts.tenant')

@section('title', __('messages.daily_attendance') ?? 'الدوام اليومي')

@section('header')
    <h1>📅 {{ __('messages.daily_attendance') ?? 'الدوام اليومي' }}</h1>
    <p>{{ $date->translatedFormat('l، j F Y') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('attendance-report.index') }}" class="btn btn-secondary">
        📋 {{ __('messages.monthly_report') ?? 'التقرير الشهري' }}
    </a>
    <a href="{{ route('attendance-report.export', array_merge(request()->query(), ['format' => 'daily'])) }}" class="btn btn-secondary">
        ⬇️ {{ __('messages.export') }}
    </a>
@endsection

@section('content')
<!-- Date Navigation -->
<div class="date-navigation" style="margin-bottom: 24px;">
    <a href="{{ route('attendance-report.daily', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}" class="nav-btn">
        ← {{ __('messages.previous_day') ?? 'اليوم السابق' }}
    </a>
    
    <form method="GET" action="{{ route('attendance-report.daily') }}" class="date-picker-form">
        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control" onchange="this.form.submit()">
        
        @if(isset($filters['shift_id']) && $filters['shift_id'])
        <input type="hidden" name="shift_id" value="{{ $filters['shift_id'] }}">
        @endif
        @if(isset($filters['branch_id']) && $filters['branch_id'])
        <input type="hidden" name="branch_id" value="{{ $filters['branch_id'] }}">
        @endif
    </form>
    
    @if($date->isToday())
        <span class="nav-btn disabled">{{ __('messages.today') }} →</span>
    @else
        <a href="{{ route('attendance-report.daily', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}" class="nav-btn">
            {{ __('messages.next_day') ?? 'اليوم التالي' }} →
        </a>
    @endif
</div>

<!-- Filters -->
<div class="filter-card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('attendance-report.daily') }}" class="filter-form">
        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
        
        <div class="filter-row">
            @if($shifts->count() > 0)
            <div class="form-group">
                <label class="form-label">{{ __('messages.shift') }}</label>
                <select name="shift_id" class="form-control">
                    <option value="">{{ __('messages.all_shifts') ?? 'جميع الورديات' }}</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ $filters['shift_id'] == $shift->id ? 'selected' : '' }}>
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($branches->count() > 0)
            <div class="form-group">
                <label class="form-label">{{ __('messages.branch') }}</label>
                <select name="branch_id" class="form-control">
                    <option value="">{{ __('messages.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ $filters['branch_id'] == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
            </div>
        </div>
    </form>
</div>

<!-- Daily Summary -->
<div class="stats-grid stats-5" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--primary-light);">
            👥
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['total_employees'] }}</div>
            <div class="stat-label">{{ __('messages.total_employees') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 211, 153, 0.15); color: #34d399;">
            ✅
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['present'] }}</div>
            <div class="stat-label">{{ __('messages.present') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(248, 113, 113, 0.15); color: #f87171;">
            ❌
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['absent'] }}</div>
            <div class="stat-label">{{ __('messages.absent') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            ⏰
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['late'] }}</div>
            <div class="stat-label">{{ __('messages.late') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(96, 165, 250, 0.15); color: #60a5fa;">
            🏃
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['early_leave'] }}</div>
            <div class="stat-label">{{ __('messages.early_leave') ?? 'مغادرة مبكرة' }}</div>
        </div>
    </div>
</div>

<!-- Attendance Visualization -->
<div class="attendance-visual" style="margin-bottom: 24px;">
    <div class="visual-bar">
        @php
            $presentPercent = $stats['total_employees'] > 0 ? round($stats['present'] / $stats['total_employees'] * 100) : 0;
            $absentPercent = $stats['total_employees'] > 0 ? round($stats['absent'] / $stats['total_employees'] * 100) : 0;
        @endphp
        <div class="bar-segment present" style="width: {{ $presentPercent }}%;" title="{{ $stats['present'] }} {{ __('messages.present') }}">
            @if($presentPercent > 10){{ $presentPercent }}%@endif
        </div>
        <div class="bar-segment absent" style="width: {{ $absentPercent }}%;" title="{{ $stats['absent'] }} {{ __('messages.absent') }}">
            @if($absentPercent > 10){{ $absentPercent }}%@endif
        </div>
    </div>
    <div class="visual-legend">
        <span><span class="dot present"></span> {{ __('messages.present') }}</span>
        <span><span class="dot absent"></span> {{ __('messages.absent') }}</span>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header">
        <h3>{{ __('messages.attendance_details') ?? 'تفاصيل الحضور' }}</h3>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th>{{ __('messages.employee') }}</th>
                    <th>{{ __('messages.shift') }}</th>
                    <th class="text-center">{{ __('messages.expected_time') ?? 'الوقت المتوقع' }}</th>
                    <th class="text-center">{{ __('messages.check_in') }}</th>
                    <th class="text-center">{{ __('messages.check_out') }}</th>
                    <th class="text-center">{{ __('messages.work_hours') ?? 'ساعات العمل' }}</th>
                    <th class="text-center">{{ __('messages.late_duration') ?? 'مدة التأخير' }}</th>
                    <th class="text-center">{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendanceData as $row)
                    <tr class="status-row status-{{ $row['status_class'] }}">
                        <td>
                            <div class="employee-cell">
                                <div class="avatar-sm" style="background: {{ $row['status_color'] }}20; color: {{ $row['status_color'] }};">
                                    {{ mb_substr($row['employee']->name, 0, 1) }}
                                </div>
                                <div class="employee-info">
                                    <a href="{{ route('analytics.employee', $row['employee']) }}" class="employee-name">
                                        {{ $row['employee']->name }}
                                    </a>
                                    <span class="employee-id">{{ $row['employee']->employee_id ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($row['shift'])
                                <span class="shift-badge" style="background: {{ $row['shift']->color ?? '#6366f1' }}20; color: {{ $row['shift']->color ?? '#6366f1' }};">
                                    {{ $row['shift']->name }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['shift'])
                                <span class="expected-time">{{ substr($row['shift']->start_time, 0, 5) }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['check_in'])
                                <span class="time-badge {{ $row['is_late'] ? 'late' : 'on-time' }}">
                                    {{ $row['check_in'] }}
                                </span>
                            @else
                                <span class="no-checkin">{{ __('messages.not_checked_in') ?? 'لم يسجل' }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['check_out'])
                                <span class="time-badge {{ $row['is_early_leave'] ? 'early' : 'normal' }}">
                                    {{ $row['check_out'] }}
                                </span>
                            @else
                                @if($row['check_in'])
                                    <span class="working-now">{{ __('messages.still_working') ?? 'لا يزال يعمل' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['work_hours'])
                                <span class="work-hours">{{ $row['work_hours'] }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['late_minutes'] > 0)
                                <span class="late-duration {{ $row['late_minutes'] > 30 ? 'severe' : ($row['late_minutes'] > 15 ? 'moderate' : 'minor') }}">
                                    {{ $row['late_minutes'] }} {{ __('messages.min') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="status-badge status-{{ $row['status_class'] }}">
                                {{ $row['status_icon'] }} {{ $row['status_text'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-icon">📅</div>
                            <p>{{ __('messages.no_attendance_data') ?? 'لا توجد بيانات حضور لهذا اليوم' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Status Summary Cards -->
<div class="status-summary" style="margin-top: 24px;">
    <div class="summary-card late-summary">
        <h4>⏰ {{ __('messages.late_employees') ?? 'الموظفون المتأخرون' }}</h4>
        @php $lateEmployees = collect($attendanceData)->filter(fn($r) => $r['is_late']); @endphp
        @if($lateEmployees->count() > 0)
            <ul class="summary-list">
                @foreach($lateEmployees as $row)
                    <li>
                        <span class="name">{{ $row['employee']->name }}</span>
                        <span class="detail">{{ $row['late_minutes'] }} {{ __('messages.min') }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="no-issues">✅ {{ __('messages.no_late_today') ?? 'لا يوجد متأخرون اليوم' }}</p>
        @endif
    </div>

    <div class="summary-card absent-summary">
        <h4>❌ {{ __('messages.absent_employees') ?? 'الموظفون الغائبون' }}</h4>
        @php $absentEmployees = collect($attendanceData)->filter(fn($r) => $r['status_class'] === 'absent'); @endphp
        @if($absentEmployees->count() > 0)
            <ul class="summary-list">
                @foreach($absentEmployees as $row)
                    <li>
                        <span class="name">{{ $row['employee']->name }}</span>
                        <span class="detail">{{ $row['shift']->name ?? '-' }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="no-issues">✅ {{ __('messages.no_absent_today') ?? 'لا يوجد غائبون اليوم' }}</p>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .date-navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
    }
    
    .nav-btn {
        padding: 10px 20px;
        background: var(--bg-secondary);
        border-radius: 8px;
        color: var(--text-primary);
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .nav-btn:hover:not(.disabled) {
        background: var(--primary-light);
        color: white;
    }
    
    .nav-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .date-picker-form {
        display: flex;
        align-items: center;
    }
    
    .date-picker-form input[type="date"] {
        padding: 10px 16px;
        font-size: 16px;
        font-weight: 600;
        text-align: center;
    }
    
    .stats-5 {
        grid-template-columns: repeat(5, 1fr);
    }
    
    @media (max-width: 1024px) {
        .stats-5 {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 640px) {
        .stats-5 {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .attendance-visual {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 20px;
    }
    
    .visual-bar {
        display: flex;
        height: 32px;
        border-radius: 8px;
        overflow: hidden;
        background: var(--bg-tertiary);
    }
    
    .bar-segment {
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 12px;
        transition: width 0.3s;
    }
    
    .bar-segment.present {
        background: linear-gradient(135deg, #34d399, #10b981);
    }
    
    .bar-segment.absent {
        background: linear-gradient(135deg, #f87171, #ef4444);
    }
    
    .visual-legend {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-top: 12px;
        font-size: 13px;
    }
    
    .dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-left: 6px;
    }
    
    .dot.present { background: #34d399; }
    .dot.absent { background: #f87171; }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .report-table th {
        background: var(--bg-secondary);
        padding: 12px 16px;
        text-align: right;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    
    .report-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .report-table .text-center {
        text-align: center;
    }
    
    .status-row.status-absent {
        background: rgba(239, 68, 68, 0.05);
    }
    
    .status-row.status-late {
        background: rgba(245, 158, 11, 0.05);
    }
    
    .employee-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .avatar-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    
    .employee-info {
        display: flex;
        flex-direction: column;
    }
    
    .employee-name {
        font-weight: 600;
        color: var(--text-primary);
        text-decoration: none;
    }
    
    .employee-id {
        font-size: 12px;
        color: var(--text-secondary);
    }
    
    .shift-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .expected-time {
        font-weight: 500;
        color: var(--text-secondary);
    }
    
    .time-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 13px;
    }
    
    .time-badge.on-time {
        background: rgba(52, 211, 153, 0.15);
        color: #10b981;
    }
    
    .time-badge.late {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .time-badge.early {
        background: rgba(96, 165, 250, 0.15);
        color: #3b82f6;
    }
    
    .no-checkin {
        color: #ef4444;
        font-weight: 500;
    }
    
    .working-now {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: rgba(99, 102, 241, 0.15);
        color: var(--primary-light);
        border-radius: 6px;
        font-size: 12px;
    }
    
    .working-now::before {
        content: '';
        width: 8px;
        height: 8px;
        background: #22c55e;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    
    .work-hours {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .late-duration {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 12px;
    }
    
    .late-duration.minor {
        background: rgba(251, 191, 36, 0.15);
        color: #d97706;
    }
    
    .late-duration.moderate {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .late-duration.severe {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-badge.status-present {
        background: rgba(52, 211, 153, 0.15);
        color: #10b981;
    }
    
    .status-badge.status-absent {
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
    }
    
    .status-badge.status-late {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .status-badge.status-early-leave {
        background: rgba(96, 165, 250, 0.15);
        color: #3b82f6;
    }
    
    .text-muted {
        color: var(--text-secondary);
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
        color: var(--text-secondary);
    }
    
    .empty-icon {
        font-size: 48px;
        margin-bottom: 12px;
    }
    
    .status-summary {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .status-summary {
            grid-template-columns: 1fr;
        }
    }
    
    .summary-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 20px;
    }
    
    .summary-card h4 {
        margin: 0 0 16px;
        font-size: 16px;
    }
    
    .late-summary {
        border-right: 4px solid #f59e0b;
    }
    
    .absent-summary {
        border-right: 4px solid #ef4444;
    }
    
    .summary-list {
        list-style: none;
        padding: 0;
        margin: 0;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .summary-list li {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        border-radius: 6px;
        margin-bottom: 6px;
        background: var(--bg-tertiary);
    }
    
    .summary-list .name {
        font-weight: 500;
    }
    
    .summary-list .detail {
        color: var(--text-secondary);
        font-size: 13px;
    }
    
    .no-issues {
        padding: 16px;
        text-align: center;
        color: #10b981;
        background: rgba(52, 211, 153, 0.1);
        border-radius: 8px;
        margin: 0;
    }
</style>
@endpush
