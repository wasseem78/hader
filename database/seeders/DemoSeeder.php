<?php

// =============================================================================
// Demo Seeder - Create demo company and users for development
// =============================================================================

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Device;
use App\Models\Plan;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Pro plan
        $proPlan = Plan::where('slug', 'pro')->first();

        // Create demo company
        $company = Company::firstOrCreate(
            ['slug' => 'demo-company'],
            [
                'name' => 'Demo Company',
                'subdomain' => 'demo',
                'email' => 'admin@demo.local',
                'phone' => '+1234567890',
                'timezone' => 'UTC',
                'locale' => 'en',
                'plan_id' => $proPlan?->id,
                'trial_ends_at' => now()->addDays(14),
                'max_devices' => 5,
                'max_employees' => 100,
                'is_active' => true,
            ]
        );

        // Create super admin (global)
        $superAdmin = User::firstOrCreate(
            ['email' => 'super@attendance.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'company_id' => null, // Global user
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super-admin');

        // Create company admin
        $companyAdmin = User::firstOrCreate(
            ['email' => 'admin@demo.local'],
            [
                'name' => 'Company Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'company_id' => $company->id,
                'is_active' => true,
            ]
        );
        $companyAdmin->assignRole('company-admin');

        // Create manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@demo.local'],
            [
                'name' => 'John Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'company_id' => $company->id,
                'department' => 'Operations',
                'position' => 'Operations Manager',
                'is_active' => true,
            ]
        );
        $manager->assignRole('manager');

        // Create employees
        $employees = [
            ['name' => 'Alice Employee', 'email' => 'alice@demo.local', 'employee_id' => 'EMP001', 'device_user_id' => 1],
            ['name' => 'Bob Employee', 'email' => 'bob@demo.local', 'employee_id' => 'EMP002', 'device_user_id' => 2],
            ['name' => 'Carol Employee', 'email' => 'carol@demo.local', 'employee_id' => 'EMP003', 'device_user_id' => 3],
        ];

        foreach ($employees as $empData) {
            $employee = User::firstOrCreate(
                ['email' => $empData['email']],
                [
                    'name' => $empData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'company_id' => $company->id,
                    'employee_id' => $empData['employee_id'],
                    'device_user_id' => $empData['device_user_id'],
                    'department' => 'General',
                    'is_active' => true,
                ]
            );
            $employee->assignRole('employee');
        }

        // Create demo device
        Device::firstOrCreate(
            ['company_id' => $company->id, 'ip_address' => '192.168.1.100'],
            [
                'name' => 'Main Entrance',
                'port' => 4370,
                'protocol' => 'tcp',
                'model' => 'ZK-F22',
                'location' => 'Building A - Front Door',
                'timezone' => 'UTC',
                'status' => 'offline',
                'is_active' => true,
                'capabilities' => ['fingerprint', 'card'],
            ]
        );

        // Create default shift
        $shift = Shift::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'DAY'],
            [
                'name' => 'Day Shift',
                'start_time' => '09:00',
                'end_time' => '18:00',
                'break_start' => '12:00',
                'break_end' => '13:00',
                'break_duration_minutes' => 60,
                'grace_period_minutes' => 15,
                'early_departure_threshold' => 15,
                'overtime_threshold_minutes' => 30,
                'work_hours' => 8,
                'working_days' => [1, 2, 3, 4, 5], // Mon-Fri
                'is_default' => true,
                'is_active' => true,
                'color' => '#3B82F6',
            ]
        );

        $this->command->info('Demo data seeded:');
        $this->command->info('  - Super Admin: super@attendance.local / password');
        $this->command->info('  - Company Admin: admin@demo.local / password');
        $this->command->info('  - Manager: manager@demo.local / password');
        $this->command->info('  - Employees: alice/bob/carol@demo.local / password');
    }
}
