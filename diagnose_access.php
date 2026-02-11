<?php

require __DIR__.'/vendor/autoload.php';

$baseUrl = 'http://yar.localhost:8000';
$loginUrl = $baseUrl . '/login';

// 1. Get Login Page and CSRF Token
echo "1. Fetching Login Page...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
$response = curl_exec($ch);

if (preg_match('/name="csrf-token" content="(.*?)"/', $response, $matches)) {
    $csrfToken = $matches[1];
    echo "   -> CSRF Token found: " . substr($csrfToken, 0, 10) . "...\n";
} else {
    echo "   -> ERROR: CSRF Token not found!\n";
    exit(1);
}

// 2. POST Login
echo "2. Submitting Login...\n";
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $csrfToken,
    'email' => 'wasseem78@gmail.com', // Using the email from previous error logs
    'password' => 'password', // Assuming default password from seeder/provisioner
]));
// We need to follow redirect manually to inspect intermediate steps
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "   -> HTTP Code: $httpCode\n";

if ($httpCode == 302) {
    if (preg_match('/Location: (.*)/i', $response, $matches)) {
        $redirectUrl = trim($matches[1]);
        echo "   -> Redirecting to: $redirectUrl\n";
        
        // 3. Follow Redirect
        echo "3. Following Redirect...\n";
        curl_setopt($ch, CURLOPT_URL, $redirectUrl);
        curl_setopt($ch, CURLOPT_POST, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        echo "   -> HTTP Code: $httpCode\n";
        
        if ($httpCode == 200) {
            echo "   -> SUCCESS: Dashboard loaded!\n";
            $body = substr($response, strpos($response, "\r\n\r\n") + 4);
             if (strpos($body, 'Dashboard') !== false) {
                 echo "   -> Verified 'Dashboard' text in body.\n";
             } else {
                 echo "   -> WARNING: 'Dashboard' text NOT found in body.\n";
             }

             // 4. Check Assets (CSS/JS)
             echo "4. Checking Assets...\n";
             if (preg_match('/<link.*?href="(.*?)"/', $body, $matches)) {
                 $cssUrl = $matches[1];
                 echo "   -> Found CSS: $cssUrl\n";
                 // Handle relative URLs
                 if (strpos($cssUrl, 'http') !== 0) {
                     $cssUrl = $baseUrl . '/' . ltrim($cssUrl, '/');
                 }
                 
                 $ch_asset = curl_init();
                 curl_setopt($ch_asset, CURLOPT_URL, $cssUrl);
                 curl_setopt($ch_asset, CURLOPT_NOBODY, true); // HEAD request
                 curl_setopt($ch_asset, CURLOPT_RETURNTRANSFER, true);
                 curl_exec($ch_asset);
                 $assetCode = curl_getinfo($ch_asset, CURLINFO_HTTP_CODE);
                 curl_close($ch_asset);
                 
                 echo "   -> CSS Status: $assetCode " . ($assetCode == 200 ? "(OK)" : "(FAILED)") . "\n";
             } else {
                 echo "   -> WARNING: No CSS links found.\n";
             }
        } else {
            echo "   -> FAILED: Expected 200 OK, got $httpCode\n";
            $body = substr($response, strpos($response, "\r\n\r\n") + 4);
            echo "Body Preview:\n" . substr($body, 0, 500) . "\n";
        }
    }
} else {
    echo "   -> FAILED: Login did not redirect. Response:\n";
    $body = substr($response, strpos($response, "\r\n\r\n") + 4);
    echo substr($body, 0, 500) . "\n";
}

curl_close($ch);
unlink('cookie.txt');

