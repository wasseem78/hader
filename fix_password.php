<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// Find tenant
$tenant = Tenant::where('subdomain', 'yar')->first();

if (!$tenant) {
    echo "Tenant 'yar' not found!\n";
    
    // List all tenants
    echo "\nAll tenants:\n";
    foreach (Tenant::all() as $t) {
        echo "- {$t->subdomain}: {$t->name} ({$t->email})\n";
    }
    exit(1);
}

echo "Tenant: {$tenant->name}\n";
echo "DB: {$tenant->db_name}\n";

// Configure tenant connection
Config::set('database.connections.tenant', [
    'driver' => 'mysql',
    'host' => $tenant->db_host,
    'port' => $tenant->db_port,
    'database' => $tenant->db_name,
    'username' => $tenant->db_username,
    'password' => $tenant->db_password,
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
]);

DB::purge('tenant');
DB::reconnect('tenant');

// Find user
$user = DB::connection('tenant')->table('users')->where('email', 'wasseem78@gmail.com')->first();

if (!$user) {
    echo "\nUser wasseem78@gmail.com NOT found!\n";
    
    // List all users
    echo "\nAll users in tenant DB:\n";
    $users = DB::connection('tenant')->table('users')->get();
    foreach ($users as $u) {
        echo "- {$u->email}: {$u->name} (active: " . ($u->is_active ? 'yes' : 'no') . ")\n";
    }
    exit(1);
}

echo "\nUser found: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Active: " . ($user->is_active ? 'Yes' : 'No') . "\n";
echo "Company ID: {$user->company_id}\n";

// Update password
$newPassword = bcrypt('12345678');
DB::connection('tenant')->table('users')
    ->where('email', 'wasseem78@gmail.com')
    ->update([
        'password' => $newPassword,
        'is_active' => true,
    ]);

echo "\n✅ Password updated to: 12345678\n";
echo "✅ User activated!\n";
