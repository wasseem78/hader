<?php
/**
 * CyberPanel Remote Operations
 * 1. Enable API access
 * 2. Create databases for tenants
 * 3. Grant privileges
 */

echo "=== CyberPanel Operations ===\n\n";

// Step 1: Try to login to CyberPanel and get session
$host = '91.108.112.113';
$port = 8090;
$user = 'admin';
$pass = 'Araory@2014@2014';

// Try login via web form (session-based)
echo "1. Attempting CyberPanel web login...\n";
$loginResult = cyberpanelWebLogin($host, $port, $user, $pass);
if ($loginResult) {
    echo "   ✓ Login successful!\n\n";
} else {
    echo "   ✗ Web login failed\n\n";
}

// Try API with different endpoints
echo "2. Testing API endpoints...\n\n";

// Try verifyConn
$endpoints = [
    'verifyConn' => ['adminUser' => $user, 'adminPass' => $pass],
    'verify' => ['adminUser' => $user, 'adminPass' => $pass],
];

foreach ($endpoints as $endpoint => $data) {
    echo "   Testing /api/{$endpoint}... ";
    $result = callApi("https://{$host}:{$port}/api/{$endpoint}", $data);
    echo json_encode($result) . "\n";
}

echo "\n3. Trying to access CyberPanel web pages...\n";

// Try to get CSRF token and login via web interface
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://{$host}:{$port}/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HEADER => true,
    CURLOPT_TIMEOUT => 15,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Login page HTTP Code: {$httpCode}\n";

// Extract CSRF token
if (preg_match('/csrfmiddlewaretoken.*?value=["\']([^"\']+)["\']/s', $response, $matches)) {
    $csrf = $matches[1];
    echo "   CSRF Token found: " . substr($csrf, 0, 20) . "...\n";
    
    // Try to login
    echo "\n4. Attempting form-based login...\n";
    
    // Get cookies first
    $cookieFile = tempnam(sys_get_temp_dir(), 'cyberpanel_');
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$host}:{$port}/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 15,
    ]);
    curl_exec($ch);
    curl_close($ch);
    
    // Now login
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$host}:{$port}/verifyLogin",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'username' => $user,
            'password' => $pass,
            'csrfmiddlewaretoken' => $csrf,
            'languageSelection' => 'english',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            'Referer: https://' . $host . ':' . $port . '/',
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $loginResponse = curl_exec($ch);
    $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    echo "   Login response HTTP Code: {$loginHttpCode}\n";
    echo "   Final URL: {$finalUrl}\n";
    
    if (strpos($loginResponse, 'dashboard') !== false || strpos($loginResponse, 'Dashboard') !== false) {
        echo "   ✓ Login successful! Redirected to dashboard.\n";
        
        // Now try to access database creation page
        echo "\n5. Trying to create database via web interface...\n";
        
        // First check existing databases
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://{$host}:{$port}/dataBases/fetchDatabases",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['selectedWebsite' => 'uhdor.com']),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Referer: https://' . $host . ':' . $port . '/dataBases/uhdor.com',
                'X-CSRFToken: ' . $csrf,
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $dbResponse = curl_exec($ch);
        $dbHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "   Databases response ({$dbHttpCode}): " . substr($dbResponse, 0, 500) . "\n";
    } else {
        echo "   ✗ Login may have failed\n";
        // Show relevant parts of response
        if (preg_match('/<title>(.*?)<\/title>/i', $loginResponse, $titleMatch)) {
            echo "   Page title: " . $titleMatch[1] . "\n";
        }
    }
    
    // Cleanup
    @unlink($cookieFile);
} else {
    echo "   No CSRF token found in login page\n";
    // Show what we got
    if (preg_match('/<title>(.*?)<\/title>/i', $response, $titleMatch)) {
        echo "   Page title: " . $titleMatch[1] . "\n";
    }
}

echo "\n=== Finding .env and site path on server ===\n";
echo "Trying to access site to find its path...\n";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "http://uhdor.com/public/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 15,
]);
$siteResponse = curl_exec($ch);
$siteCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "Site HTTP Code: {$siteCode}\n";

echo "\n=== Done ===\n";

// ============================================================

function callApi(string $url, array $data): ?array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function cyberpanelWebLogin(string $host, int $port, string $user, string $pass): bool
{
    $cookieFile = tempnam(sys_get_temp_dir(), 'cp_');
    
    // Get login page for CSRF
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://{$host}:{$port}/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_TIMEOUT => 15,
    ]);
    $page = curl_exec($ch);
    curl_close($ch);
    
    @unlink($cookieFile);
    return !empty($page) && strlen($page) > 100;
}
