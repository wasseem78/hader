<?php

// Test CyberPanel API connection
$apiUrl = 'https://91.108.112.113:8090/api/';

// Try different credential combinations
$credentials = [
    ['admin', 'Araory@2014@2014'],
    ['root', 'Araory@2014@2014'],
];

foreach ($credentials as [$user, $pass]) {
    echo "Testing with user: {$user}...\n";
    
    $result = callCyberPanelApi($apiUrl . 'verifyConn', [
        'adminUser' => $user,
        'adminPass' => $pass,
    ]);
    
    echo "Response: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    if (isset($result['verifyConn']) && $result['verifyConn'] == 1) {
        echo "✓ Connected successfully with user: {$user}\n\n";
        
        // List websites
        echo "Listing websites...\n";
        $websites = callCyberPanelApi($apiUrl . 'listWebsitesJson', [
            'adminUser' => $user,
            'adminPass' => $pass,
        ]);
        echo "Websites: " . json_encode($websites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        // List databases
        echo "Listing databases...\n";
        $databases = callCyberPanelApi($apiUrl . 'fetchDatabases', [
            'adminUser' => $user,
            'adminPass' => $pass,
            'databaseWebsite' => 'uhdor.com',
        ]);
        echo "Databases: " . json_encode($databases, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
        
        break;
    }
}

function callCyberPanelApi(string $url, array $data): ?array
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
        CURLOPT_TIMEOUT => 30,
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "cURL Error: {$error}\n";
        return null;
    }

    echo "HTTP Code: {$httpCode}\n";
    return json_decode($result, true);
}
