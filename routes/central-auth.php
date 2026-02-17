<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

Route::middleware('guest:central')->group(function () {
    Route::get('register', function () {
        return view('auth.register');
    })->name('register');

    // Send verification code to email
    Route::post('register/send-code', function (Request $request) {
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
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => __('messages.too_many_code_requests', ['seconds' => $seconds]),
            ], 429);
        }

        // Generate 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store in cache for 10 minutes
        Cache::put('email_verify:' . $email, $code, now()->addMinutes(10));
        Cache::put('email_verify_attempts:' . $email, 0, now()->addMinutes(10));

        // Send email
        try {
            Mail::raw(
                "Your verification code is: {$code}\n\nThis code expires in 10 minutes.\n\nIf you did not request this, please ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)
                            ->subject('Uhdor - Email Verification Code');
                }
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => __('messages.code_send_failed'),
            ], 500);
        }

        RateLimiter::hit($rateLimitKey, 300);

        return response()->json([
            'success' => true,
            'message' => __('messages.code_sent_success'),
        ]);
    })->name('register.send-code');

    // Verify the code
    Route::post('register/verify-code', function (Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'code' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->email));
        $code = $request->code;

        // Check attempt limit (max 10 wrong attempts)
        $attempts = Cache::get('email_verify_attempts:' . $email, 0);
        if ($attempts >= 10) {
            Cache::forget('email_verify:' . $email);
            Cache::forget('email_verify_attempts:' . $email);
            return response()->json([
                'success' => false,
                'message' => __('messages.too_many_verify_attempts'),
            ], 429);
        }

        $storedCode = Cache::get('email_verify:' . $email);

        if (!$storedCode) {
            return response()->json([
                'success' => false,
                'message' => __('messages.code_expired'),
            ], 422);
        }

        if ($storedCode !== $code) {
            Cache::increment('email_verify_attempts:' . $email);
            return response()->json([
                'success' => false,
                'message' => __('messages.code_invalid'),
            ], 422);
        }

        // Code is correct — generate a verification token
        $token = bin2hex(random_bytes(32));
        Cache::put('email_verified:' . $email, $token, now()->addMinutes(30));

        // Clean up
        Cache::forget('email_verify:' . $email);
        Cache::forget('email_verify_attempts:' . $email);

        return response()->json([
            'success' => true,
            'message' => __('messages.email_verified_success'),
            'token' => $token,
        ]);
    })->name('register.verify-code');

    Route::post('register', function (Request $request, \App\Tenancy\Services\TenantProvisioner $provisioner) {
        $request->validate([
            'company_name' => 'required|string|max:255|unique:tenants,name',
            'subdomain' => 'required|string|max:50|unique:tenants,subdomain|alpha_dash|not_in:www,admin,app',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email',
            'password' => 'required|confirmed|min:8',
            'email_verification_token' => 'required|string',
        ]);

        // Verify the email verification token
        $email = strtolower(trim($request->email));
        $storedToken = Cache::get('email_verified:' . $email);

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
            Cache::forget('email_verified:' . $email);

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
