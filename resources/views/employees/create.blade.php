@extends('layouts.tenant')

@section('title', __('messages.add_employee'))

@section('header')
    <h1>{{ __('messages.add_employee') }}</h1>
    <p>{{ __('messages.add_new_team_member') ?? 'إضافة عضو جديد للفريق' }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
        
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.full_name_label') }}</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" value="{{ old('name') }}" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.email_label') }}</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. john@company.com" value="{{ old('email') }}" required>
                @error('email') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.employee_id') }}</label>
                <input type="text" name="employee_id" class="form-control" placeholder="e.g. EMP001" value="{{ old('employee_id') }}">
                @error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('messages.branch') }}</label>
                <select name="branch_id" class="form-control">
                    <option value="">{{ __('messages.select_branch') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                            @if($branch->is_headquarters) ({{ __('messages.headquarters') }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('branch_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.department') }}</label>
                @if(isset($departments) && $departments->count() > 0)
                    <select name="department_id" class="form-control">
                        <option value="">{{ __('messages.select_department') ?? 'اختر القسم' }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}
                                    style="color: {{ $dept->color }};">
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="department" class="form-control" placeholder="e.g. Engineering" value="{{ old('department') }}">
                @endif
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('messages.position') }}</label>
                <input type="text" name="position" class="form-control" placeholder="e.g. Senior Developer" value="{{ old('position') }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.device_user_id') }}</label>
            <input type="number" name="device_user_id" class="form-control" placeholder="e.g. 101" value="{{ old('device_user_id') }}">
            <p class="form-hint">{{ __('messages.device_user_id_help') }}</p>
        </div>

        @if(isset($shifts) && $shifts->count() > 0)
        <div class="form-group">
            <label class="form-label">{{ __('messages.assigned_shifts') }}</label>
            <p class="form-hint" style="margin-bottom: 12px;">{{ __('messages.select_employee_shifts_hint') ?? 'حدد الورديات التي يتبعها هذا الموظف' }}</p>
            <div class="checkbox-grid">
                @foreach($shifts as $shift)
                    <label class="checkbox-label shift-checkbox">
                        <input type="checkbox" name="shifts[]" value="{{ $shift->id }}" 
                            {{ in_array($shift->id, old('shifts', [])) ? 'checked' : '' }}>
                        <span class="checkbox-content">
                            <span class="checkbox-text">{{ $shift->name }}</span>
                            <span class="shift-time">{{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('shifts') <span class="form-error">{{ $message }}</span> @enderror
        </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.save_employee') }}</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
