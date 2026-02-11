<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Tenant Fix (Email Verified At)...\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Fixing tenant: {$tenant->subdomain} (DB: {$tenant->db_name})...\n";

    try {
        // Configure Tenant Connection
        Config::set('database.connections.tenant_fix', [
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

        DB::purge('tenant_fix');
        
        // Check and Add Column
        if (Schema::connection('tenant_fix')->hasTable('users')) {
            if (!Schema::connection('tenant_fix')->hasColumn('users', 'email_verified_at')) {
                Schema::connection('tenant_fix')->table('users', function (Blueprint $table) {
                    $table->timestamp('email_verified_at')->nullable()->after('email');
                });
                echo "  -> Added 'email_verified_at' column.\n";
            } else {
                echo "  -> Column 'email_verified_at' already exists.\n";
            }
        } else {
            echo "  -> Table 'users' not found!\n";
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
