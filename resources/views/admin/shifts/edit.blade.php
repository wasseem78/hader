@extends('layouts.tenant')

@section('title', __('messages.edit_shift'))

@section('header')
    <h1>{{ __('messages.edit_shift') }}</h1>
    <p>{{ $shift->name }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back_to_list') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('admin.shifts.update', ['shift' => $shift->uuid]) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.shift_name') }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $shift->name) }}" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.shift_code') }}</label>
                <input type="text" name="code" class="form-control" value="{{ old('code', $shift->code) }}" required>
                @error('code') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        @if(isset($branches) && $branches->count() > 0)
        <div class="form-group">
            <label class="form-label">{{ __('messages.branch') }}</label>
            <select name="branch_id" class="form-control">
                <option value="">{{ __('messages.all_branches') }}</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $shift->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.start_time') }}</label>
                <input type="time" name="start_time" class="form-control" value="{{ old('start_time', is_string($shift->start_time) ? $shift->start_time : $shift->start_time?->format('H:i')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('messages.end_time') }}</label>
                <input type="time" name="end_time" class="form-control" value="{{ old('end_time', is_string($shift->end_time) ? $shift->end_time : $shift->end_time?->format('H:i')) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.working_days') }}</label>
            <div class="checkbox-grid">
                @php
                    $selectedDays = old('working_days', $shift->working_days ?? []);
                @endphp
                @foreach(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                    <label class="checkbox-label">
                        <input type="checkbox" name="working_days[]" value="{{ $day }}" {{ in_array($day, $selectedDays) ? 'checked' : '' }}>
                        <span class="checkbox-text">{{ __('messages.' . $day) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.shifts.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('messages.update_shift') ?? __('messages.save') }}</button>
        </div>
    </form>
</div>
@endsection
