@extends('layouts.tenant')

@section('title', __('messages.edit_employee'))

@section('header')
    <h1>{{ __('messages.edit_employee') }}</h1>
    <p>{{ $employee->name }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('employees.update', ['employee' => $employee->uuid]) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-grid-2">
            <div class="form-group">
                <label for="name" class="form-label">{{ __('messages.full_name_label') }}</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('messages.email_label') }}</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $employee->email) }}" readonly style="opacity: 0.6;">
                <p class="form-hint">{{ __('messages.email_cannot_be_changed') }}</p>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label for="employee_id" class="form-label">{{ __('messages.employee_id') }}</label>
                <input type="text" name="employee_id" id="employee_id" class="form-control" value="{{ old('employee_id', $employee->employee_id) }}">
                @error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="branch_id" class="form-label">{{ __('messages.branch') }}</label>
                <select name="branch_id" id="branch_id" class="form-control">
                    <option value="">{{ __('messages.select_branch') }}</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>
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
                <label for="department" class="form-label">{{ __('messages.department') }}</label>
                @if(isset($departments) && $departments->count() > 0)
                    <select name="department_id" id="department_id" class="form-control">
                        <option value="">{{ __('messages.select_department') ?? 'اختر القسم' }}</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" 
                                    {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}
                                    style="color: {{ $dept->color }};">
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" name="department" id="department" class="form-control" value="{{ old('department', $employee->department) }}">
                @endif
            </div>

            <div class="form-group">
                <label for="position" class="form-label">{{ __('messages.position') }}</label>
                <input type="text" name="position" id="position" class="form-control" value="{{ old('position', $employee->position) }}">
            </div>
        </div>

        <div class="form-group">
            <label for="device_user_id" class="form-label">{{ __('messages.device_user_id') }}</label>
            <input type="number" name="device_user_id" id="device_user_id" class="form-control" value="{{ old('device_user_id', $employee->device_user_id) }}">
            <p class="form-hint">{{ __('messages.device_user_id_help') }}</p>
        </div>

        @if(isset($shifts) && $shifts->count() > 0)
        <div class="form-group">
            <label class="form-label">{{ __('messages.assigned_shifts') }}</label>
            <p class="form-hint" style="margin-bottom: 12px;">{{ __('messages.select_employee_shifts_hint') ?? 'حدد الورديات التي يتبعها هذا الموظف' }}</p>
            <div class="checkbox-grid">
                @php
                    $employeeShiftIds = old('shifts', $employee->shifts->pluck('id')->toArray());
                @endphp
                @foreach($shifts as $shift)
                    <label class="checkbox-label shift-checkbox">
                        <input type="checkbox" name="shifts[]" value="{{ $shift->id }}" 
                            {{ in_array($shift->id, $employeeShiftIds) ? 'checked' : '' }}>
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
            <button type="submit" class="btn btn-primary">{{ __('messages.update_employee') }}</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
