@extends('layouts.tenant')

@section('title', __('messages.attendance_report'))

@section('header')
    <h1>{{ __('messages.attendance_report') }}</h1>
    <p>{{ __('messages.comprehensive_attendance_analysis') ?? 'تحليل شامل لدوام الموظفين' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('attendance-report.export', request()->query()) }}" class="btn btn-secondary">
        ⬇️ {{ __('messages.export_csv') }}
    </a>
    <a href="{{ route('attendance-report.daily') }}" class="btn btn-primary">
        📅 {{ __('messages.daily_view') ?? 'العرض اليومي' }}
    </a>
@endsection

@section('content')
<!-- Filters Card -->
<div class="filter-card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('attendance-report.index') }}" class="filter-form">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">{{ __('messages.start_date') }}</label>
                <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] }}">
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.end_date') }}</label>
                <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] }}">
            </div>

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

            @if($departments->count() > 0)
            <div class="form-group">
                <label class="form-label">{{ __('messages.department') }}</label>
                <select name="department_id" class="form-control">
                    <option value="">{{ __('messages.all_departments') ?? 'جميع الأقسام' }}</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ ($filters['department_id'] ?? '') == $dept->id ? 'selected' : '' }}
                                style="color: {{ $dept->color }};">
                            {{ $dept->name }} ({{ $dept->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <div class="filter-actions">
            <label class="checkbox-inline">
                <input type="checkbox" name="late_only" value="1" {{ $filters['late_only'] ? 'checked' : '' }}>
                <span>{{ __('messages.late_employees_only') ?? 'المتأخرون فقط' }}</span>
            </label>
            
            <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
            <a href="{{ route('attendance-report.index') }}" class="btn btn-secondary">{{ __('messages.reset') ?? 'إعادة تعيين' }}</a>
        </div>
    </form>
</div>

<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--primary-light);">
            👥
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['total_employees'] }}</div>
            <div class="stat-label">{{ __('messages.total_employees') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 211, 153, 0.15); color: #34d399;">
            ✓
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['avg_attendance_rate'] }}%</div>
            <div class="stat-label">{{ __('messages.avg_attendance_rate') ?? 'متوسط نسبة الحضور' }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            ⏰
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['avg_punctuality_rate'] }}%</div>
            <div class="stat-label">{{ __('messages.avg_punctuality_rate') ?? 'متوسط نسبة الانضباط' }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(248, 113, 113, 0.15); color: #f87171;">
            ⚠️
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['total_late_incidents'] }}</div>
            <div class="stat-label">{{ __('messages.late_incidents') ?? 'حالات التأخير' }}</div>
        </div>
    </div>
</div>

<!-- Alert Cards -->
<div class="alert-cards" style="display: flex; gap: 16px; margin-bottom: 24px;">
    @if($summary['critical_count'] > 0)
    <div class="alert-card alert-critical">
        <span class="alert-icon">🚨</span>
        <div class="alert-content">
            <strong>{{ $summary['critical_count'] }}</strong> {{ __('messages.employees_critical') ?? 'موظف بحالة حرجة' }}
        </div>
    </div>
    @endif
    
    @if($summary['warning_count'] > 0)
    <div class="alert-card alert-warning">
        <span class="alert-icon">⚠️</span>
        <div class="alert-content">
            <strong>{{ $summary['warning_count'] }}</strong> {{ __('messages.employees_warning') ?? 'موظف يحتاج متابعة' }}
        </div>
    </div>
    @endif
    
    <div class="alert-card alert-info">
        <span class="alert-icon">📊</span>
        <div class="alert-content">
            <strong>{{ $summary['total_late_hours'] }}h</strong> {{ __('messages.total_late_hours') ?? 'إجمالي ساعات التأخير' }}
        </div>
    </div>
</div>

