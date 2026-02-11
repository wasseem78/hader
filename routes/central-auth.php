<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::middleware('guest:central')->group(function () {
    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('register', function (Request $request, \App\Tenancy\Services\TenantProvisioner $provisioner) {
        $request->validate([
            'company_name' => 'required|string|max:255|unique:tenants,name',
            'subdomain' => 'required|string|max:50|unique:tenants,subdomain|alpha_dash|not_in:www,admin,app',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email',
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
            $port = $request->getPort();
            $portSuffix = ($port == 80 || $port == 443) ? '' : ":$port";
            
            $tenantUrl = $protocol . $tenant->subdomain . '.' . $domain . $portSuffix . '/login';

            return redirect($tenantUrl . '?registered=true');

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Registration failed: ' . $e->getMessage()])->withInput();
        }
    });
});
