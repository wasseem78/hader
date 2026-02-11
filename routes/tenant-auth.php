<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('tenant.login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Password Reset Routes
    Route::get('forgot-password', [\App\Http\Controllers\Auth\TenantPasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [\App\Http\Controllers\Auth\TenantPasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\TenantNewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [\App\Http\Controllers\Auth\TenantNewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
        
    // Verification Routes
    Route::get('verify-email', function () { return view('auth.verify-email'); })
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});
