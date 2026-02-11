@extends('layouts.tenant')

@section('title', __('messages.reports'))

@section('header')
    <h1>{{ __('messages.reports') }}</h1>
    <p>{{ app()->getLocale() == 'ar' ? 'تحليل البيانات وتصدير التقارير' : 'Analyze data and export reports' }}</p>
@endsection

@section('content')
<!-- Summary Stats -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--primary-light);">
            📊
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['attendance']['total_records'] ?? 0 }}</div>
            <div class="stat-label">{{ __('messages.total_records') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 211, 153, 0.15); color: #34d399;">
            ✓
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['punctuality']['on_time'] ?? 0 }}</div>
            <div class="stat-label">{{ __('messages.on_time') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(248, 113, 113, 0.15); color: #f87171;">
            ⏰
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['punctuality']['late_count'] ?? 0 }}</div>
            <div class="stat-label">{{ __('messages.late_arrivals') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            👥
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $summary['attendance']['unique_employees'] ?? 0 }}</div>
            <div class="stat-label">{{ app()->getLocale() == 'ar' ? 'موظفين نشطين' : 'Active Employees' }}</div>
        </div>
    </div>
</div>

<div class="content-grid">
    <!-- Quick Reports -->
    <div class="card">
        <div class="card-header">
            <h3>{{ app()->getLocale() == 'ar' ? 'تقارير سريعة' : 'Quick Reports' }}</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="{{ route('reports.export', ['report_type' => 'daily', 'date' => date('Y-m-d'), 'type' => 'pdf']) }}" class="quick-action">
                    <div class="quick-action-icon">📄</div>
                    <div class="quick-action-content">
                        <strong>{{ app()->getLocale() == 'ar' ? 'تقرير اليوم' : "Today's Report" }}</strong>
                        <span>{{ date('M d, Y') }}</span>
                    </div>
                    <span style="color: var(--primary-light);">→</span>
                </a>
                <a href="{{ route('reports.export', ['report_type' => 'monthly', 'date' => date('Y-m-01'), 'type' => 'pdf']) }}" class="quick-action">
                    <div class="quick-action-icon">📅</div>
                    <div class="quick-action-content">
                        <strong>{{ app()->getLocale() == 'ar' ? 'تقرير الشهر الحالي' : 'Current Month Report' }}</strong>
                        <span>{{ date('F Y') }}</span>
                    </div>
                    <span style="color: var(--primary-light);">→</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Custom Export -->
    <div class="card">
        <div class="card-header">
            <h3>{{ __('messages.export_options') }}</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('reports.export') }}" method="GET">
                @if(isset($branches) && $branches->count() > 0)
                <div class="form-group">
                    <label class="form-label">{{ __('messages.branch') }}</label>
                    <select name="branch_id" class="form-control">
                        <option value="">{{ __('messages.all_branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ __('messages.report_type') }}</label>
                    <select name="report_type" class="form-control">
                        <option value="daily">{{ __('messages.daily_report') }}</option>
                        <option value="monthly">{{ __('messages.monthly_summary') }}</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">{{ __('messages.date') }}</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.format') }}</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="type" value="csv" checked>
                            <span class="radio-text">
                                <span class="radio-icon">📊</span> {{ __('messages.csv') }}
                            </span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="type" value="pdf">
                            <span class="radio-text">
                                <span class="radio-icon">📄</span> {{ __('messages.pdf') }}
                            </span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    ⬇️ {{ __('messages.download_report') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

