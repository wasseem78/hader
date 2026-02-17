<?php

// =============================================================================
// Web Routes - Unified Single Domain (No Subdomains)
// =============================================================================

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    return view('app');
})->name('home');

// Language Switcher
Route::get('/lang/{locale}', [LocaleController::class, 'switch'])
    ->name('lang.switch')
    ->where('locale', 'en|ar');

// Stripe Webhook
Route::post('/stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('stripe.webhook');


/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    // Language Switch Route (no auth required)
    Route::get('lang/{locale}', function ($locale) {
        if (in_array($locale, ['ar', 'en'])) {
            session(['locale' => $locale]);
            app()->setLocale($locale);
        }
        return redirect()->back();
    })->name('lang');

    // Guest Routes
    Route::middleware('guest:central')->group(function () {
        Route::get('login', [\App\Http\Controllers\SuperAdmin\Auth\LoginController::class, 'create'])->name('login');
        Route::post('login', [\App\Http\Controllers\SuperAdmin\Auth\LoginController::class, 'store'])->name('login.store');
    });

    // Authenticated Routes
    Route::middleware('auth:central')->group(function () {
        Route::post('logout', [\App\Http\Controllers\SuperAdmin\Auth\LoginController::class, 'destroy'])->name('logout');
        
        Route::get('/', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');

        // Tenants Management
        Route::get('tenants/{tenant}/impersonate', [\App\Http\Controllers\SuperAdmin\TenantController::class, 'impersonate'])->name('tenants.impersonate');
        Route::resource('tenants', \App\Http\Controllers\SuperAdmin\TenantController::class);

        // Plans Management
        Route::resource('plans', \App\Http\Controllers\SuperAdmin\PlanController::class);

        // Orders Management
        Route::get('orders', [\App\Http\Controllers\SuperAdmin\OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [\App\Http\Controllers\SuperAdmin\OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/approve', [\App\Http\Controllers\SuperAdmin\OrderController::class, 'approve'])->name('orders.approve');
        Route::post('orders/{order}/reject', [\App\Http\Controllers\SuperAdmin\OrderController::class, 'reject'])->name('orders.reject');

        // System Settings
        Route::get('/system', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'index'])
            ->name('system.index');
    });
});


/*
|--------------------------------------------------------------------------
| Tenant Registration (Public)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    // Send verification code to email
    Route::post('register/send-code', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $email = strtolower(trim($request->email));

        // Check if email already registered
        $exists = \App\Models\Tenant::where('email', $email)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => __('messages.email_already_registered'),
            ], 422);
        }

        // Rate limit: max 5 codes per email per 5 minutes
        $rateLimitKey = 'verify-code-send:' . $email;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => __('messages.too_many_code_requests', ['seconds' => $seconds]),
            ], 429);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in cache for 10 minutes
        \Illuminate\Support\Facades\Cache::put('email_verify:' . $email, $code, now()->addMinutes(10));
        \Illuminate\Support\Facades\Cache::put('email_verify_attempts:' . $email, 0, now()->addMinutes(10));

        // Send email
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Your verification code is: {$code}\n\nThis code expires in 10 minutes.\n\nIf you did not request this, please ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Uhdor - Email Verification Code');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send verification email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.code_send_failed'),
            ], 500);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 300);

        return response()->json([
            'success' => true,
            'message' => __('messages.code_sent_success'),
        ]);
    })->name('register.send-code');

    // Verify the code
    Route::post('register/verify-code', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $code = $request->code;

        // Check attempt limit (max 10 wrong attempts)
        $attempts = \Illuminate\Support\Facades\Cache::get('email_verify_attempts:' . $email, 0);
        if ($attempts >= 10) {
            \Illuminate\Support\Facades\Cache::forget('email_verify:' . $email);
            \Illuminate\Support\Facades\Cache::forget('email_verify_attempts:' . $email);
            return response()->json([
                'success' => false,
                'message' => __('messages.too_many_verify_attempts'),
            ], 429);
        }

        $storedCode = \Illuminate\Support\Facades\Cache::get('email_verify:' . $email);

        if (!$storedCode) {
            return response()->json([
                'success' => false,
                'message' => __('messages.code_expired'),
            ], 422);
        }

        if ($storedCode !== $code) {
            \Illuminate\Support\Facades\Cache::increment('email_verify_attempts:' . $email);
            return response()->json([
                'success' => false,
                'message' => __('messages.code_invalid'),
            ], 422);
        }

        // Code is correct — generate a verification token
        $token = bin2hex(random_bytes(32));
        \Illuminate\Support\Facades\Cache::put('email_verified:' . $email, $token, now()->addMinutes(30));

        // Clean up
        \Illuminate\Support\Facades\Cache::forget('email_verify:' . $email);
        \Illuminate\Support\Facades\Cache::forget('email_verify_attempts:' . $email);

        return response()->json([
            'success' => true,
            'message' => __('messages.email_verified_success'),
            'token' => $token,
        ]);
    })->name('register.verify-code');

    Route::post('register', function (\Illuminate\Http\Request $request, \App\Tenancy\Services\TenantProvisioner $provisioner) {
        $request->validate([
            'company_name' => 'required|string|max:255|unique:tenants,name',
            'subdomain' => 'required|string|max:50|alpha_dash|not_in:www,admin,app',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email',
            'password' => 'required|confirmed|min:8',
            'email_verification_token' => 'required|string',
        ]);

        // Verify the email verification token
        $email = strtolower(trim($request->email));
        $storedToken = \Illuminate\Support\Facades\Cache::get('email_verified:' . $email);

        if (!$storedToken || $storedToken !== $request->email_verification_token) {
            return back()->withErrors(['email' => __('messages.email_not_verified')])->withInput();
        }

        try {
            // Provision Tenant
            $tenant = $provisioner->provision([
                'name' => $request->company_name,
                'subdomain' => $request->subdomain,
                'email' => $request->email,
                'password' => $request->password,
                'plan_id' => \App\Models\Plan::where('slug', 'free')->first()->id ?? 1,
            ]);

            // Clean up verification token
            \Illuminate\Support\Facades\Cache::forget('email_verified:' . $email);

            // Redirect to login page
            return redirect('/login')->with('success', __('Registration successful! Please login.'));

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    });
});


/*
|--------------------------------------------------------------------------
| Tenant Authentication (Login, Logout, Password Reset)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [\App\Http\Controllers\Auth\TenantLoginController::class, 'create'])
        ->name('login');

    Route::post('login', [\App\Http\Controllers\Auth\TenantLoginController::class, 'store']);

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
    Route::post('logout', [\App\Http\Controllers\Auth\TenantLoginController::class, 'destroy'])
        ->name('logout');
        
    // Verification Routes
    Route::get('verify-email', function () { return view('auth.verify-email'); })
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', [\App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});


/*
|--------------------------------------------------------------------------
| Authenticated Tenant App Routes
|--------------------------------------------------------------------------
*/

// Tenant Assets (logos, etc.) - Must be accessible for authenticated users
Route::get('/tenant-storage/{path}', [\App\Http\Controllers\TenantAssetController::class, 'show'])
    ->where('path', '.*')
    ->name('tenant.storage')
    ->middleware('auth');

Route::middleware(['auth', 'verified', 'plan.limits'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // Branches Management
    Route::resource('branches', \App\Http\Controllers\BranchController::class);

    // Departments Management
    Route::resource('departments', DepartmentController::class);
    Route::get('/departments-by-branch', [DepartmentController::class, 'getByBranch'])->name('departments.by-branch');
    Route::post('/departments/{department}/move-employees', [DepartmentController::class, 'moveEmployees'])->name('departments.move-employees');
    Route::post('/departments/reorder', [DepartmentController::class, 'reorder'])->name('departments.reorder');

    // Devices Management
    Route::resource('devices', DeviceController::class);
    Route::post('/devices/{device}/test', [DeviceController::class, 'testConnection'])
        ->name('devices.test');
    Route::post('/devices/{device}/sync', [DeviceController::class, 'syncLogs'])
        ->name('devices.sync');
    Route::get('/devices/{device}/info', [DeviceController::class, 'getDeviceInfo'])
        ->name('devices.info');
    Route::get('/devices/{device}/users', [DeviceController::class, 'getDeviceUsers'])
        ->name('devices.users');
    Route::post('/devices/{device}/push-command', [DeviceController::class, 'sendPushCommand'])
        ->name('devices.push-command');
    Route::post('/devices/{device}/sync-users', [DeviceController::class, 'syncDeviceUsers'])
        ->name('devices.sync-users');
    Route::get('/devices/{device}/sync-users-status', [DeviceController::class, 'getSyncUsersStatus'])
        ->name('devices.sync-users-status');

    // Employees Management
    Route::resource('employees', EmployeeController::class);
    Route::post('/employees/{employee}/sync-to-device', [EmployeeController::class, 'syncToDevice'])
        ->name('employees.sync-to-device');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/timeline', [AttendanceController::class, 'timeline'])
        ->name('attendance.timeline');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])
        ->name('attendance.export');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/daily', [ReportController::class, 'daily'])->name('reports.daily');
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/employee/{employee}', [ReportController::class, 'employee'])
        ->name('reports.employee');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/employee/{employee}', [AnalyticsController::class, 'employee'])->name('analytics.employee');
    Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');
    Route::get('/analytics/chart-data', [AnalyticsController::class, 'chartData'])->name('analytics.chart-data');

    // Attendance Report (Advanced Filterable)
    Route::get('/attendance-report', [AttendanceReportController::class, 'index'])->name('attendance-report.index');
    Route::get('/attendance-report/daily', [AttendanceReportController::class, 'daily'])->name('attendance-report.daily');
    Route::get('/attendance-report/export', [AttendanceReportController::class, 'export'])->name('attendance-report.export');

    // Billing
    Route::get('/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/invoices', [\App\Http\Controllers\BillingController::class, 'invoices'])->name('billing.invoices');
    Route::get('/billing/checkout/{plan}', [\App\Http\Controllers\BillingController::class, 'showCheckout'])->name('billing.checkout');
    Route::get('/billing/renew-checkout', [\App\Http\Controllers\BillingController::class, 'showRenewCheckout'])->name('billing.renew-checkout');
    Route::get('/billing/orders', [\App\Http\Controllers\BillingController::class, 'orders'])->name('billing.orders');
    Route::get('/billing/orders/{order}', [\App\Http\Controllers\BillingController::class, 'showOrder'])->name('billing.order.show');
    Route::post('/billing/orders/{order}/cancel', [\App\Http\Controllers\BillingController::class, 'cancelOrder'])->name('billing.order.cancel');
    Route::post('/billing/subscribe/{plan}', [\App\Http\Controllers\BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::post('/billing/renew', [\App\Http\Controllers\BillingController::class, 'renew'])->name('billing.renew');
    Route::post('/billing/cancel-subscription', [\App\Http\Controllers\BillingController::class, 'cancelSubscription'])->name('billing.cancel-subscription');
    
    // Admin Routes (Tenant Context)
    Route::prefix('admin')->name('admin.')->group(function () {
        // Shifts Management
        Route::resource('shifts', \App\Http\Controllers\Admin\ShiftController::class);

        // User Management
        Route::get('users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/invite', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.invite');
        Route::post('users/invite', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        
        // Time Off Requests
        Route::resource('time-off', \App\Http\Controllers\Admin\TimeOffController::class);
        Route::post('/time-off/{timeOff}/approve', [\App\Http\Controllers\Admin\TimeOffController::class, 'approve'])
            ->name('time-off.approve');
        Route::post('/time-off/{timeOff}/reject', [\App\Http\Controllers\Admin\TimeOffController::class, 'reject'])
            ->name('time-off.reject');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
            ->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])
            ->name('settings.update');
    });
});


/*
|--------------------------------------------------------------------------
| Magic Login (Impersonation from Super Admin)
|--------------------------------------------------------------------------
*/
Route::get('/magic-login', \App\Http\Controllers\Auth\MagicLoginController::class)->name('magic.login');
