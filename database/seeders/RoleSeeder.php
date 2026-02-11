<?php

// =============================================================================
// Role Seeder - Seed roles and permissions via Spatie Laravel-Permission
// =============================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Device permissions
            'devices.view',
            'devices.create',
            'devices.edit',
            'devices.delete',
            'devices.sync',

            // Employee permissions
            'employees.view',
            'employees.create',
            'employees.edit',
            'employees.delete',

            // Attendance permissions
            'attendance.view',
            'attendance.edit',
            'attendance.export',

            // Reports permissions
            'reports.view',
            'reports.export',

            // Shifts permissions
            'shifts.view',
            'shifts.create',
            'shifts.edit',
            'shifts.delete',

            // Time-off permissions
            'time-off.view',
            'time-off.create',
            'time-off.approve',

            // Settings permissions
            'settings.view',
            'settings.edit',

            // Billing permissions
            'billing.view',
            'billing.manage',

            // Tenant management (super-admin only)
            'tenants.view',
            'tenants.create',
            'tenants.edit',
            'tenants.delete',

            // System settings (super-admin only)
            'system.view',
            'system.edit',
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions($permissions);

        // Company Admin - manages company settings and employees
        $companyAdmin = Role::firstOrCreate(['name' => 'company-admin']);
        $companyAdmin->syncPermissions([
            'devices.view', 'devices.create', 'devices.edit', 'devices.delete', 'devices.sync',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'attendance.view', 'attendance.edit', 'attendance.export',
            'reports.view', 'reports.export',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete',
            'time-off.view', 'time-off.create', 'time-off.approve',
            'settings.view', 'settings.edit',
            'billing.view', 'billing.manage',
        ]);

        // Manager - manages employees and approves time-off
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $manager->syncPermissions([
            'devices.view',
            'employees.view', 'employees.create', 'employees.edit',
            'attendance.view', 'attendance.export',
            'reports.view', 'reports.export',
            'shifts.view',
            'time-off.view', 'time-off.approve',
        ]);

        // Employee - basic access
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'attendance.view',
            'time-off.view', 'time-off.create',
        ]);

        $this->command->info('Roles seeded: super-admin, company-admin, manager, employee');
    }
}
