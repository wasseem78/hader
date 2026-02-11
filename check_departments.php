<?php

require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=attendance_tenant_sweden_7orH7o', 'root', '');
$result = $pdo->query('SHOW TABLES LIKE "departments"');

if ($result->rowCount() > 0) {
    echo "✅ Table 'departments' EXISTS\n";
    
    // Show columns
    $cols = $pdo->query('DESCRIBE departments');
    echo "\nColumns:\n";
    foreach ($cols as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
} else {
    echo "❌ Table 'departments' NOT EXISTS\n";
    echo "Running migration...\n";
}

// Check if department_id column exists in users
$result2 = $pdo->query('SHOW COLUMNS FROM users LIKE "department_id"');
if ($result2->rowCount() > 0) {
    echo "\n✅ Column 'department_id' EXISTS in users table\n";
} else {
    echo "\n❌ Column 'department_id' NOT EXISTS in users table\n";
}
