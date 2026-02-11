<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Test the TenantLoginController logic
$tenants = Tenant::where('status', 'active')->get();
echo "Active tenants: " . count($tenants) . "\n";

foreach ($tenants as $tenant) {
    echo "\nChecking tenant: {$tenant->subdomain}\n";
    
    try {
        Config::set('database.connections.tenant_check', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_username,
            'password' => $tenant->db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
        
        DB::purge('tenant_check');
        
        $user = DB::connection('tenant_check')
            ->table('users')
            ->where('email', 'wasseem78@gmail.com')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();
        
        if ($user) {
            echo "✅ FOUND! User: {$user->name}\n";
            
            // Test password
            if (Hash::check('12345678', $user->password)) {
                echo "✅ Password VALID!\n";
            } else {
                echo "❌ Password INVALID!\n";
                echo "Hash in DB: " . substr($user->password, 0, 50) . "...\n";
            }
        } else {
            echo "User not found or not active in this tenant\n";
            
            // Check if user exists but not active
            $inactiveUser = DB::connection('tenant_check')
                ->table('users')
                ->where('email', 'wasseem78@gmail.com')
                ->first();
            if ($inactiveUser) {
                echo "  -> User exists but is_active={$inactiveUser->is_active}\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
