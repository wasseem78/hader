@extends('layouts.tenant')

@section('title', __('messages.edit_branch'))

@section('header')
    <h1>{{ __('messages.edit_branch') }}</h1>
    <p>{{ $branch->name }}</p>
@endsection

@section('header-actions')
    <a href="{{ route('branches.index') }}" class="btn btn-secondary">
        ← {{ __('messages.back') }}
    </a>
@endsection

@section('content')
<div class="form-card">
    <form action="{{ route('branches.update', $branch) }}" method="POST">
        @csrf
        @method('PUT')
        
        <!-- Basic Information -->
        <div class="form-section">
            <h3 class="form-section-title">{{ __('messages.basic_information') }}</h3>
            
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.branch_name') }} <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" 
                           placeholder="{{ __('messages.branch_name_placeholder') }}" 
                           value="{{ old('name', $branch->name) }}" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.branch_code') }}</label>
                    <input type="text" name="code" class="form-control" 
                           placeholder="{{ __('messages.branch_code_placeholder') }}" 
                           value="{{ old('code', $branch->code) }}">
                    <p class="form-hint">{{ __('messages.branch_code_hint') }}</p>
                    @error('code') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_headquarters" value="1" 
                           {{ old('is_headquarters', $branch->is_headquarters) ? 'checked' : '' }} 
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
                       value="{{ old('address', $branch->address) }}">
            </div>

            <div class="form-grid-3">
                <div class="form-group">
                    <label class="form-label">{{ __('messages.city') }}</label>
                    <input type="text" name="city" class="form-control" 
                           placeholder="{{ __('messages.city_placeholder') }}" 
                           value="{{ old('city', $branch->city) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.country') }}</label>
                    <input type="text" name="country" class="form-control" 
                           placeholder="{{ __('messages.country_placeholder') }}" 
                           value="{{ old('country', $branch->country) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.timezone') }}</label>
                    <select name="timezone" class="form-control">
                        <option value="">{{ __('messages.select_timezone') }}</option>
                        <option value="UTC" {{ old('timezone', $branch->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="Asia/Riyadh" {{ old('timezone', $branch->timezone) == 'Asia/Riyadh' ? 'selected' : '' }}>Asia/Riyadh (GMT+3)</option>
                        <option value="Asia/Dubai" {{ old('timezone', $branch->timezone) == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GMT+4)</option>
                        <option value="Asia/Baghdad" {{ old('timezone', $branch->timezone) == 'Asia/Baghdad' ? 'selected' : '' }}>Asia/Baghdad (GMT+3)</option>
                        <option value="Europe/London" {{ old('timezone', $branch->timezone) == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT+0)</option>
                        <option value="America/New_York" {{ old('timezone', $branch->timezone) == 'America/New_York' ? 'selected' : '' }}>America/New York (GMT-5)</option>
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
                           value="{{ old('phone', $branch->phone) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.email') }}</label>
                    <input type="email" name="email" class="form-control" 
                           placeholder="{{ __('messages.email_placeholder') }}" 
                           value="{{ old('email', $branch->email) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.manager_name') }}</label>
                    <input type="text" name="manager_name" class="form-control" 
                           placeholder="{{ __('messages.manager_name_placeholder') }}" 
                           value="{{ old('manager_name', $branch->manager_name) }}">
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
                           value="{{ old('work_start_time', $branch->work_start_time ? \Carbon\Carbon::parse($branch->work_start_time)->format('H:i') : '09:00') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.work_end_time') }}</label>
                    <input type="time" name="work_end_time" class="form-control" 
                           value="{{ old('work_end_time', $branch->work_end_time ? \Carbon\Carbon::parse($branch->work_end_time)->format('H:i') : '17:00') }}">
                </div>
            </div>
        </div>

        <!-- Notes & Status -->
        <div class="form-section">
            <div class="form-group">
                <label class="form-label">{{ __('messages.notes') }}</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="{{ __('messages.branch_notes_placeholder') }}">{{ old('notes', $branch->notes) }}</textarea>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" 
                           {{ old('is_active', $branch->is_active) ? 'checked' : '' }}
                           style="margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 8px;">
                    {{ __('messages.branch_active') }}
                </label>
            </div>
        </div>

        <!-- Statistics -->
        <div class="form-section">
            <h3 class="form-section-title">{{ __('messages.statistics') }}</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value">{{ $branch->employees()->count() }}</div>
                    <div class="stat-label">{{ __('messages.employees') }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📱</div>
                    <div class="stat-value">{{ $branch->devices()->count() }}</div>
                    <div class="stat-label">{{ __('messages.devices') }}</div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.update_branch') }}</button>
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
    }
    .required {
        color: var(--danger);
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 16px;
    }
    .stat-card {
        background: rgba(99, 102, 241, 0.1);
        border: 1px solid rgba(99, 102, 241, 0.2);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
    }
    .stat-icon {
        font-size: 24px;
        margin-bottom: 8px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary-light);
    }
    .stat-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 4px;
    }
</style>
@endsection
