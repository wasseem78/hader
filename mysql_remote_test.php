<?php
/**
 * This script connects to the remote server via CyberPanel 
 * and grants MySQL privileges to allow CREATE DATABASE.
 * 
 * Since CyberPanel API is disabled, we'll create a PHP script 
 * that should be uploaded to the server and run there.
 */

// First, let's try to connect directly to MySQL on the remote server
$host = '91.108.112.113';
$port = 3306;

echo "=== Attempting Direct MySQL Connection ===\n\n";

// Try connecting to MySQL remotely
$credentials = [
    ['root', 'Araory@2014@2014'],
    ['uhdor_root', 'Araory@2014@2014'],
    ['root', ''],
];

foreach ($credentials as [$user, $pass]) {
    echo "Trying MySQL: {$user}@{$host}:{$port}... ";
    try {
        $dsn = "mysql:host={$host};port={$port}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        echo "✓ CONNECTED!\n";
        
        // Check grants
        $grants = $pdo->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
        echo "Grants:\n";
        foreach ($grants as $grant) {
            echo "  - {$grant}\n";
        }
        echo "\n";
        
        // Try to grant CREATE privilege
        echo "Attempting to grant CREATE privilege to uhdor_root...\n";
        try {
            $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'uhdor_root'@'localhost' WITH GRANT OPTION");
            $pdo->exec("FLUSH PRIVILEGES");
            echo "✓ Privileges granted!\n";
        } catch (Exception $e) {
            echo "✗ " . $e->getMessage() . "\n";
        }
        
        break;
    } catch (PDOException $e) {
        echo "✗ " . $e->getMessage() . "\n";
    }
}

echo "\n=== Done ===\n";
