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

echo "Starting Tenant Fix (Spatie Permissions)...\n";

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
        
        $tableNames = [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ];

        if (!Schema::connection('tenant_fix')->hasTable($tableNames['permissions'])) {
            Schema::connection('tenant_fix')->create($tableNames['permissions'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
            echo "  -> Created 'permissions' table.\n";
        }

        if (!Schema::connection('tenant_fix')->hasTable($tableNames['roles'])) {
            Schema::connection('tenant_fix')->create($tableNames['roles'], function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
            echo "  -> Created 'roles' table.\n";
        }

        if (!Schema::connection('tenant_fix')->hasTable($tableNames['model_has_permissions'])) {
            Schema::connection('tenant_fix')->create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
                $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->onDelete('cascade');
                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
            });
            echo "  -> Created 'model_has_permissions' table.\n";
        }

        if (!Schema::connection('tenant_fix')->hasTable($tableNames['model_has_roles'])) {
            Schema::connection('tenant_fix')->create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
                $table->foreign('role_id')->references('id')->on($tableNames['roles'])->onDelete('cascade');
                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
            });
            echo "  -> Created 'model_has_roles' table.\n";
        }

        if (!Schema::connection('tenant_fix')->hasTable($tableNames['role_has_permissions'])) {
            Schema::connection('tenant_fix')->create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on($tableNames['permissions'])->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on($tableNames['roles'])->onDelete('cascade');
                $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
            });
            echo "  -> Created 'role_has_permissions' table.\n";
        }

    } catch (\Exception $e) {
        echo "  -> ERROR: " . $e->getMessage() . "\n";
    }
}

echo "Done.\n";
