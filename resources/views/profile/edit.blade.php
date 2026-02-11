@extends('layouts.tenant')

@section('title', __('messages.profile') ?? 'Profile')

@section('header')
    <h1>{{ __('messages.profile') ?? 'Profile' }}</h1>
    <p>{{ app()->getLocale() == 'ar' ? 'إدارة إعدادات حسابك' : 'Manage your account settings' }}</p>
@endsection

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px; max-width: 700px;">

    <!-- Profile Information -->
    <div class="form-card">
        <div class="card-header">
            <div>
                <h3>{{ app()->getLocale() == 'ar' ? 'معلومات الملف الشخصي' : 'Profile Information' }}</h3>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 4px;">{{ app()->getLocale() == 'ar' ? 'تحديث معلومات حسابك وبريدك الإلكتروني' : "Update your account's profile information and email address." }}</p>
            </div>
        </div>
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label class="form-label">{{ __('messages.name') ?? 'Name' }}</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('messages.email') ?? 'Email' }}</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ __('messages.save') ?? 'Save' }}</button>
                @if (session('status') === 'profile-updated')
                    <span class="badge badge-success">{{ app()->getLocale() == 'ar' ? 'تم الحفظ' : 'Saved' }}</span>
                @endif
            </div>
        </form>
    </div>

    <!-- Update Password -->
    <div class="form-card">
        <div class="card-header">
            <div>
                <h3>{{ app()->getLocale() == 'ar' ? 'تحديث كلمة المرور' : 'Update Password' }}</h3>
                <p style="color: var(--text-muted); font-size: 12px; margin-top: 4px;">{{ app()->getLocale() == 'ar' ? 'تأكد من استخدام كلمة مرور قوية وآمنة' : 'Ensure your account is using a long, random password to stay secure.' }}</p>
            </div>
        </div>
        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">{{ app()->getLocale() == 'ar' ? 'كلمة المرور الحالية' : 'Current Password' }}</label>
                <input type="password" name="current_password" class="form-control">
                @error('current_password', 'updatePassword')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() == 'ar' ? 'كلمة المرور الجديدة' : 'New Password' }}</label>
                    <input type="password" name="password" class="form-control">
                    @error('password', 'updatePassword')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ app()->getLocale() == 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }}</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ __('messages.save') ?? 'Save' }}</button>
                @if (session('status') === 'password-updated')
                    <span class="badge badge-success">{{ app()->getLocale() == 'ar' ? 'تم الحفظ' : 'Saved' }}</span>
                @endif
            </div>
        </form>
    </div>

</div>
@endsection
