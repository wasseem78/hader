<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing CENTRAL Admin Login ===\n\n";

$email = 'admin@attendance.local';
$password = 'admin123';

// Get the central user
$user = DB::connection('mysql')->table('users')->where('email', $email)->first();

if (!$user) {
    echo "✗ User not found: $email\n";
    exit(1);
}

echo "✓ Central User found:\n";
echo "  ID: {$user->id}\n";
echo "  Email: {$user->email}\n";
echo "  Name: {$user->name}\n\n";

// Test password
$bcryptCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);

echo "Password verification:\n";
echo "  Hash::check(): " . ($bcryptCheck ? "✓ PASS" : "✗ FAIL") . "\n\n";

if (!$bcryptCheck) {
    echo "Updating password...\n";
    DB::connection('mysql')->table('users')->where('email', $email)->update([
        'password' => bcrypt($password)
    ]);
    echo "✓ Password updated\n\n";
    
    // Verify again
    $user = DB::connection('mysql')->table('users')->where('email', $email)->first();
    $verifyAgain = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    echo "  Verification after update: " . ($verifyAgain ? "✓ PASS" : "✗ FAIL") . "\n\n";
}

// Try authentication with central guard
try {
    $credentials = ['email' => $email, 'password' => $password];
    
    if (Auth::guard('central')->attempt($credentials)) {
        echo "✓✓✓ CENTRAL Authentication SUCCESSFUL! ✓✓✓\n\n";
        echo "═══════════════════════════════════════════\n";
        echo "  You can now login with CENTRAL guard:\n";
        echo "  Email: $email\n";
        echo "  Password: $password\n";
        echo "  URL: http://localhost/attendance/public/login\n";
        echo "       (or http://attendance.local/login)\n";
        echo "═══════════════════════════════════════════\n";
    } else {
        echo "✗ Central Authentication FAILED\n";
    }
} catch (Exception $e) {
    echo "Error during central authentication: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
