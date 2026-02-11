<?php
/**
 * Script to add branch_id column to shifts table in tenant database
 * Run with: php run_branch_migrations.php
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
    
    // Check if branch_id column exists in shifts table
    $result = $pdo->query("SHOW COLUMNS FROM shifts LIKE 'branch_id'");
    $columnExists = $result->fetch();
    
    if (!$columnExists) {
        echo "Adding branch_id column to shifts table...\n";
        
        // Add branch_id column
        $pdo->exec("
            ALTER TABLE shifts 
            ADD COLUMN branch_id CHAR(36) NULL AFTER company_id
        ");
        
        echo "✓ Column branch_id added to shifts table\n";
        
        // Check if branches table exists before adding foreign key
        $branchesExists = $pdo->query("SHOW TABLES LIKE 'branches'")->fetch();
        
        if ($branchesExists) {
            // Add foreign key constraint
            try {
                $pdo->exec("
                    ALTER TABLE shifts 
                    ADD CONSTRAINT fk_shifts_branch_id 
                    FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
                ");
                echo "✓ Foreign key constraint added\n";
            } catch (Exception $e) {
                echo "Note: Could not add foreign key (this is OK): " . $e->getMessage() . "\n";
            }
        }
    } else {
        echo "✓ Column branch_id already exists in shifts table\n";
    }
    
    // Check if branch_id exists in devices table
    $result = $pdo->query("SHOW COLUMNS FROM devices LIKE 'branch_id'");
    $deviceColumnExists = $result->fetch();
    
    if (!$deviceColumnExists) {
        echo "\nAdding branch_id column to devices table...\n";
        $pdo->exec("
            ALTER TABLE devices 
            ADD COLUMN branch_id CHAR(36) NULL AFTER company_id
        ");
        echo "✓ Column branch_id added to devices table\n";
    } else {
        echo "✓ Column branch_id already exists in devices table\n";
    }
    
    // Check if branch_id exists in attendance_records table
    $result = $pdo->query("SHOW COLUMNS FROM attendance_records LIKE 'branch_id'");
    $attendanceColumnExists = $result->fetch();
    
    if (!$attendanceColumnExists) {
        echo "\nAdding branch_id column to attendance_records table...\n";
        $pdo->exec("
            ALTER TABLE attendance_records 
            ADD COLUMN branch_id CHAR(36) NULL AFTER company_id
        ");
        echo "✓ Column branch_id added to attendance_records table\n";
    } else {
        echo "✓ Column branch_id already exists in attendance_records table\n";
    }
    
    echo "\n✅ All migrations completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
