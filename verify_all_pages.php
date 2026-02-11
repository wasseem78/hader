<?php

require __DIR__.'/vendor/autoload.php';

$baseUrl = 'http://127.0.0.1:8000';
$hostHeader = 'Host: yar.localhost';
$loginUrl = $baseUrl . '/login';
$cookieFile = 'cookie_audit.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

// Routes to test
$routes = [
    '/dashboard' => 'Dashboard',
    '/devices' => 'Devices List',
    '/devices/create' => 'Add Device',
    '/employees' => 'Employees List',
    '/employees/create' => 'Add Employee',
    '/attendance' => 'Attendance List',
    '/reports' => 'Reports',
    '/admin/shifts' => 'Shifts Management',
    '/admin/shifts/create' => 'Add Shift',
    '/billing' => 'Billing',
    '/admin/users' => 'User Management',
    '/admin/users' => 'User Management',
    '/admin/settings' => 'Settings',
    '/profile' => 'User Profile',
];

echo "=== Starting Comprehensive System Audit ===\n";

// 1. Login
echo "[Login] Authenticating as wasseem78@gmail.com...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_HTTPHEADER, [$hostHeader]);
$response = curl_exec($ch);

if (preg_match('/name="csrf-token" content="(.*?)"/', $response, $matches)) {
    $csrfToken = $matches[1];
} else {
    echo "[FATAL] Could not get CSRF token.\n";
    echo "Response Code: " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
    echo "Response Body Snippet: " . substr($response, 0, 500) . "\n";
    die();
}

curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $csrfToken,
    'email' => 'wasseem78@gmail.com',
    'password' => '12345678',
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [$hostHeader]);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode != 302) {
    die("[FATAL] Login failed (HTTP $httpCode). Check credentials or server log.\n");
}
echo "[Login] Success. Session established.\n\n";

// 2. Crawl Pages
$failed = 0;
foreach ($routes as $path => $name) {
    echo "[$name] Checking $path... ";
    
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $path);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [$hostHeader]);
    $pageResponse = curl_exec($ch);
    $pageCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($pageCode == 200) {
        echo "OK (200)\n";
    } else {
        $failed++;
        echo "FAILED ($pageCode)\n";
        // Extract error message if possible (Laravel error page)
        if ($pageCode == 500) {
            if (preg_match('/<span class="text-gray-500 text-xl truncate block">(.*?)<\/span>/', $pageResponse, $errMatches)) {
                 echo "    -> Error: " . strip_tags($errMatches[1]) . "\n";
            } elseif (preg_match('/"message": "(.*?)"/', $pageResponse, $jsonMatches)) {
                 echo "    -> JSON Error: " . $jsonMatches[1] . "\n";
            } else {
                 // Fallback: print first 200 chars of body
                 echo "    -> Response: " . substr(strip_tags($pageResponse), 0, 200) . "...\n";
            }
        }
    }
}

curl_close($ch);
// unlink($cookieFile);

echo "\n=== Audit Complete ===\n";
echo "Failures: $failed / " . count($routes) . "\n";
if ($failed > 0) {
    exit(1);
}
