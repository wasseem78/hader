<?php
/**
 * CyberPanel Web Session Login & Database Operations
 */

echo "=== CyberPanel Web Session ===\n\n";

$host = '91.108.112.113';
$port = 8090;
$baseUrl = "https://{$host}:{$port}";
$cookieFile = tempnam(sys_get_temp_dir(), 'cyberpanel_session_');

// Step 1: Get login page and extract CSRF token
echo "1. Getting login page...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "{$baseUrl}/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
]);
$loginPage = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: {$httpCode}\n";

// Find CSRF token - try multiple patterns
$csrf = null;
$patterns = [
    '/csrfmiddlewaretoken["\'\s]*value=["\']([^"\']+)["\']/i',
    '/name=["\']csrfmiddlewaretoken["\'].*?value=["\']([^"\']+)["\']/is',
    '/value=["\']([^"\']+)["\'].*?name=["\']csrfmiddlewaretoken["\']/is',
    '/csrftoken[=\s]+([a-zA-Z0-9]+)/i',
];

foreach ($patterns as $pattern) {
    if (preg_match($pattern, $loginPage, $m)) {
        $csrf = $m[1];
        break;
    }
}

// Also try to get from cookie
if (!$csrf && file_exists($cookieFile)) {
    $cookies = file_get_contents($cookieFile);
    if (preg_match('/csrftoken\s+(\S+)/', $cookies, $m)) {
        $csrf = $m[1];
        echo "   Got CSRF from cookie: {$csrf}\n";
    }
}

if (!$csrf) {
    // Try to find any hidden input with a token
    preg_match_all('/<input[^>]*type=["\']hidden["\'][^>]*>/i', $loginPage, $hiddenInputs);
    echo "   Hidden inputs found: " . count($hiddenInputs[0]) . "\n";
    foreach ($hiddenInputs[0] as $input) {
        echo "   - {$input}\n";
    }
    
    // Check if it's a JSON/React-based login
    if (strpos($loginPage, 'React') !== false || strpos($loginPage, 'react') !== false || strpos($loginPage, 'vue') !== false) {
        echo "   Detected SPA-based login page\n";
    }
    
    // Try jQuery/AJAX style login
    echo "\n2. Trying AJAX-based login...\n";
    
    // Read all cookies
    echo "   Cookie file content:\n";
    if (file_exists($cookieFile)) {
        echo "   " . file_get_contents($cookieFile) . "\n";
    }
    
    // Fetch the login page as API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "{$baseUrl}/verifyLogin",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'username' => 'admin',
            'password' => 'Araory@2014@2014',
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest',
            'Referer: ' . $baseUrl . '/',
        ],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ]);
    $ajaxResponse = curl_exec($ch);
    $ajaxCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   AJAX login HTTP Code: {$ajaxCode}\n";
    echo "   Response: " . substr($ajaxResponse, 0, 500) . "\n";
    
    // If 403 (CSRF), try to get the token from cookies and retry
    if ($ajaxCode == 403) {
        $cookies = file_get_contents($cookieFile);
        if (preg_match('/csrftoken\s+(\S+)/', $cookies, $m)) {
            $csrf = $m[1];
            echo "\n   Found CSRF token in cookies: {$csrf}\n";
            echo "   Retrying login with CSRF...\n";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => "{$baseUrl}/verifyLogin",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'username' => 'admin',
                    'password' => 'Araory@2014@2014',
                ]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_COOKIEJAR => $cookieFile,
                CURLOPT_COOKIEFILE => $cookieFile,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Requested-With: XMLHttpRequest',
                    'X-CSRFToken: ' . $csrf,
                    'Referer: ' . $baseUrl . '/',
                ],
                CURLOPT_TIMEOUT => 15,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ]);
            $retryResponse = curl_exec($ch);
            $retryCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "   Retry HTTP Code: {$retryCode}\n";
            echo "   Response: " . substr($retryResponse, 0, 500) . "\n";
            
            if ($retryCode == 200) {
                $result = json_decode($retryResponse, true);
                if (isset($result['loginStatus']) && $result['loginStatus'] == 1) {
                    echo "   ✓ LOGIN SUCCESSFUL!\n";
                    performDatabaseOps($baseUrl, $cookieFile, $csrf);
                } else {
                    echo "   Login result: " . json_encode($result) . "\n";
                }
            }
        }
    }
} else {
    echo "   CSRF Token: {$csrf}\n";
    
    // Login with CSRF
    echo "\n2. Logging in...\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "{$baseUrl}/verifyLogin",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'username' => 'admin',
            'password' => 'Araory@2014@2014',
            'csrfmiddlewaretoken' => $csrf,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => [
            'Referer: ' . $baseUrl . '/',
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $response = curl_exec($ch);
    $loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    echo "   HTTP Code: {$loginCode}, Final URL: {$finalUrl}\n";
    
    if (strpos($response, 'dashboard') !== false || strpos($finalUrl, 'dashboard') !== false) {
        echo "   ✓ LOGIN SUCCESSFUL!\n";
        performDatabaseOps($baseUrl, $cookieFile, $csrf);
    }
}

@unlink($cookieFile);
echo "\n=== Done ===\n";

// ============================================================

function performDatabaseOps(string $baseUrl, string $cookieFile, string $csrf): void
{
    echo "\n3. Performing database operations...\n";
    
    // First, enable API access
    echo "   Enabling API access...\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "{$baseUrl}/api/enableAPIAccess",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest',
            'X-CSRFToken: ' . $csrf,
            'Referer: ' . $baseUrl . '/',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    echo "   Result: {$result}\n";
    
    // Fetch databases
    echo "\n   Fetching databases...\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "{$baseUrl}/dataBases/fetchDatabases",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['selectedWebsite' => 'uhdor.com']),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Requested-With: XMLHttpRequest',
            'X-CSRFToken: ' . $csrf,
            'Referer: ' . $baseUrl . '/dataBases/uhdor.com',
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    echo "   Databases: " . substr($result, 0, 1000) . "\n";
}
