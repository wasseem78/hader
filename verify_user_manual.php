<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Manual Verification...\n";

// Find the latest tenant
$tenant = Tenant::latest()->first();

if (!$tenant) {
    echo "No tenants found.\n";
    exit(1);
}

echo "Using Tenant: {$tenant->subdomain} (DB: {$tenant->db_name})\n";

try {
    // Configure Tenant Connection
    Config::set('database.connections.tenant_manual', [
        'driver' => 'mysql',
        'host' => $tenant->db_host,
        'port' => $tenant->db_port,
        'database' => $tenant->db_name,
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
    ]);

    DB::purge('tenant_manual');
    
    // Find the user
    // We'll just verify ALL users in this tenant for simplicity/guarantee
    $affected = DB::connection('tenant_manual')->table('users')
        ->whereNull('email_verified_at')
        ->update(['email_verified_at' => now()]);

    echo "Verified {$affected} users in tenant '{$tenant->subdomain}'.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "Done.\n";
