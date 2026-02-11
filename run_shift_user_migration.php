<?php
/**
 * Script to create shift_user pivot table in tenant database
 * Run with: php run_shift_user_migration.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Tenant database configuration
$tenantDb = 'attendance_tenant_sweden_7orH7o';
$host = '127.0.0.1';
$port = '3306';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$tenantDb", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to tenant database: $tenantDb\n\n";
    
    // Check if shift_user table exists
    $result = $pdo->query("SHOW TABLES LIKE 'shift_user'");
    $tableExists = $result->fetch();
    
    if (!$tableExists) {
        echo "Creating shift_user pivot table...\n";
        
        $pdo->exec("
            CREATE TABLE shift_user (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                shift_id BIGINT UNSIGNED NOT NULL,
                effective_from DATE NULL,
                effective_to DATE NULL,
                is_primary BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_user_shift (user_id, shift_id),
                CONSTRAINT fk_shift_user_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_shift_user_shift FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✓ shift_user table created successfully\n";
    } else {
        echo "✓ shift_user table already exists\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
