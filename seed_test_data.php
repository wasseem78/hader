<?php
/**
 * Comprehensive Test Data Seeder for Attendance System
 * Creates: Shifts, Employees, Attendance Records with realistic patterns
 * Run with: php seed_test_data.php
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
    
    echo "🔗 Connected to tenant database: $tenantDb\n\n";
    
    // Get company_id from existing user
    $stmt = $pdo->query("SELECT company_id FROM users WHERE company_id IS NOT NULL LIMIT 1");
    $companyId = $stmt->fetchColumn();
    
    if (!$companyId) {
        die("❌ No company found. Please login first to create company data.\n");
    }
    
    echo "📦 Company ID: $companyId\n\n";
    
    // =========================================================================
    // 1. CREATE SHIFTS
    // =========================================================================
    echo "📅 Creating Shifts...\n";
    
    $shifts = [
        [
            'uuid' => generateUuid(),
            'company_id' => $companyId,
            'name' => 'الوردية الصباحية',
            'code' => 'MORNING',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'grace_period_minutes' => 15,
            'early_departure_threshold' => 15,
            'overtime_threshold_minutes' => 30,
            'working_days' => json_encode(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
            'is_default' => 1,
            'is_active' => 1,
            'color' => '#6366f1',
        ],
        [
            'uuid' => generateUuid(),
            'company_id' => $companyId,
            'name' => 'الوردية المسائية',
            'code' => 'EVENING',
            'start_time' => '14:00:00',
            'end_time' => '22:00:00',
            'grace_period_minutes' => 10,
            'early_departure_threshold' => 15,
            'overtime_threshold_minutes' => 30,
            'working_days' => json_encode(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
            'is_default' => 0,
            'is_active' => 1,
            'color' => '#8b5cf6',
        ],
        [
            'uuid' => generateUuid(),
            'company_id' => $companyId,
            'name' => 'الوردية الليلية',
            'code' => 'NIGHT',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'grace_period_minutes' => 15,
            'early_departure_threshold' => 15,
            'overtime_threshold_minutes' => 30,
            'working_days' => json_encode(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday']),
            'is_default' => 0,
            'is_active' => 1,
            'next_day_end' => 1,
            'color' => '#1e3a5f',
        ],
    ];
    
    $shiftIds = [];
    foreach ($shifts as $shift) {
        // Check if shift exists
        $stmt = $pdo->prepare("SELECT id FROM shifts WHERE code = ? AND company_id = ?");
        $stmt->execute([$shift['code'], $companyId]);
        $existingId = $stmt->fetchColumn();
        
        if ($existingId) {
            $shiftIds[$shift['code']] = $existingId;
            echo "  ✓ Shift '{$shift['name']}' already exists (ID: $existingId)\n";
        } else {
            $cols = implode(', ', array_keys($shift));
            $placeholders = implode(', ', array_fill(0, count($shift), '?'));
            $stmt = $pdo->prepare("INSERT INTO shifts ($cols) VALUES ($placeholders)");
            $stmt->execute(array_values($shift));
            $shiftIds[$shift['code']] = $pdo->lastInsertId();
            echo "  ✓ Created shift: {$shift['name']} (ID: {$shiftIds[$shift['code']]})\n";
        }
    }
    
    // =========================================================================
    // 2. CREATE EMPLOYEES
    // =========================================================================
    echo "\n👥 Creating Employees...\n";
    
    $departments = ['تقنية المعلومات', 'الموارد البشرية', 'المالية', 'التسويق', 'المبيعات', 'خدمة العملاء'];
    $positions = ['مدير', 'مشرف', 'موظف', 'محلل', 'مهندس', 'أخصائي'];
    
    $employees = [
        ['name' => 'أحمد محمد العلي', 'email' => 'ahmed.ali@test.com', 'department' => 'تقنية المعلومات', 'position' => 'مهندس برمجيات', 'shift' => 'MORNING'],
        ['name' => 'فاطمة خالد السعيد', 'email' => 'fatima.saeed@test.com', 'department' => 'الموارد البشرية', 'position' => 'مدير الموارد البشرية', 'shift' => 'MORNING'],
        ['name' => 'محمد عبدالله الحربي', 'email' => 'mohammed.harbi@test.com', 'department' => 'المالية', 'position' => 'محاسب', 'shift' => 'MORNING'],
        ['name' => 'نورة سعد القحطاني', 'email' => 'noura.qahtani@test.com', 'department' => 'التسويق', 'position' => 'أخصائي تسويق', 'shift' => 'MORNING'],
        ['name' => 'عبدالرحمن فهد المطيري', 'email' => 'abdulrahman.mutairi@test.com', 'department' => 'المبيعات', 'position' => 'مندوب مبيعات', 'shift' => 'MORNING'],
        ['name' => 'سارة أحمد الغامدي', 'email' => 'sara.ghamdi@test.com', 'department' => 'خدمة العملاء', 'position' => 'مشرف خدمة العملاء', 'shift' => 'EVENING'],
        ['name' => 'خالد عمر الشمري', 'email' => 'khalid.shammari@test.com', 'department' => 'تقنية المعلومات', 'position' => 'مدير تقنية المعلومات', 'shift' => 'MORNING'],
        ['name' => 'ريم محمد الدوسري', 'email' => 'reem.dosari@test.com', 'department' => 'المالية', 'position' => 'محلل مالي', 'shift' => 'MORNING'],
        ['name' => 'يوسف سلمان العتيبي', 'email' => 'yousef.otaibi@test.com', 'department' => 'المبيعات', 'position' => 'مدير المبيعات', 'shift' => 'MORNING'],
        ['name' => 'هند فيصل الزهراني', 'email' => 'hind.zahrani@test.com', 'department' => 'التسويق', 'position' => 'مدير التسويق', 'shift' => 'MORNING'],
        ['name' => 'عمر سعود البقمي', 'email' => 'omar.buqami@test.com', 'department' => 'تقنية المعلومات', 'position' => 'مطور ويب', 'shift' => 'EVENING'],
        ['name' => 'لمى عادل السبيعي', 'email' => 'lama.subaie@test.com', 'department' => 'الموارد البشرية', 'position' => 'أخصائي توظيف', 'shift' => 'MORNING'],
        ['name' => 'ماجد ناصر الحارثي', 'email' => 'majid.harthi@test.com', 'department' => 'خدمة العملاء', 'position' => 'موظف خدمة عملاء', 'shift' => 'EVENING'],
        ['name' => 'أمل راشد العنزي', 'email' => 'amal.anazi@test.com', 'department' => 'المالية', 'position' => 'مدير مالي', 'shift' => 'MORNING'],
        ['name' => 'بندر حمد السهلي', 'email' => 'bandar.sahli@test.com', 'department' => 'المبيعات', 'position' => 'مندوب مبيعات', 'shift' => 'MORNING'],
    ];
    
    $employeeIds = [];
    $empCount = 1;
    
    foreach ($employees as $emp) {
        // Check if employee exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$emp['email']]);
        $existingId = $stmt->fetchColumn();
        
        if ($existingId) {
            $employeeIds[] = ['id' => $existingId, 'shift' => $emp['shift']];
            echo "  ✓ Employee '{$emp['name']}' already exists (ID: $existingId)\n";
        } else {
            $uuid = generateUuid();
            $stmt = $pdo->prepare("
                INSERT INTO users (uuid, company_id, name, email, password, employee_id, department, position, is_active, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([
                $uuid,
                $companyId,
                $emp['name'],
                $emp['email'],
                password_hash('password123', PASSWORD_DEFAULT),
                'EMP' . str_pad($empCount, 4, '0', STR_PAD_LEFT),
                $emp['department'],
                $emp['position'],
            ]);
            $newId = $pdo->lastInsertId();
            $employeeIds[] = ['id' => $newId, 'shift' => $emp['shift']];
            
            // Assign shift to employee
            $shiftId = $shiftIds[$emp['shift']];
            $stmt = $pdo->prepare("INSERT IGNORE INTO shift_user (user_id, shift_id, is_primary, effective_from, created_at, updated_at) VALUES (?, ?, 1, CURDATE(), NOW(), NOW())");
            $stmt->execute([$newId, $shiftId]);
            
            echo "  ✓ Created employee: {$emp['name']} (ID: $newId, Shift: {$emp['shift']})\n";
        }
        $empCount++;
    }
    
    // =========================================================================
    // 3. CREATE ATTENDANCE RECORDS (Last 30 days)
    // =========================================================================
    echo "\n📊 Creating Attendance Records (Last 30 days)...\n";
    
    // Clear existing attendance records for clean data
    $stmt = $pdo->prepare("DELETE FROM attendance_records WHERE company_id = ?");
    $stmt->execute([$companyId]);
    echo "  🗑️ Cleared existing attendance records\n";
    
    $startDate = new DateTime('-30 days');
    $endDate = new DateTime('today');
    $recordCount = 0;
    
    // Attendance patterns for realistic data
    $patterns = [
        'punctual' => ['late_chance' => 0.05, 'absent_chance' => 0.02, 'early_leave_chance' => 0.03], // Very good employee
        'average' => ['late_chance' => 0.15, 'absent_chance' => 0.05, 'early_leave_chance' => 0.08],  // Average employee
        'problematic' => ['late_chance' => 0.35, 'absent_chance' => 0.10, 'early_leave_chance' => 0.15], // Problematic employee
    ];
    
    // Assign patterns to employees
    $employeePatterns = [];
    foreach ($employeeIds as $index => $empData) {
        if ($index % 5 == 0) {
            $employeePatterns[$empData['id']] = 'problematic'; // Every 5th employee is problematic
        } elseif ($index % 3 == 0) {
            $employeePatterns[$empData['id']] = 'average'; // Every 3rd is average
        } else {
            $employeePatterns[$empData['id']] = 'punctual'; // Rest are punctual
        }
    }
    
    $workingDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
    
    $currentDate = clone $startDate;
    while ($currentDate <= $endDate) {
        $dayName = $currentDate->format('l');
        
        // Skip non-working days
        if (!in_array($dayName, $workingDays)) {
            $currentDate->modify('+1 day');
            continue;
        }
        
        foreach ($employeeIds as $empData) {
            $empId = $empData['id'];
            $shiftCode = $empData['shift'];
            $shiftId = $shiftIds[$shiftCode];
            $pattern = $patterns[$employeePatterns[$empId]];
            
            // Get shift times
            $shift = array_filter($shifts, fn($s) => $s['code'] === $shiftCode);
            $shift = reset($shift);
            $shiftStart = $shift['start_time'];
            $shiftEnd = $shift['end_time'];
            $gracePeriod = $shift['grace_period_minutes'];
            
            // Random absence
            if (mt_rand(1, 100) / 100 <= $pattern['absent_chance']) {
                continue; // Employee is absent
            }
            
            // Calculate check-in time
            $isLate = mt_rand(1, 100) / 100 <= $pattern['late_chance'];
            $lateMinutes = 0;
            
            if ($isLate) {
                // Late by 5 to 90 minutes
                $lateMinutes = mt_rand(5, 90);
            } else {
                // Early or on time (-15 to +gracePeriod minutes)
                $lateMinutes = mt_rand(-15, $gracePeriod);
            }
            
            $checkInTime = new DateTime($currentDate->format('Y-m-d') . ' ' . $shiftStart);
            $checkInTime->modify("+{$lateMinutes} minutes");
            
            // Calculate actual late minutes (after grace period)
            $actualLateMinutes = max(0, $lateMinutes - $gracePeriod);
            $isActuallyLate = $actualLateMinutes > 0;
            
            // Insert check-in record
            $checkInUuid = generateUuid();
            $stmt = $pdo->prepare("
                INSERT INTO attendance_records 
                (uuid, company_id, user_id, shift_id, punched_at, punch_date, punch_time, type, is_late, late_minutes, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'in', ?, ?, 'processed', NOW(), NOW())
            ");
            $stmt->execute([
                $checkInUuid,
                $companyId,
                $empId,
                $shiftId,
                $checkInTime->format('Y-m-d H:i:s'),
                $checkInTime->format('Y-m-d'),
                $checkInTime->format('H:i:s'),
                $isActuallyLate ? 1 : 0,
                $actualLateMinutes,
            ]);
            $recordCount++;
            
            // Calculate check-out time
            $isEarlyLeave = mt_rand(1, 100) / 100 <= $pattern['early_leave_chance'];
            $earlyMinutes = 0;
            $overtimeMinutes = 0;
            
            if ($isEarlyLeave) {
                $earlyMinutes = mt_rand(10, 60);
            } else {
                // Sometimes overtime (0 to 120 minutes)
                if (mt_rand(1, 100) <= 20) {
                    $overtimeMinutes = mt_rand(15, 120);
                }
            }
            
            $checkOutTime = new DateTime($currentDate->format('Y-m-d') . ' ' . $shiftEnd);
            if ($shift['next_day_end'] ?? false) {
                $checkOutTime->modify('+1 day');
            }
            $checkOutTime->modify("-{$earlyMinutes} minutes");
            $checkOutTime->modify("+{$overtimeMinutes} minutes");
            
            // Calculate work duration
            $workDuration = ($checkOutTime->getTimestamp() - $checkInTime->getTimestamp()) / 60;
            
            // Insert check-out record
            $checkOutUuid = generateUuid();
            $stmt = $pdo->prepare("
                INSERT INTO attendance_records 
                (uuid, company_id, user_id, shift_id, punched_at, punch_date, punch_time, type, is_early_departure, early_minutes, overtime_minutes, work_duration_minutes, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'out', ?, ?, ?, ?, 'processed', NOW(), NOW())
            ");
            $stmt->execute([
                $checkOutUuid,
                $companyId,
                $empId,
                $shiftId,
                $checkOutTime->format('Y-m-d H:i:s'),
                $currentDate->format('Y-m-d'), // Same date as check-in for reporting
                $checkOutTime->format('H:i:s'),
                $earlyMinutes > 15 ? 1 : 0,
                $earlyMinutes,
                $overtimeMinutes,
                round($workDuration),
            ]);
            $recordCount++;
        }
        
        $currentDate->modify('+1 day');
    }
    
    echo "  ✓ Created $recordCount attendance records\n";
    
    // =========================================================================
    // SUMMARY
    // =========================================================================
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ TEST DATA SEEDING COMPLETED!\n";
    echo str_repeat("=", 50) . "\n";
    echo "📊 Summary:\n";
    echo "   - Shifts: " . count($shifts) . "\n";
    echo "   - Employees: " . count($employees) . "\n";
    echo "   - Attendance Records: $recordCount\n";
    echo "   - Date Range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";
    echo "\n🎯 Employee Patterns:\n";
    foreach ($employeePatterns as $id => $pattern) {
        echo "   - Employee ID $id: $pattern\n";
    }
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function generateUuid(): string
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
