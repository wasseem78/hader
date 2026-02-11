<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Tenant Data Fix (UUIDs & Limits)...\n";

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

        // 1. Fix User UUIDs
        $users = DB::connection('tenant_fix')->table('users')->whereNull('uuid')->orWhere('uuid', '')->get();
        $fixedUsers = 0;
        foreach ($users as $user) {
            DB::connection('tenant_fix')->table('users')->where('id', $user->id)->update(['uuid' => (string) Str::uuid()]);
            $fixedUsers++;
        }
        if ($fixedUsers > 0) {
            echo "  -> Generated UUIDs for $fixedUsers users.\n";
        }

        // 2. Fix Company Limits (Add Columns & Update Values)
        if (!Schema::connection('tenant_fix')->hasColumn('companies', 'max_devices')) {
            Schema::connection('tenant_fix')->table('companies', function (Blueprint $table) {
                $table->integer('max_devices')->default(5);
            });
            echo "  -> Added 'max_devices' column.\n";
        }
        if (!Schema::connection('tenant_fix')->hasColumn('companies', 'max_employees')) {
            Schema::connection('tenant_fix')->table('companies', function (Blueprint $table) {
                $table->integer('max_employees')->default(10);
            });
            echo "  -> Added 'max_employees' column.\n";
        }
        if (!Schema::connection('tenant_fix')->hasColumn('companies', 'max_users')) {
            Schema::connection('tenant_fix')->table('companies', function (Blueprint $table) {
                $table->integer('max_users')->default(2);
            });
            echo "  -> Added 'max_users' column.\n";
        }

        $companies = DB::connection('tenant_fix')->table('companies')->get();
        foreach ($companies as $company) {
            $updates = [];
            if (empty($company->max_devices) || $company->max_devices == 0) {
                $updates['max_devices'] = 5;
            }
            if (empty($company->max_employees) || $company->max_employees == 0) {
                $updates['max_employees'] = 10;
            }
            
            if (!empty($updates)) {
                DB::connection('tenant_fix')->table('companies')->where('id', $company->id)->update($updates);
                echo "  -> Updated limits for company {$company->name}.\n";
            }
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
