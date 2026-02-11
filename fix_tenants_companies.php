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

echo "Starting Tenant Fix (Companies SoftDeletes)...\n";

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
        if (Schema::connection('tenant_fix')->hasTable('companies')) {
            if (!Schema::connection('tenant_fix')->hasColumn('companies', 'deleted_at')) {
                Schema::connection('tenant_fix')->table('companies', function (Blueprint $table) {
                    $table->softDeletes();
                });
                echo "  -> Added 'deleted_at' column to companies.\n";
            } else {
                echo "  -> Column 'deleted_at' already exists in companies.\n";
            }
        } else {
            echo "  -> Table 'companies' not found!\n";
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
