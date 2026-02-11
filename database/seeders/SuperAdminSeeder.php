<?php

namespace Database\Seeders;

use App\Models\CentralUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist in central DB
        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'central']);

        $user = CentralUser::firstOrCreate(
            ['email' => 'admin@attendance.local'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole($role);
        
        $this->command->info('Super Admin created: admin@attendance.local / password');
    }
}
