<?php

use App\Models\Tenant;
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting Tenant Trial Fix...\n";

$tenants = Tenant::all();

foreach ($tenants as $tenant) {
    echo "Fixing tenant: {$tenant->subdomain}...\n";
    $tenant->trial_ends_at = now()->addDays(14);
    $tenant->save();
    echo "  -> Trial extended to: " . $tenant->trial_ends_at . "\n";
}

echo "Done.\n";
