<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

// The tenant model is App\Models\Company according to config/tenancy.php
$tenants = Company::all();

foreach ($tenants as $tenant) {
    echo "Processing Tenant: " . $tenant->id . " (" . $tenant->name . ")\n";
    
    // Switch to tenant
    tenancy()->initialize($tenant);
    
    $database = DB::connection()->getDatabaseName();
    echo " - Database: " . $database . "\n";

    $queries = [
        "ALTER TABLE `devices` ADD COLUMN IF NOT EXISTS `total_users` INT DEFAULT 0 AFTER `last_error` ",
        "ALTER TABLE `devices` ADD COLUMN IF NOT EXISTS `total_fingerprints` INT DEFAULT 0 AFTER `total_users` ",
        "ALTER TABLE `devices` ADD COLUMN IF NOT EXISTS `total_logs` INT DEFAULT 0 AFTER `total_fingerprints` "
    ];

    foreach ($queries as $query) {
        try {
            DB::statement($query);
            echo " - Query executed: " . substr($query, 0, 50) . "...\n";
        } catch (\Exception $e) {
            echo " - Error executing query: " . $e->getMessage() . "\n";
        }
    }
    
    tenancy()->end();
}

echo "Finished.\n";
