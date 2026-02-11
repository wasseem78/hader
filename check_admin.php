<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Checking admin user ===\n\n";

// Check in default database
try {
    $users = DB::table('users')->select('email', 'name', 'id')->get();
    echo "Users in default database (attendance):\n";
    foreach ($users as $user) {
        echo "- Email: {$user->email}, Name: {$user->name}, ID: {$user->id}\n";
    }
    echo "\nTotal users: " . count($users) . "\n\n";
    
    // Try to find admin@attendance.local
    $admin = DB::table('users')->where('email', 'admin@attendance.local')->first();
    if ($admin) {
        echo "✓ Found admin@attendance.local:\n";
        echo "  ID: {$admin->id}\n";
        echo "  Name: {$admin->name}\n";
        echo "  Email: {$admin->email}\n\n";
        
        // Update password
        DB::table('users')->where('email', 'admin@attendance.local')->update([
            'password' => bcrypt('admin123')
        ]);
        echo "✓ Password updated to: admin123\n\n";
    } else {
        echo "✗ admin@attendance.local NOT FOUND in default database\n\n";
    }
} catch (Exception $e) {
    echo "Error with default database: " . $e->getMessage() . "\n\n";
}

// Check in central database
try {
    echo "Checking central database (attendance_central):\n";
    $centralUsers = DB::connection('central')->table('users')->select('email', 'name', 'id')->get();
    echo "Users in central database:\n";
    foreach ($centralUsers as $user) {
        echo "- Email: {$user->email}, Name: {$user->name}, ID: {$user->id}\n";
    }
    echo "\nTotal users: " . count($centralUsers) . "\n\n";
    
    // Try to find admin@attendance.local in central
    $adminCentral = DB::connection('central')->table('users')->where('email', 'admin@attendance.local')->first();
    if ($adminCentral) {
        echo "✓ Found admin@attendance.local in CENTRAL database:\n";
        echo "  ID: {$adminCentral->id}\n";
        echo "  Name: {$adminCentral->name}\n";
        echo "  Email: {$adminCentral->email}\n\n";
        
        // Update password
        DB::connection('central')->table('users')->where('email', 'admin@attendance.local')->update([
            'password' => bcrypt('admin123')
        ]);
        echo "✓ Password updated in CENTRAL database to: admin123\n\n";
    }
} catch (Exception $e) {
    echo "Error with central database: " . $e->getMessage() . "\n\n";
}

echo "=== Done ===\n";
