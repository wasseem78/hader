<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Blade-based)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('register', function (Request $request, \App\Tenancy\Services\TenantProvisioner $provisioner) {
        $request->validate([
            'company_name' => 'required|string|max:255|unique:tenants,name',
            'subdomain' => 'required|string|max:50|unique:tenants,subdomain|alpha_dash|not_in:www,admin,app',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email', // Check central DB for tenant owner email uniqueness? Or allow multiple tenants per email?
            'password' => 'required|confirmed|min:8',
        ]);

        try {
            // Provision Tenant
            $tenant = $provisioner->provision([
                'name' => $request->company_name,
                'subdomain' => $request->subdomain,
                'email' => $request->email,
                'password' => $request->password,
                'plan_id' => \App\Models\Plan::where('slug', 'free')->first()->id ?? 1,
            ]);

            // Redirect to Tenant Login
            $protocol = $request->secure() ? 'https://' : 'http://';
            $domain = env('APP_DOMAIN', 'localhost');
            $tenantUrl = $protocol . $tenant->subdomain . '.' . $domain . '/login';

            return redirect($tenantUrl . '?registered=true');

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    });

    Route::get('login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
        ])->withInput($request->only('email', 'remember'));
    });
    
    // Accept Invite
    Route::get('invite/accept/{user}', [\App\Http\Controllers\Admin\UserController::class, 'acceptInvite'])
        ->middleware('signed')
        ->name('admin.users.accept-invite');
    Route::post('invite/accept/{user}', [\App\Http\Controllers\Admin\UserController::class, 'storePassword'])
        ->name('admin.users.store-password');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
