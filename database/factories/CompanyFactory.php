<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();
        $slug = Str::slug($name);

        return [
            'uuid' => fake()->uuid(),
            'name' => $name,
            'slug' => $slug,
            'domain' => $slug . '.localhost',
            'subdomain' => $slug,
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'timezone' => 'UTC',
            'locale' => 'en',
            'database' => 'tenant_' . str_replace('-', '_', $slug),
            'trial_ends_at' => now()->addDays(14),
            'is_active' => true,
            'max_devices' => 5,
            'max_employees' => 20,
        ];
    }
}
