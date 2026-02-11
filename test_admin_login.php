<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Admin Login ===\n\n";

$email = 'admin@attendance.local';
$password = 'admin123';

// Get the user
$user = DB::table('users')->where('email', $email)->first();

if (!$user) {
    echo "✗ User not found: $email\n";
    exit(1);
}

echo "✓ User found:\n";
echo "  ID: {$user->id}\n";
echo "  Email: {$user->email}\n";
echo "  Name: {$user->name}\n\n";

// Test password
$passwordCheck = password_verify($password, $user->password);
$bcryptCheck = \Illuminate\Support\Facades\Hash::check($password, $user->password);

echo "Password verification:\n";
echo "  password_verify(): " . ($passwordCheck ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  Hash::check(): " . ($bcryptCheck ? "✓ PASS" : "✗ FAIL") . "\n\n";

if (!$bcryptCheck) {
    echo "Updating password again with bcrypt...\n";
    $newHash = bcrypt($password);
    DB::table('users')->where('email', $email)->update([
        'password' => $newHash
    ]);
    echo "✓ Password updated\n";
    
    // Verify again
    $user = DB::table('users')->where('email', $email)->first();
    $verifyAgain = \Illuminate\Support\Facades\Hash::check($password, $user->password);
    echo "  Verification after update: " . ($verifyAgain ? "✓ PASS" : "✗ FAIL") . "\n\n";
}

// Try authentication through Laravel
try {
    $credentials = ['email' => $email, 'password' => $password];
    
    if (Auth::attempt($credentials)) {
        echo "✓✓✓ Authentication SUCCESSFUL! ✓✓✓\n";
        echo "You can now login with:\n";
        echo "  Email: $email\n";
        echo "  Password: $password\n";
    } else {
        echo "✗ Authentication FAILED\n";
        echo "Please check:\n";
        echo "  1. The user email is correct\n";
        echo "  2. The password is correct\n";
        echo "  3. User account is not suspended\n";
    }
} catch (Exception $e) {
    echo "Error during authentication: " . $e->getMessage() . "\n";
}

echo "\n=== Done ===\n";
