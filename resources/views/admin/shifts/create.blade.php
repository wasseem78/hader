@extends('layouts.tenant')

@section('title', __('messages.add_new_shift'))

@section('header')
    <h1>{{ __('messages.add_new_shift') }}</h1>
    <p>{{ __('messages.create_schedule_desc') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back_to_list') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('admin.shifts.store') }}" method="POST">
        @csrf
        
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.shift_name') }}</label>
                <input type="text" name="name" class="form-control" placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: وردية صباحية' : 'e.g. Morning Shift' }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.shift_code') }}</label>
                <input type="text" name="code" class="form-control" placeholder="{{ app()->getLocale() == 'ar' ? 'مثال: MORNING' : 'e.g. MORNING' }}" required>
            </div>
        </div>

        @if(isset($branches) && $branches->count() > 0)
        <div class="form-group">
            <label class="form-label">{{ __('messages.branch') }}</label>
            <select name="branch_id" class="form-control">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
            <p class="form-hint">{{ __('messages.branch_optional_hint') ?? (app()->getLocale() == 'ar' ? 'اختياري: حدد الفرع التابعة له هذه الوردية' : 'Optional: Select the branch this shift belongs to') }}</p>
        </div>
        @endif

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.start_time') }}</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('messages.end_time') }}</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.working_days') }}</label>
            <div class="checkbox-grid">
                @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                    <label class="checkbox-label">
                        <input type="checkbox" name="working_days[]" value="{{ $day }}" {{ in_array($day, ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']) ? 'checked' : '' }}>
                        <span class="checkbox-text">{{ __('messages.' . $day) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('messages.save_shift') }}</button>
        </div>
    </form>
</div>
@endsection
