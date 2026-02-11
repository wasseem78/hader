<?php
/**
 * Test tenant registration remotely
 */

echo "=== Testing Registration on Server ===\n\n";

$registerUrl = 'http://uhdor.com/public/register';

// First get the register page to check CSRF token
echo "1. Getting registration page...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $registerUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_COOKIEJAR => sys_get_temp_dir() . '/test_reg_cookie.txt',
    CURLOPT_COOKIEFILE => sys_get_temp_dir() . '/test_reg_cookie.txt',
    CURLOPT_USERAGENT => 'Mozilla/5.0',
]);
$page = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: {$httpCode}\n";

// Extract CSRF token from Laravel form
if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $page, $m)) {
    $token = $m[1];
    echo "   CSRF Token: " . substr($token, 0, 20) . "...\n";
} elseif (preg_match('/_token.*?value=["\']([^"\']+)["\']/s', $page, $m)) {
    $token = $m[1];
    echo "   CSRF Token: " . substr($token, 0, 20) . "...\n";
} else {
    echo "   Page content (first 500 chars):\n";
    echo "   " . substr(strip_tags($page), 0, 500) . "\n";
    echo "   ✗ No CSRF token found!\n";
    $token = null;
}

if ($token) {
    echo "\n2. Submitting test registration...\n";
    
    $testData = [
        '_token' => $token,
        'company_name' => 'TestCompany' . rand(100, 999),
        'name' => 'Test User',
        'email' => 'test' . rand(100, 999) . '@test.com',
        'password' => 'TestPass123!',
        'password_confirmation' => 'TestPass123!',
    ];
    
    echo "   Data: company={$testData['company_name']}, email={$testData['email']}\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $registerUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($testData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_COOKIEJAR => sys_get_temp_dir() . '/test_reg_cookie.txt',
        CURLOPT_COOKIEFILE => sys_get_temp_dir() . '/test_reg_cookie.txt',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
            'Referer: ' . $registerUrl,
            'Accept: text/html,application/xhtml+xml',
        ],
        CURLOPT_USERAGENT => 'Mozilla/5.0',
        CURLOPT_HEADER => true,
    ]);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);
    
    echo "   HTTP Code: {$httpCode}\n";
    echo "   Final URL: {$finalUrl}\n";
    
    // Check for success or error
    if (strpos($finalUrl, 'login') !== false) {
        echo "   ✓✓✓ REGISTRATION SUCCESSFUL! Redirected to login page.\n";
    } elseif (preg_match('/Registration failed[^<]*/', $result, $errMatch)) {
        echo "   ✗ Error: " . $errMatch[0] . "\n"; 
    } elseif (preg_match('/class="[^"]*error[^"]*"[^>]*>([^<]+)/i', $result, $errMatch)) {
        echo "   ✗ Error: " . trim($errMatch[1]) . "\n";
    } else {
        // Show page title
        if (preg_match('/<title>(.*?)<\/title>/i', $result, $titleMatch)) {
            echo "   Page title: " . trim($titleMatch[1]) . "\n";
        }
        // Check for error messages in body
        $body = preg_replace('/^.*?\r?\n\r?\n/s', '', $result); // remove headers
        if (preg_match_all('/(?:error|fail|denied|exception|SQLSTATE)[^<\n]{0,200}/i', $body, $errors)) {
            echo "   Errors found:\n";
            foreach (array_unique($errors[0]) as $err) {
                echo "   - " . trim($err) . "\n";
            }
        } else {
            echo "   Page body (first 300 chars):\n";
            echo "   " . substr(strip_tags($body), 0, 300) . "\n";
        }
    }
}

@unlink(sys_get_temp_dir() . '/test_reg_cookie.txt');
echo "\n=== Done ===\n";
