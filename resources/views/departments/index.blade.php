@extends('layouts.tenant')

@section('title', __('messages.departments'))

@section('header')
    <h1>🏢 {{ __('messages.departments') }}</h1>
    <p>{{ __('messages.manage_departments') ?? 'إدارة الأقسام والهيكل التنظيمي' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('departments.create') }}" class="btn btn-primary">
        ➕ {{ __('messages.add_department') ?? 'إضافة قسم' }}
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--primary-light);">
            🏢
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">{{ __('messages.total_departments') ?? 'إجمالي الأقسام' }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(52, 211, 153, 0.15); color: #34d399;">
            ✓
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-label">{{ __('messages.active_departments') ?? 'الأقسام النشطة' }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24;">
            📊
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['root'] }}</div>
            <div class="stat-label">{{ __('messages.main_departments') ?? 'الأقسام الرئيسية' }}</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(96, 165, 250, 0.15); color: #60a5fa;">
            👥
        </div>
        <div class="stat-content">
            <div class="stat-value">{{ $stats['with_employees'] }}</div>
            <div class="stat-label">{{ __('messages.with_employees') ?? 'تحتوي موظفين' }}</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="filter-card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('departments.index') }}" class="filter-form">
        <div class="filter-row">
            <div class="form-group">
                <label class="form-label">{{ __('messages.search') }}</label>
                <input type="text" name="search" class="form-control" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('messages.search_departments') ?? 'بحث في الأقسام...' }}">
            </div>

            @if($branches->count() > 0)
            <div class="form-group">
                <label class="form-label">{{ __('messages.branch') }}</label>
                <select name="branch_id" class="form-control">
                    <option value="">{{ __('messages.all_branches') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">{{ __('messages.status') }}</label>
                <select name="status" class="form-control">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                        {{ __('messages.active') }}
                    </option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                        {{ __('messages.inactive') }}
                    </option>
                </select>
            </div>

            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="submit" class="btn btn-primary">{{ __('messages.filter') }}</button>
                <a href="{{ route('departments.index') }}" class="btn btn-secondary" style="margin-right: 8px;">
                    {{ __('messages.reset') ?? 'إعادة تعيين' }}
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Departments Table -->
<div class="card">
    <div class="card-header">
        <h3>📋 {{ __('messages.departments_list') ?? 'قائمة الأقسام' }}</h3>
        <span class="badge badge-secondary">{{ count($hierarchicalDepartments) }} {{ __('messages.department') ?? 'قسم' }}</span>
    </div>
    <div class="card-body" style="padding: 0; overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('messages.department_name') ?? 'اسم القسم' }}</th>
                    <th>{{ __('messages.code') }}</th>
                    <th>{{ __('messages.branch') }}</th>
                    <th>{{ __('messages.manager') ?? 'المدير' }}</th>
                    <th class="text-center">{{ __('messages.employees') }}</th>
                    <th class="text-center">{{ __('messages.status') }}</th>
                    <th class="text-center">{{ __('messages.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hierarchicalDepartments as $department)
                    <tr class="{{ !$department->is_active ? 'inactive-row' : '' }}">
                        <td>
                            <div class="department-cell" style="padding-right: {{ ($department->hierarchy_level ?? 0) * 24 }}px;">
                                @if($department->hierarchy_level > 0)
                                    <span class="hierarchy-line">└─</span>
                                @endif
                                <div class="department-color" style="background: {{ $department->color }};">
                                    {{ mb_substr($department->name, 0, 1) }}
                                </div>
                                <div class="department-info">
                                    <span class="department-name">{{ $department->name }}</span>
                                    @if($department->description)
                                        <span class="department-desc">{{ Str::limit($department->description, 50) }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="code-badge">{{ $department->code }}</span>
                        </td>
                        <td>
                            @if($department->branch)
                                <span class="branch-badge">{{ $department->branch->name }}</span>
                            @else
                                <span class="text-muted">{{ __('messages.all_branches') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($department->manager)
                                <div class="manager-cell">
                                    <div class="avatar-xs">{{ mb_substr($department->manager->name, 0, 1) }}</div>
                                    <span>{{ $department->manager->name }}</span>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="employee-count {{ $department->employees_count > 0 ? 'has-employees' : '' }}">
                                {{ $department->employees_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($department->is_active)
                                <span class="status-badge status-active">{{ __('messages.active') }}</span>
                            @else
                                <span class="status-badge status-inactive">{{ __('messages.inactive') }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="action-buttons">
                                <a href="{{ route('departments.edit', $department) }}" class="btn-icon" title="{{ __('messages.edit') }}">
                                    ✏️
                                </a>
                                <form action="{{ route('departments.destroy', $department) }}" method="POST" 
                                      style="display: inline;" 
                                      onsubmit="return confirm('{{ __('messages.confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger" title="{{ __('messages.delete') }}"
                                            {{ !$department->canBeDeleted() ? 'disabled' : '' }}>
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">
                            <div class="empty-icon">🏢</div>
                            <h3>{{ __('messages.no_departments') ?? 'لا توجد أقسام' }}</h3>
                            <p>{{ __('messages.add_first_department') ?? 'أضف القسم الأول لبدء تنظيم الهيكل الإداري' }}</p>
                            <a href="{{ route('departments.create') }}" class="btn btn-primary">
                                ➕ {{ __('messages.add_department') ?? 'إضافة قسم' }}
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Organizational Chart Preview -->
@if($departments->count() > 0)
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3>🌳 {{ __('messages.org_chart') ?? 'الهيكل التنظيمي' }}</h3>
    </div>
    <div class="card-body">
        <div class="org-chart">
            @foreach($departments->where('parent_id', null) as $rootDept)
                <div class="org-node root" style="--dept-color: {{ $rootDept->color }};">
                    <div class="org-node-content">
                        <div class="org-node-header" style="background: {{ $rootDept->color }};">
                            {{ $rootDept->name }}
                        </div>
                        <div class="org-node-body">
                            <span class="org-code">{{ $rootDept->code }}</span>
                            <span class="org-count">{{ $rootDept->employees_count }} {{ __('messages.employees') }}</span>
                        </div>
                    </div>
                    @if($rootDept->children->count() > 0)
                        <div class="org-children">
                            @foreach($rootDept->children as $child)
                                <div class="org-node child" style="--dept-color: {{ $child->color }};">
                                    <div class="org-node-content">
                                        <div class="org-node-header" style="background: {{ $child->color }};">
                                            {{ $child->name }}
                                        </div>
                                        <div class="org-node-body">
                                            <span class="org-count">{{ $child->employees_count ?? 0 }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
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
        align-items: flex-end;
    }
    
    .filter-row .form-group {
        flex: 1;
        min-width: 150px;
    }
    
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th {
        background: var(--bg-secondary);
        padding: 12px 16px;
        text-align: right;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
    }
    
    .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .data-table .text-center {
        text-align: center;
    }
    
    .inactive-row {
        opacity: 0.6;
        background: var(--bg-secondary);
    }
    
    .department-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .hierarchy-line {
        color: var(--text-secondary);
        font-family: monospace;
        margin-left: 4px;
    }
    
    .department-color {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 16px;
    }
    
    .department-info {
        display: flex;
        flex-direction: column;
    }
    
    .department-name {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .department-desc {
        font-size: 12px;
        color: var(--text-secondary);
    }
    
    .code-badge {
        display: inline-block;
        padding: 4px 10px;
        background: var(--bg-secondary);
        border-radius: 6px;
        font-family: monospace;
        font-size: 12px;
        font-weight: 600;
    }
    
    .branch-badge {
        display: inline-block;
        padding: 4px 10px;
        background: rgba(99, 102, 241, 0.1);
        color: var(--primary-light);
        border-radius: 6px;
        font-size: 12px;
    }
    
    .manager-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .avatar-xs {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--primary-light);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
    }
    
    .employee-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 28px;
        padding: 0 10px;
        background: var(--bg-secondary);
        border-radius: 14px;
        font-weight: 600;
        font-size: 13px;
    }
    
    .employee-count.has-employees {
        background: rgba(52, 211, 153, 0.15);
        color: #10b981;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-active {
        background: rgba(52, 211, 153, 0.15);
        color: #10b981;
    }
    
    .status-inactive {
        background: rgba(248, 113, 113, 0.15);
        color: #f87171;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: center;
    }
    
    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-secondary);
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .btn-icon:hover {
        background: var(--primary-light);
        transform: scale(1.1);
    }
    
    .btn-icon:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px !important;
    }
    
    .empty-icon {
        font-size: 64px;
        margin-bottom: 16px;
    }
    
    .empty-state h3 {
        margin: 0 0 8px;
        color: var(--text-primary);
    }
    
    .empty-state p {
        color: var(--text-secondary);
        margin-bottom: 24px;
    }
    
    .text-muted {
        color: var(--text-secondary);
    }
    
    /* Org Chart Styles */
    .org-chart {
        display: flex;
        flex-wrap: wrap;
        gap: 32px;
        justify-content: center;
        padding: 20px;
    }
    
    .org-node {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    
    .org-node-content {
        background: var(--bg-secondary);
        border-radius: 12px;
        overflow: hidden;
        min-width: 140px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    
    .org-node-header {
        padding: 10px 16px;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    
    .org-node-body {
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .org-code {
        font-family: monospace;
        font-size: 11px;
        color: var(--text-secondary);
    }
    
    .org-count {
        font-size: 12px;
        color: var(--text-primary);
    }
    
    .org-children {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed var(--border-color);
        justify-content: center;
    }
    
    .org-node.child .org-node-content {
        min-width: 100px;
    }
    
    .org-node.child .org-node-header {
        padding: 8px 12px;
        font-size: 12px;
    }
    
    @media (max-width: 768px) {
        .filter-row .form-group {
            flex: 1 1 100%;
        }
        
        .org-chart {
            flex-direction: column;
            align-items: center;
        }
    }
</style>
@endpush
