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

echo "Starting Tenant Fix (Company Profile Columns)...\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Fixing tenant: {$tenant->subdomain} (DB: {$tenant->db_name})...\n";

    try {
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

        $columns = ['logo', 'address', 'phone', 'website'];
        
        foreach ($columns as $column) {
            if (!Schema::connection('tenant_fix')->hasColumn('companies', $column)) {
                Schema::connection('tenant_fix')->table('companies', function (Blueprint $table) use ($column) {
                    $table->string($column)->nullable();
                });
                echo "  -> Added '$column' column.\n";
            }
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
