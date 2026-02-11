<?php

namespace App\Tenancy\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ResolveTenantConnection
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Identify Tenant
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // Skip for central domains
        $centralDomains = explode(',', env('TENANCY_CENTRAL_DOMAINS', 'localhost,attendance.local'));
        if (in_array($host, $centralDomains)) {
            return $next($request);
        }

        // Also skip if subdomain is www or app (just in case)
        if ($subdomain === 'www' || $subdomain === 'app') {
            return $next($request);
        }

        // 2. Load Tenant Record
        $tenant = Tenant::where('subdomain', $subdomain)->first();

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        if ($tenant->status !== 'active') {
            abort(403, 'Tenant is not active.');
        }

        // 3. Configure Connection
        $this->configureConnection($tenant);

        // 4. Bind Tenant to Container
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

        // 5. Isolate Cache
        Config::set('cache.prefix', 'tenant_' . $tenant->uuid);
        app('cache')->forgetDriver(Config::get('cache.default'));

        // 6. Isolate Filesystem
        $tenantRoot = storage_path('app/tenants/' . $tenant->uuid);
        if (!is_dir($tenantRoot)) {
            mkdir($tenantRoot, 0755, true);
        }
        Config::set('filesystems.disks.local.root', $tenantRoot);
        Config::set('filesystems.disks.public.root', $tenantRoot . '/public');
        Config::set('filesystems.disks.public.url', env('APP_URL') . '/storage/' . $tenant->uuid);
    }
}
