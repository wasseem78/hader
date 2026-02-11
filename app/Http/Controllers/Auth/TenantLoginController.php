<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Unified Login Controller - Handles tenant resolution during authentication.
 * Works with single domain (no subdomains required).
 */
class TenantLoginController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited($request);

        // First, find which tenant this user belongs to
        $tenantUser = $this->findUserInTenants($request->email);

        if (!$tenantUser) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Configure tenant connection
        $this->configureConnection($tenantUser['tenant']);

        // Now authenticate against tenant database
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();
        
        // Store tenant ID in session for subsequent requests
        session(['tenant_id' => $tenantUser['tenant']->id]);

        RateLimiter::clear($this->throttleKey($request));

        return redirect()->intended('/dashboard');
    }

    /**
     * Find user across all tenant databases.
     */
    protected function findUserInTenants(string $email): ?array
    {
        // Get all active tenants
        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            try {
                // Configure temporary connection
                Config::set('database.connections.tenant_check', [
                    'driver' => 'mysql',
                    'host' => $tenant->db_host,
                    'port' => $tenant->db_port,
                    'database' => $tenant->db_name,
                    'username' => $tenant->db_username,
                    'password' => $tenant->db_password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                ]);

                DB::purge('tenant_check');
                
                // Check if user exists in this tenant's database
                $user = DB::connection('tenant_check')
                    ->table('users')
                    ->where('email', $email)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($user) {
                    return [
                        'tenant' => $tenant,
                        'user' => $user,
                    ];
                }
            } catch (\Exception $e) {
                // Skip tenant if database connection fails
                \Log::warning("Failed to check tenant {$tenant->subdomain}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Configure the tenant database connection.
     */
    protected function configureConnection(Tenant $tenant): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_username,
            'password' => $tenant->db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');

        // Bind tenant to container
        app()->instance('currentTenant', $tenant);

        // Isolate Cache
        Config::set('cache.prefix', 'tenant_' . $tenant->uuid);

        // Isolate Filesystem
        $tenantRoot = storage_path('app/tenants/' . $tenant->uuid);
        if (!is_dir($tenantRoot)) {
            mkdir($tenantRoot, 0755, true);
        }
        Config::set('filesystems.disks.local.root', $tenantRoot);
        Config::set('filesystems.disks.public.root', $tenantRoot . '/public');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }
}
