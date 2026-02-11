<?php

require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=attendance_tenant_sweden_7orH7o', 'root', '');

echo "Creating departments table...\n";

// Create departments table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `departments` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
        `company_id` bigint unsigned NOT NULL,
        `branch_id` bigint unsigned DEFAULT NULL,
        `manager_id` bigint unsigned DEFAULT NULL,
        `parent_id` bigint unsigned DEFAULT NULL,
        `name` varchar(255) NOT NULL,
        `code` varchar(20) DEFAULT NULL,
        `color` varchar(7) DEFAULT '#6366f1',
        `description` text,
        `phone` varchar(20) DEFAULT NULL,
        `email` varchar(255) DEFAULT NULL,
        `location` varchar(255) DEFAULT NULL,
        `sort_order` int DEFAULT 0,
        `is_active` tinyint(1) DEFAULT 1,
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `departments_company_id_index` (`company_id`),
        KEY `departments_branch_id_index` (`branch_id`),
        KEY `departments_parent_id_index` (`parent_id`),
        KEY `departments_is_active_index` (`is_active`),
        UNIQUE KEY `departments_company_id_code_unique` (`company_id`, `code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

echo "✅ Departments table created\n";

// Add department_id to users table
echo "Adding department_id column to users...\n";

try {
    $pdo->exec("ALTER TABLE `users` ADD COLUMN `department_id` bigint unsigned DEFAULT NULL AFTER `branch_id`");
    $pdo->exec("ALTER TABLE `users` ADD INDEX `users_department_id_index` (`department_id`)");
    echo "✅ department_id column added to users\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "⚠️ Column already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Create some sample departments
echo "\nCreating sample departments...\n";

$departments = [
    ['name' => 'الموارد البشرية', 'code' => 'HR', 'color' => '#10b981'],
    ['name' => 'تقنية المعلومات', 'code' => 'IT', 'color' => '#6366f1'],
    ['name' => 'المالية والمحاسبة', 'code' => 'FIN', 'color' => '#f59e0b'],
    ['name' => 'المبيعات والتسويق', 'code' => 'SALES', 'color' => '#ec4899'],
    ['name' => 'العمليات', 'code' => 'OPS', 'color' => '#8b5cf6'],
    ['name' => 'خدمة العملاء', 'code' => 'CS', 'color' => '#14b8a6'],
];

$stmt = $pdo->prepare("INSERT INTO departments (company_id, name, code, color, is_active, created_at, updated_at) VALUES (1, ?, ?, ?, 1, NOW(), NOW())");

foreach ($departments as $dept) {
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM departments WHERE company_id = 1 AND code = ?");
    $check->execute([$dept['code']]);
    
    if ($check->rowCount() == 0) {
        $stmt->execute([$dept['name'], $dept['code'], $dept['color']]);
        echo "  ✅ Created: {$dept['name']} ({$dept['code']})\n";
    } else {
        echo "  ⚠️ Exists: {$dept['name']} ({$dept['code']})\n";
    }
}

echo "\n🎉 Done!\n";
