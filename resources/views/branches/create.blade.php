@extends('layouts.tenant')

@section('title', __('messages.add_branch'))

@section('header')
    <h1>{{ __('messages.add_branch') }}</h1>
    <p>{{ __('messages.create_new_branch') }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('branches.store') }}" method="POST">
        @csrf
        
        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="form-section-title">{{ __('messages.basic_information') }}</h3>
            
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.branch_name') }} <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="{{ __('messages.branch_name_placeholder') }}" 
                           value="{{ old('name') }}" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.branch_code') }}</label>
                    <input type="text" name="code" class="form-control" 
                           placeholder="{{ __('messages.branch_code_placeholder') }}" 
                           value="{{ old('code') }}">
                    <p class="form-hint">{{ __('messages.branch_code_hint') }}</p>
                    @error('code') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_headquarters" value="1" {{ old('is_headquarters') ? 'checked' : '' }} 
                           style="margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 8px;">
                    {{ __('messages.is_headquarters') }}
                </label>
                <p class="form-hint">{{ __('messages.headquarters_hint') }}</p>
            </div>
        </div>

        <!-- Location -->
        <div class="form-section">
            <h3 class="form-section-title">{{ __('messages.location') }}</h3>
            
            <div class="form-group">
                <label class="form-label">{{ __('messages.address') }}</label>
                <input type="text" name="address" class="form-control" 
                       placeholder="{{ __('messages.address_placeholder') }}" 
                       value="{{ old('address') }}">
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.city') }}</label>
                    <input type="text" name="city" class="form-control" 
                           placeholder="{{ __('messages.city_placeholder') }}" 
                           value="{{ old('city') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.country') }}</label>
                    <input type="text" name="country" class="form-control" 
                           placeholder="{{ __('messages.country_placeholder') }}" 
                           value="{{ old('country') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.timezone') }}</label>
                    <select name="timezone" class="form-control">
                        <option value="">{{ __('messages.select_timezone') }}</option>
                        <option value="UTC" {{ old('timezone') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="Asia/Riyadh" {{ old('timezone') == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh (GMT+3)</option>
                        <option value="Asia/Dubai" {{ old('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GMT+4)</option>
                        <option value="Asia/Baghdad" {{ old('timezone') == 'Asia/Baghdad' ? 'selected' : '' }}>Asia/Baghdad (GMT+3)</option>
                        <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT+0)</option>
                        <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>America/New York (GMT-5)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="form-section">
            <h3 class="form-section-title">{{ __('messages.contact_information') }}</h3>
            
            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.phone') }}</label>
                    <input type="tel" name="phone" class="form-control" 
                           placeholder="{{ __('messages.phone_placeholder') }}" 
                           value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" name="email" class="form-control" 
                           placeholder="{{ __('messages.email_placeholder') }}" 
                           value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.manager_name') }}</label>
                    <input type="text" name="manager_name" class="form-control" 
                           placeholder="{{ __('messages.manager_name_placeholder') }}" 
                           value="{{ old('manager_name') }}">
                </div>
            </div>
        </div>

        <!-- Work Hours -->
        <div class="form-section">
            <h3 class="form-section-title">{{ __('messages.work_hours') }}</h3>
            
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.work_start_time') }}</label>
                    <input type="time" name="work_start_time" class="form-control" 
                           value="{{ old('work_start_time', '09:00') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.work_end_time') }}</label>
                    <input type="time" name="work_end_time" class="form-control" 
                           value="{{ old('work_end_time', '17:00') }}">
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="form-section">
            <div class="form-group">
                <label class="form-label">{{ __('messages.notes') }}</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="{{ __('messages.branch_notes_placeholder') }}">{{ old('notes') }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           style="margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 8px;">
                    {{ __('messages.branch_active') }}
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.save_branch') }}</button>
            <a href="{{ route('branches.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </div>
    </form>
</div>

<style>
    .form-section {
        margin-bottom: 32px;
        padding-bottom: 24px;
        border-bottom: 1px solid var(--glass-border);
    }
    .form-section:last-of-type {
        border-bottom: none;
    }
    .form-section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .required {
        color: var(--danger);
    }
</style>
@endsection
