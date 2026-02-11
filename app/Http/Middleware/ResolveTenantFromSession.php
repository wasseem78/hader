<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Middleware to resolve tenant from session BEFORE authentication check.
 * No subdomain required - tenant is identified from session set during login.
 */
class ResolveTenantFromSession
{
    public function handle(Request $request, Closure $next)
    {
        // Skip tenant resolution for Super Admin routes - they use central database
        if ($request->is('super-admin/*') || $request->is('super-admin')) {
            return $next($request);
        }

        // Get tenant_id from session FIRST (before any auth check)
        $tenantId = session('tenant_id');
        
        if (!$tenantId) {
            // No tenant in session — flush any stale auth data to prevent
            // "No database selected" errors when the web guard tries to
            // load a User model on the unconfigured tenant connection.
            $this->flushStaleAuth($request);
            return $next($request);
        }

        // Load tenant from CENTRAL database
        $tenant = Tenant::on('mysql')->find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            $this->flushStaleAuth($request);
            return $next($request);
        }

        if ($tenant->status !== 'active') {
            session()->forget('tenant_id');
            $this->flushStaleAuth($request);
            return redirect('/login')->withErrors(['email' => 'Your company account is not active.']);
        }

        // Configure tenant connection BEFORE any auth check
        $this->configureConnection($tenant);

        // Bind tenant to container
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }

    protected function configureConnection(Tenant $tenant)
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
        
        // Set default connection to tenant
        DB::setDefaultConnection('tenant');

        // Isolate Cache
        Config::set('cache.prefix', 'tenant_' . $tenant->uuid);
        
        // Isolate Filesystem
        $tenantRoot = storage_path('app/tenants/' . $tenant->uuid);
        if (!is_dir($tenantRoot)) {
            mkdir($tenantRoot, 0755, true);
        }
        Config::set('filesystems.disks.local.root', $tenantRoot);
        Config::set('filesystems.disks.public.root', $tenantRoot . '/public');
        Config::set('filesystems.disks.public.url', env('APP_URL') . '/storage/' . $tenant->uuid);
    }

    /**
     * Remove stale web-guard auth session data so that later middleware
     * (e.g. RedirectIfAuthenticated) will not try to load a User model
     * on the unconfigured tenant connection.
     */
    protected function flushStaleAuth(Request $request): void
    {
        // The session key Laravel uses for the web guard
        $key = 'login_web_' . sha1('Illuminate\Auth\SessionGuard');

        if ($request->session()->has($key)) {
            $request->session()->forget($key);
            $request->session()->regenerateToken();
        }
    }
}
