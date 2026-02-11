<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate {tenant_id?} {--fresh} {--seed}';
    protected $description = 'Run migrations for tenants';

    public function handle()
    {
        $query = Tenant::where('status', 'active');
        
        if ($this->argument('tenant_id')) {
            $query->where('id', $this->argument('tenant_id'));
        }

        $tenants = $query->get();

        foreach ($tenants as $tenant) {
            $this->info("Migrating tenant: {$tenant->name} ({$tenant->db_name})");

            $this->configureConnection($tenant);

            $options = [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ];

            if ($this->option('fresh')) {
                Artisan::call('migrate:fresh', $options);
            } else {
                Artisan::call('migrate', $options);
            }

            if ($this->option('seed')) {
                Artisan::call('db:seed', ['--database' => 'tenant', '--force' => true]);
            }
            
            $this->info(Artisan::output());
        }
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
    }
}