<!-- Main Report Table -->
<div class="card">
    <div class="card-header">
        <h3>📋 {{ __('messages.detailed_report') ?? 'التقرير التفصيلي' }}</h3>
        <span class="badge badge-secondary">{{ count($attendanceData) }} {{ __('messages.employees') }}</span>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th class="sticky-col">{{ __('messages.employee') }}</th>
                    <th>{{ __('messages.department') }}</th>
                    <th>{{ __('messages.shift') }}</th>
                    <th class="text-center">{{ __('messages.working_days') }}</th>
                    <th class="text-center">{{ __('messages.present') }}</th>
                    <th class="text-center">{{ __('messages.absent') }}</th>
                    <th class="text-center">{{ __('messages.late_days') }}</th>
                    <th class="text-center">{{ __('messages.total_late') ?? 'إجمالي التأخير' }}</th>
                    <th class="text-center">{{ __('messages.avg_late') }}</th>
                    <th class="text-center">{{ __('messages.attendance') }}</th>
                    <th class="text-center">{{ __('messages.punctuality') }}</th>
                    <th class="text-center">{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendanceData as $row)
                    <tr class="severity-{{ $row['severity']['level'] }}">
                        <td class="sticky-col">
                            <div class="employee-cell">
                                <div class="avatar-sm" style="background: {{ $row['severity']['color'] }}20; color: {{ $row['severity']['color'] }};">
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
                        <td>{{ $row['employee']->department ?? '-' }}</td>
                        <td>
                            <span class="shift-badge" style="background: {{ $row['shift']->color ?? '#6366f1' }}20; color: {{ $row['shift']->color ?? '#6366f1' }};">
                                {{ $row['shift']->name ?? '-' }}
                            </span>
                        </td>
                        <td class="text-center">{{ $row['working_days'] }}</td>
                        <td class="text-center">
                            <span class="text-success font-bold">{{ $row['present_days'] }}</span>
                        </td>
                        <td class="text-center">
                            @if($row['absent_days'] > 0)
                                <span class="badge badge-danger">{{ $row['absent_days'] }}</span>
                            @else
                                <span class="text-muted">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['total_late_days'] > 0)
                                <span class="badge badge-warning">{{ $row['total_late_days'] }}</span>
                            @else
                                <span class="text-success">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['total_late_minutes'] > 0)
                                <span class="late-time">{{ floor($row['total_late_minutes'] / 60) }}h {{ $row['total_late_minutes'] % 60 }}m</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($row['avg_late_minutes'] > 0)
                                <span class="avg-late {{ $row['avg_late_minutes'] > 30 ? 'high' : ($row['avg_late_minutes'] > 15 ? 'medium' : 'low') }}">
                                    {{ $row['avg_late_minutes'] }} {{ __('messages.min') }}
                                </span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="rate-bar">
                                <div class="rate-fill" style="width: {{ $row['attendance_rate'] }}%; background: {{ $row['attendance_rate'] >= 90 ? '#34d399' : ($row['attendance_rate'] >= 75 ? '#fbbf24' : '#f87171') }};"></div>
                            </div>
                            <span class="rate-text">{{ $row['attendance_rate'] }}%</span>
                        </td>
                        <td class="text-center">
                            <div class="rate-bar">
                                <div class="rate-fill" style="width: {{ $row['punctuality_rate'] }}%; background: {{ $row['punctuality_rate'] >= 85 ? '#34d399' : ($row['punctuality_rate'] >= 70 ? '#fbbf24' : '#f87171') }};"></div>
                            </div>
                            <span class="rate-text">{{ $row['punctuality_rate'] }}%</span>
                        </td>
                        <td class="text-center">
                            <span class="status-indicator status-{{ $row['severity']['level'] }}">
                                @if($row['severity']['level'] === 'critical')
                                    🚨 {{ __('messages.critical') ?? 'حرج' }}
                                @elseif($row['severity']['level'] === 'warning')
                                    ⚠️ {{ __('messages.warning') ?? 'تحذير' }}
                                @elseif($row['severity']['level'] === 'attention')
                                    👀 {{ __('messages.attention') ?? 'انتباه' }}
                                @else
                                    ✅ {{ __('messages.good') ?? 'جيد' }}
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="empty-state">
                            <div class="empty-icon">📊</div>
                            <p>{{ __('messages.no_data_found') ?? 'لا توجد بيانات' }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Legend -->
<div class="legend-card" style="margin-top: 24px;">
    <h4>{{ __('messages.legend') ?? 'دليل الألوان' }}</h4>
    <div class="legend-items">
        <div class="legend-item">
            <span class="legend-color" style="background: #dc2626;"></span>
            <span>{{ __('messages.critical') ?? 'حرج' }} - {{ __('messages.needs_immediate_attention') ?? 'يحتاج اهتمام فوري' }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #f59e0b;"></span>
            <span>{{ __('messages.warning') ?? 'تحذير' }} - {{ __('messages.needs_monitoring') ?? 'يحتاج متابعة' }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #fbbf24;"></span>
            <span>{{ __('messages.attention') ?? 'انتباه' }} - {{ __('messages.minor_issues') ?? 'مشاكل بسيطة' }}</span>
        </div>
        <div class="legend-item">
            <span class="legend-color" style="background: #34d399;"></span>
            <span>{{ __('messages.good') ?? 'جيد' }} - {{ __('messages.excellent_performance') ?? 'أداء ممتاز' }}</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .filter-form {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .filter-row .form-group {
        flex: 1;
        min-width: 150px;
    }
    
    .filter-actions {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }
    
    .checkbox-inline {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 16px;
        background: var(--bg-secondary);
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }
    
    .checkbox-inline:has(input:checked) {
        background: rgba(251, 191, 36, 0.15);
        border-color: #fbbf24;
    }
    
    .alert-cards {
        flex-wrap: wrap;
    }
    
    .alert-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        border-radius: 12px;
        flex: 1;
        min-width: 200px;
    }
    
    .alert-critical {
        background: rgba(220, 38, 38, 0.1);
        border: 1px solid rgba(220, 38, 38, 0.3);
        color: #dc2626;
    }
    
    .alert-warning {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
        color: #f59e0b;
    }
    
    .alert-info {
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.3);
        color: var(--primary-light);
    }
    
    .alert-icon {
        font-size: 1.5rem;
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
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
        white-space: nowrap;
    }
    
    .report-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }
    
    .report-table .text-center {
        text-align: center;
    }
    
    .sticky-col {
        position: sticky;
        right: 0;
        background: var(--bg-primary);
        z-index: 10;
        min-width: 200px;
    }
    
    .severity-critical {
        background: rgba(220, 38, 38, 0.05) !important;
    }
    
    .severity-warning {
        background: rgba(245, 158, 11, 0.05) !important;
    }
    
    .severity-attention {
        background: rgba(251, 191, 36, 0.03) !important;
    }
    
    .employee-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
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
    
    .employee-name:hover {
        color: var(--primary-light);
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
    
    .text-success { color: #34d399; }
    .text-muted { color: var(--text-secondary); }
    .font-bold { font-weight: 600; }
    
    .late-time {
        color: #f59e0b;
        font-weight: 500;
    }
    
    .avg-late {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .avg-late.high {
        background: rgba(220, 38, 38, 0.15);
        color: #dc2626;
    }
    
    .avg-late.medium {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .avg-late.low {
        background: rgba(251, 191, 36, 0.15);
        color: #d97706;
    }
    
    .rate-bar {
        width: 60px;
        height: 6px;
        background: var(--bg-secondary);
        border-radius: 3px;
        overflow: hidden;
        display: inline-block;
        margin-left: 8px;
    }
    
    .rate-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s;
    }
    
    .rate-text {
        font-size: 12px;
        font-weight: 600;
    }
    
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .status-critical {
        background: rgba(220, 38, 38, 0.15);
        color: #dc2626;
    }
    
    .status-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
    }
    
    .status-attention {
        background: rgba(251, 191, 36, 0.15);
        color: #d97706;
    }
    
    .status-good {
        background: rgba(52, 211, 153, 0.15);
        color: #34d399;
    }
    
    .legend-card {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 16px 20px;
    }
    
    .legend-card h4 {
        margin: 0 0 12px;
        font-size: 14px;
        color: var(--text-secondary);
    }
    
    .legend-items {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 3px;
    }
    
    @media (max-width: 768px) {
        .filter-row .form-group {
            flex: 1 1 100%;
        }
        
        .alert-card {
            flex: 1 1 100%;
        }
        
        .sticky-col {
            position: static;
        }
    }
</style>
@endpush
