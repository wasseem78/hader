<?php

// =============================================================================
// Plan Seeder - Seed 3 example plans: Free, Pro, Enterprise
// =============================================================================

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Perfect for small teams getting started with attendance tracking.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'max_devices' => 1,
                'max_employees' => 10,
                'max_users' => 2,
                'retention_days' => 30,
                'api_access' => false,
                'advanced_reports' => false,
                'custom_branding' => false,
                'priority_support' => false,
                'trial_days' => 0,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
                'features' => [
                    'basic_attendance',
                    'single_device',
                    'email_support',
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'For growing businesses that need more devices and advanced features.',
                'price_monthly' => 49.00,
                'price_yearly' => 490.00,
                'stripe_price_monthly_id' => config('services.stripe.prices.pro_monthly', 'price_pro_monthly_placeholder'),
                'stripe_price_yearly_id' => config('services.stripe.prices.pro_yearly', 'price_pro_yearly_placeholder'),
                'max_devices' => 5,
                'max_employees' => 100,
                'max_users' => 10,
                'retention_days' => 365,
                'api_access' => true,
                'advanced_reports' => true,
                'custom_branding' => false,
                'priority_support' => false,
                'trial_days' => 14,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'features' => [
                    'basic_attendance',
                    'multiple_devices',
                    'api_access',
                    'advanced_reports',
                    'shift_management',
                    'time_off_management',
                    'email_support',
                    'chat_support',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations with unlimited needs and dedicated support.',
                'price_monthly' => 199.00,
                'price_yearly' => 1990.00,
                'stripe_price_monthly_id' => config('services.stripe.prices.enterprise_monthly', 'price_ent_monthly_placeholder'),
                'stripe_price_yearly_id' => config('services.stripe.prices.enterprise_yearly', 'price_ent_yearly_placeholder'),
                'max_devices' => 50,
                'max_employees' => 1000,
                'max_users' => 50,
                'retention_days' => 730, // 2 years
                'api_access' => true,
                'advanced_reports' => true,
                'custom_branding' => true,
                'priority_support' => true,
                'trial_days' => 30,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
                'features' => [
                    'basic_attendance',
                    'unlimited_devices',
                    'api_access',
                    'advanced_reports',
                    'shift_management',
                    'time_off_management',
                    'custom_branding',
                    'white_label',
                    'sso_integration',
                    'dedicated_support',
                    'sla_guarantee',
                    'onboarding_assistance',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('Plans seeded: Free, Pro, Enterprise');
    }
}
