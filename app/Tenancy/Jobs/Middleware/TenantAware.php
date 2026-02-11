<?php

namespace App\Tenancy\Jobs\Middleware;

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantAware
{
    protected $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function handle($job, $next)
    {
        $tenant = Tenant::find($this->tenantId);

        if ($tenant) {
            $this->configureConnection($tenant);
        }

        return $next($job);
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
        DB::setDefaultConnection('tenant');
    }
}
