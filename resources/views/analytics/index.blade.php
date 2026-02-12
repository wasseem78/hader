@extends('layouts.tenant')

@section('title', __('messages.analytics'))

@section('header')
    <h1>{{ __('messages.analytics') }}</h1>
    <p>{{ __('messages.analytics_description') ?? 'تحليل بيانات الحضور والانصراف' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('analytics.export', request()->query()) }}" class="btn btn-secondary">
        ⬇️ {{ __('messages.export_report') }}
    </a>
@endsection

@section('content')
<!-- Filters -->
<div class="filter-card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('analytics.index') }}" class="filter-row">
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

<!-- Summary Stats Cards -->
<div class="stats-grid" style="margin-bottom: 24px;">
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
            ✓
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['attendance_rate'] }}%</div>
            <div class="stat-label">{{ __('messages.attendance_rate') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            ⏰
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['punctuality_rate'] }}%</div>
            <div class="stat-label">{{ __('messages.punctuality_rate') }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(248, 113, 113, 0.15); color: #f87171;">
            ⚠️
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['late_count'] }}</div>
            <div class="stat-label">{{ __('messages.late_arrivals') }}</div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="stats-grid stats-grid-5" style="margin-bottom: 24px;">
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ $stats['actual_attendance'] }}</div>
            <div class="stat-label">{{ __('messages.days_present') ?? 'أيام الحضور' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ $stats['absence_count'] }}</div>
            <div class="stat-label">{{ __('messages.days_absent') ?? 'أيام الغياب' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ $stats['on_time_count'] }}</div>
            <div class="stat-label">{{ __('messages.on_time') }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ number_format($stats['total_late_minutes'] / 60, 1) }}h</div>
            <div class="stat-label">{{ __('messages.total_late_hours') ?? 'ساعات التأخير' }}</div>
        </div>
    </div>
    <div class="stat-card stat-card-sm">
        <div class="stat-content">
            <div class="stat-value">{{ number_format($stats['total_overtime_minutes'] / 60, 1) }}h</div>
            <div class="stat-label">{{ __('messages.overtime_hours') ?? 'ساعات إضافية' }}</div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Daily Trend Chart -->
    <div class="card">
        <div class="card-header">
            <h3>{{ __('messages.daily_attendance_trend') ?? 'اتجاه الحضور اليومي' }}</h3>
        </div>
        <div class="card-body">
            <canvas id="dailyTrendChart" height="300"></canvas>
        </div>
    </div>

    <!-- Late Breakdown Pie Chart -->
    <div class="card">
        <div class="card-header">
            <h3>{{ __('messages.late_breakdown') ?? 'توزيع التأخيرات' }}</h3>
        </div>
        <div class="card-body">
            @if(array_sum($lateBreakdown['data'] ?? []) > 0)
                <canvas id="lateBreakdownChart" height="300"></canvas>
            @else
                <div style="display:flex;align-items:center;justify-content:center;height:300px;color:var(--text-secondary);">
                    <div style="text-align:center;">
                        <div style="font-size:2.5rem;margin-bottom:8px;">✅</div>
                        <p>{{ __('messages.no_late_arrivals') ?? 'لا توجد تأخيرات في الفترة المحددة' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Weekly Pattern Chart -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3>{{ __('messages.weekly_pattern') ?? 'نمط الحضور الأسبوعي' }}</h3>
    </div>
    <div class="card-body">
        <canvas id="weeklyPatternChart" height="200"></canvas>
    </div>
</div>

<!-- Two Column Layout -->
<div class="charts-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Best Employees -->
    <div class="card">
        <div class="card-header">
            <h3>🏆 {{ __('messages.best_punctuality') ?? 'أفضل الموظفين التزاماً' }}</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.punctuality') }}</th>
                        <th>{{ __('messages.attendance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rankings['best'] as $index => $emp)
                        <tr>
                            <td>
                                @if($index === 0)
                                    <span class="rank-badge rank-gold">🥇</span>
                                @elseif($index === 1)
                                    <span class="rank-badge rank-silver">🥈</span>
                                @elseif($index === 2)
                                    <span class="rank-badge rank-bronze">🥉</span>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td>
                                <strong>{{ $emp['employee_name'] }}</strong>
                                <br><small style="color: var(--text-secondary);">{{ $emp['department'] ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $emp['punctuality_rate'] }}%</span>
                            </td>
                            <td>{{ $emp['attendance_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('messages.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Worst Employees (Needs Improvement) -->
    <div class="card">
        <div class="card-header">
            <h3>⚠️ {{ __('messages.needs_improvement') ?? 'بحاجة لتحسين' }}</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('messages.employee') }}</th>
                        <th>{{ __('messages.late_days') }}</th>
                        <th>{{ __('messages.avg_late') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rankings['worst'] as $index => $emp)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <strong>{{ $emp['employee_name'] }}</strong>
                                <br><small style="color: var(--text-secondary);">{{ $emp['department'] ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge badge-danger">{{ $emp['late_days'] }} {{ __('messages.days') }}</span>
                            </td>
                            <td>{{ $emp['avg_late_minutes'] }} {{ __('messages.min') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ __('messages.no_data') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Department Statistics -->
@if(count($departmentStats) > 0)
<div class="card">
    <div class="card-header">
        <h3>📊 {{ __('messages.department_statistics') ?? 'إحصائيات الأقسام' }}</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.department') }}</th>
                    <th>{{ __('messages.employees') }}</th>
                    <th>{{ __('messages.attendance_rate') }}</th>
                    <th>{{ __('messages.late_days') }}</th>
                    <th>{{ __('messages.absent_days') }}</th>
                    <th>{{ __('messages.avg_late') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentStats as $dept)
                    <tr>
                        <td><strong>{{ $dept['name'] }}</strong></td>
                        <td>{{ $dept['employee_count'] }}</td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $dept['attendance_rate'] }}%; background: {{ $dept['attendance_rate'] >= 90 ? '#34d399' : ($dept['attendance_rate'] >= 75 ? '#fbbf24' : '#f87171') }};"></div>
                                <span>{{ $dept['attendance_rate'] }}%</span>
                            </div>
                        </td>
                        <td>{{ $dept['total_late_days'] }}</td>
                        <td>{{ $dept['total_absent_days'] }}</td>
                        <td>{{ $dept['avg_late_minutes'] }} {{ __('messages.min') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
    .stats-grid-5 {
        grid-template-columns: repeat(5, 1fr) !important;
    }
    
    .stat-card-sm {
        padding: 16px !important;
    }
    
    .stat-card-sm .stat-value {
        font-size: 1.5rem !important;
    }
    
    .charts-grid {
        display: grid;
        gap: 24px;
    }
    
    .ranking-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .ranking-table th,
    .ranking-table td {
        padding: 12px 16px;
        text-align: right;
        border-bottom: 1px solid var(--border-color);
    }
    
    .ranking-table th {
        background: var(--bg-secondary);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: var(--text-secondary);
    }
    
    .rank-badge {
        font-size: 1.2rem;
    }
    
    .progress-bar-container {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .progress-bar {
        height: 8px;
        border-radius: 4px;
        flex: 1;
        max-width: 100px;
    }
    
    @media (max-width: 1024px) {
        .stats-grid-5 {
            grid-template-columns: repeat(3, 1fr) !important;
        }
        
        .charts-grid {
            grid-template-columns: 1fr !important;
        }
    }
    
    @media (max-width: 768px) {
        .stats-grid-5 {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Trend Chart
        const dailyTrendCtx = document.getElementById('dailyTrendChart').getContext('2d');
        new Chart(dailyTrendCtx, {
            type: 'line',
            data: @json($dailyTrend),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        rtl: {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                        labels: {
                            color: '#9ca3af',
                            font: { family: 'inherit' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: '#9ca3af' }
                    }
                }
            }
        });

        // Late Breakdown Pie Chart
        const lateBreakdownEl = document.getElementById('lateBreakdownChart');
        if (lateBreakdownEl) {
            const lateBreakdown = @json($lateBreakdown);
            const lateBreakdownCtx = lateBreakdownEl.getContext('2d');
            new Chart(lateBreakdownCtx, {
                type: 'doughnut',
                data: {
                    labels: lateBreakdown.labels,
                    datasets: [{
                        data: lateBreakdown.data,
                        backgroundColor: lateBreakdown.colors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            rtl: {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                            labels: {
                                color: '#9ca3af',
                                font: { family: 'inherit' },
                                padding: 16
                            }
                        }
                    }
                }
            });
        }

        // Weekly Pattern Chart
        const weeklyPattern = @json($weeklyPattern);
        const weeklyPatternCtx = document.getElementById('weeklyPatternChart').getContext('2d');
        new Chart(weeklyPatternCtx, {
            type: 'bar',
            data: {
                labels: weeklyPattern.labels,
                datasets: [
                    {
                        label: '{{ __("messages.attendance") }}',
                        data: weeklyPattern.attendance,
                        backgroundColor: 'rgba(99, 102, 241, 0.7)',
                        borderRadius: 4
                    },
                    {
                        label: '{{ __("messages.late") }}',
                        data: weeklyPattern.late,
                        backgroundColor: 'rgba(251, 191, 36, 0.7)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        rtl: {{ app()->getLocale() == 'ar' ? 'true' : 'false' }},
                        labels: {
                            color: '#9ca3af',
                            font: { family: 'inherit' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(156, 163, 175, 0.1)' },
                        ticks: { color: '#9ca3af' }
                    }
                }
            }
        });
    });
</script>
@endsection
