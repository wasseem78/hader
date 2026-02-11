@extends('layouts.tenant')

@section('title', __('messages.settings'))

@section('header')
    <h1>{{ __('messages.settings') }}</h1>
    <p>{{ __('messages.configure_system_preferences') }}</p>
@endsection

@section('content')
<div class="form-card" style="max-width: 800px;">
    <div class="card-header">
        <h3>{{ app()->getLocale() == 'ar' ? 'معلومات الشركة' : 'Company Information' }}</h3>
    </div>
    
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label class="form-label">{{ __('messages.company_name_label') }}</label>
            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $company?->name) }}" required>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.company_logo') }}</label>
            @if($company && $company->logo)
                <div style="margin-bottom: 12px;">
                    <img src="{{ route('tenant.storage', ['path' => $company->logo]) }}" alt="Company Logo" style="height: 80px; border-radius: 12px; border: 1px solid var(--glass-border);">
                </div>
            @endif
            <input type="file" name="logo" class="form-control" accept="image/*">
            <p class="form-hint">{{ app()->getLocale() == 'ar' ? 'الحجم الموصى به: 200×200 بكسل. الحد الأقصى: 2 ميجابايت.' : 'Recommended size: 200x200px. Max: 2MB.' }}</p>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.address') }}</label>
            <textarea name="address" class="form-control" rows="3">{{ old('address', $company?->address) }}</textarea>
        </div>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.phone') }}</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $company?->phone) }}">
            </div>
            <div class="form-group">
                <label class="form-label">{{ __('messages.website') }}</label>
                <input type="url" name="website" class="form-control" value="{{ old('website', $company?->website) }}" placeholder="https://example.com">
            </div>
        </div>

        <div class="section-divider" style="border-top: 1px solid var(--glass-border); margin: 32px 0;"></div>
        
        <h3 style="font-size: 16px; font-weight: 600; color: var(--text-primary); margin-bottom: 20px;">{{ __('messages.system_preferences') }}</h3>

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">{{ __('messages.timezone') }}</label>
                <select name="settings[timezone]" class="form-control">
                    <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                    <option value="Asia/Riyadh" {{ ($settings['timezone'] ?? '') == 'Asia/Riyadh' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'آسيا/الرياض' : 'Asia/Riyadh' }}</option>
                    <option value="Asia/Dubai" {{ ($settings['timezone'] ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'آسيا/دبي' : 'Asia/Dubai' }}</option>
                    <option value="Europe/London" {{ ($settings['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'أوروبا/لندن' : 'Europe/London' }}</option>
                    <option value="America/New_York" {{ ($settings['timezone'] ?? '') == 'America/New_York' ? 'selected' : '' }}>{{ app()->getLocale() == 'ar' ? 'أمريكا/نيويورك' : 'America/New_York' }}</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.week_start') }}</label>
                <select name="settings[week_start]" class="form-control">
                    <option value="sunday" {{ ($settings['week_start'] ?? '') == 'sunday' ? 'selected' : '' }}>{{ __('messages.sunday') }}</option>
                    <option value="monday" {{ ($settings['week_start'] ?? '') == 'monday' ? 'selected' : '' }}>{{ __('messages.monday') }}</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.grace_period') }}</label>
            <div style="display: flex; align-items: center; gap: 10px;">
                <input type="number" name="settings[grace_period]" class="form-control" style="max-width: 120px;" value="{{ $settings['grace_period'] ?? 15 }}">
                <span style="color: var(--text-muted); font-size: 13px;">{{ app()->getLocale() == 'ar' ? 'دقيقة' : 'minutes' }}</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ __('messages.save_settings') }}</button>
        </div>
    </form>
</div>
@endsection

