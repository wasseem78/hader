<?php

namespace App\Console\Commands;

use App\Tenancy\Services\TenantProvisioner;
use Illuminate\Console\Command;

class TenantProvision extends Command
{
    protected $signature = 'tenant:provision {name} {subdomain} {email}';
    protected $description = 'Provision a new tenant with isolated database';

    public function handle(TenantProvisioner $provisioner)
    {
        $this->info("Provisioning tenant: " . $this->argument('name'));

        try {
            $tenant = $provisioner->provision([
                'name' => $this->argument('name'),
                'subdomain' => $this->argument('subdomain'),
                'email' => $this->argument('email'),
            ]);

            $this->info("Tenant provisioned successfully!");
            $this->info("Database: " . $tenant->db_name);
            $this->info("URL: http://" . $tenant->subdomain . ".localhost:8000"); // Adjust for env

        } catch (\Exception $e) {
            $this->error("Provisioning failed: " . $e->getMessage());
        }
    }
}
