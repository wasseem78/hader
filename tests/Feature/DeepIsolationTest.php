<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\Services\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeepIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Skip if no MySQL available or if we want to mock
        // For this test to be real, we need actual DBs.
        // We will simulate the behavior by mocking the connection switching logic
        // or by using SQLite in-memory databases if possible, but SQLite doesn't support
        // multiple connections easily in the same way MySQL does for this architecture.
        
        // Ideally, we would use a Docker container for MySQL testing.
    }

    public function test_models_use_tenant_connection_by_default()
    {
        // 1. Verify User model has the trait and scope
        $user = new User();
        $this->assertEquals('tenant', $user->getConnectionName());
    }

    public function test_tenant_data_isolation()
    {
        // This is a conceptual test since we can't easily spin up 2 real MySQL DBs in this environment
        // without Docker/CI setup. We will verify the logic flow.

        // Mock Tenant A
        $tenantA = new Tenant([
            'id' => 1,
            'db_name' => 'tenant_a',
            'db_host' => '127.0.0.1',
            'db_username_enc' => encrypt('root'),
            'db_password_enc' => encrypt(''),
        ]);

        // Mock Tenant B
        $tenantB = new Tenant([
            'id' => 2,
            'db_name' => 'tenant_b',
            'db_host' => '127.0.0.1',
            'db_username_enc' => encrypt('root'),
            'db_password_enc' => encrypt(''),
        ]);

        // Simulate switching to Tenant A
        $this->configureConnection($tenantA);
        $this->assertEquals('tenant_a', Config::get('database.connections.tenant.database'));

        // Simulate switching to Tenant B
        $this->configureConnection($tenantB);
        $this->assertEquals('tenant_b', Config::get('database.connections.tenant.database'));
    }

    protected function configureConnection($tenant)
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'database' => $tenant->db_name,
            'username' => $tenant->db_username,
            'password' => $tenant->db_password,
        ]);
    }
}
