<?php

// =============================================================================
// Database Seeder - Main seeder that calls other seeders
// =============================================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PlanSeeder::class,
            RoleSeeder::class,
        ]);

        // Create a demo company and admin user in non-production
        if (app()->environment(['local', 'staging'])) {
            $this->call(DemoSeeder::class);
        }
    }
}
