@extends('layouts.tenant')

@section('title', __('messages.edit_department') ?? 'تعديل القسم')

@section('header')
    <h1>✏️ {{ __('messages.edit_department') ?? 'تعديل القسم' }}</h1>
    <p>{{ $department->name }}</p>
@endsection

@section('content')
<div class="form-container">
    <form action="{{ route('departments.update', $department) }}" method="POST" class="department-form">
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="section-title">📋 {{ __('messages.basic_information') }}</h3>
            
            <div class="form-row">
                <div class="form-group flex-2">
                    <label class="form-label required">{{ __('messages.department_name') ?? 'اسم القسم' }}</label>
                    <input type="text" name="name" class="form-control @error('name') error @enderror" 
                           value="{{ old('name', $department->name) }}" 
                           required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.department_code') ?? 'رمز القسم' }}</label>
                    <input type="text" name="code" class="form-control @error('code') error @enderror" 
                           value="{{ old('code', $department->code) }}"
                           maxlength="20">
                    @error('code')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.color') ?? 'اللون' }}</label>
                    <div class="color-picker-wrapper">
                        <input type="color" name="color" class="color-picker" value="{{ old('color', $department->color) }}">
                        <input type="text" class="form-control color-text" value="{{ old('color', $department->color) }}" readonly>
                    </div>
                </div>

                <div class="form-group flex-2">
                    <label class="form-label">{{ __('messages.description') ?? 'الوصف' }}</label>
                    <input type="text" name="description" class="form-control" 
                           value="{{ old('description', $department->description) }}">
                </div>
            </div>
        </div>

        <!-- Hierarchy -->
        <div class="form-section">
            <h3 class="section-title">🌳 {{ __('messages.hierarchy') ?? 'التسلسل الهرمي' }}</h3>
            
            <div class="form-row">
                @if($branches->count() > 0)
                <div class="form-group">
                    <label class="form-label">{{ __('messages.branch') }}</label>
                    <select name="branch_id" class="form-control">
                        <option value="">{{ __('messages.all_branches') }}</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $department->branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label">{{ __('messages.parent_department') ?? 'القسم الأب' }}</label>
                    <select name="parent_id" class="form-control">
                        <option value="">{{ __('messages.none_root') ?? 'لا يوجد (قسم رئيسي)' }}</option>
                        @foreach($parentOptions as $option)
                            <option value="{{ $option['id'] }}" {{ old('parent_id', $department->parent_id) == $option['id'] ? 'selected' : '' }}>
                                {{ $option['name'] }} ({{ $option['code'] }})
                            </option>
                        @endforeach
                    </select>
                    @error('parent_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Management -->
        <div class="form-section">
            <h3 class="section-title">👤 {{ __('messages.management') ?? 'الإدارة' }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.department_manager') ?? 'مدير القسم' }}</label>
                    <select name="manager_id" class="form-control">
                        <option value="">{{ __('messages.select_manager') ?? 'اختر المدير' }}</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('manager_id', $department->manager_id) == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}
                                @if($employee->employee_id) ({{ $employee->employee_id }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.sort_order') ?? 'ترتيب العرض' }}</label>
                    <input type="number" name="sort_order" class="form-control" 
                           value="{{ old('sort_order', $department->sort_order) }}" min="0">
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="form-section">
            <h3 class="section-title">📞 {{ __('messages.contact_information') }}</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.phone') }}</label>
                    <input type="tel" name="phone" class="form-control" 
                           value="{{ old('phone', $department->phone) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" name="email" class="form-control" 
                           value="{{ old('email', $department->email) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.location') }}</label>
                    <input type="text" name="location" class="form-control" 
                           value="{{ old('location', $department->location) }}">
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="form-section">
            <div class="form-check-card">
                <label class="form-check-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}>
                    <span class="check-content">
                        <span class="check-title">{{ __('messages.active') }}</span>
                        <span class="check-desc">{{ __('messages.department_active_hint') ?? 'القسم متاح لإضافة الموظفين' }}</span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Statistics -->
        @php $stats = $department->getStatistics(); @endphp
        <div class="form-section stats-section">
            <h3 class="section-title">📊 {{ __('messages.statistics') }}</h3>
            <div class="stats-mini-grid">
                <div class="stat-mini">
                    <span class="stat-mini-value">{{ $stats['total_employees'] }}</span>
                    <span class="stat-mini-label">{{ __('messages.employees') }}</span>
                </div>
                <div class="stat-mini">
                    <span class="stat-mini-value">{{ $stats['sub_departments'] }}</span>
                    <span class="stat-mini-label">{{ __('messages.sub_departments') ?? 'أقسام فرعية' }}</span>
                </div>
                <div class="stat-mini">
                    <span class="stat-mini-value">{{ $stats['total_with_sub'] }}</span>
                    <span class="stat-mini-label">{{ __('messages.total_with_sub') ?? 'شامل الفرعية' }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
            <a href="{{ route('departments.index') }}" class="btn btn-secondary">
                {{ __('messages.cancel') }}
            </a>
            <button type="submit" class="btn btn-primary">
                💾 {{ __('messages.update_department') ?? 'تحديث القسم' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .form-container {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .form-section {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin: 0 0 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 16px;
    }
    
    .form-row:last-child {
        margin-bottom: 0;
    }
    
    .form-group {
        flex: 1;
        min-width: 200px;
    }
    
    .form-group.flex-2 {
        flex: 2;
    }
    
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
    }
    
    .form-label.required::after {
        content: '*';
        color: #ef4444;
        margin-right: 4px;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: var(--primary-light);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    
    .form-control.error {
        border-color: #ef4444;
    }
    
    .error-message {
        color: #ef4444;
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }
    
    .color-picker-wrapper {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .color-picker {
        width: 50px;
        height: 42px;
        padding: 2px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        cursor: pointer;
    }
    
    .color-text {
        flex: 1;
        font-family: monospace;
        text-transform: uppercase;
    }
    
    .form-check-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 16px;
    }
    
    .form-check-label {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        cursor: pointer;
    }
    
    .form-check-label input {
        margin-top: 4px;
        width: 18px;
        height: 18px;
        accent-color: var(--primary-light);
    }
    
    .check-content {
        display: flex;
        flex-direction: column;
    }
    
    .check-title {
        font-weight: 600;
        color: var(--text-primary);
    }
    
    .check-desc {
        font-size: 13px;
        color: var(--text-secondary);
    }
    
    .stats-section {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.1));
    }
    
    .stats-mini-grid {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .stat-mini {
        flex: 1;
        min-width: 120px;
        background: var(--bg-primary);
        padding: 16px;
        border-radius: 10px;
        text-align: center;
    }
    
    .stat-mini-value {
        display: block;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary-light);
    }
    
    .stat-mini-label {
        font-size: 13px;
        color: var(--text-secondary);
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 20px;
    }
    
    @media (max-width: 768px) {
        .form-group {
            flex: 1 1 100%;
        }
        
        .form-group.flex-2 {
            flex: 1 1 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelector('.color-picker').addEventListener('input', function() {
        document.querySelector('.color-text').value = this.value.toUpperCase();
    });
</script>
@endpush
