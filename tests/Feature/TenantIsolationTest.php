<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure tenancy is initialized for tests
        // Note: In a real multi-database setup, we would need to mock the database creation
        // or use a testing trait provided by stancl/tenancy.
        // For this scaffold, we are testing the logical separation.
    }

    public function test_users_are_scoped_to_company()
    {
        $company1 = Company::factory()->create(['name' => 'Company A']);
        $company2 = Company::factory()->create(['name' => 'Company B']);

        $user1 = User::factory()->create(['company_id' => $company1->id, 'email' => 'user1@companya.com']);
        $user2 = User::factory()->create(['company_id' => $company2->id, 'email' => 'user2@companyb.com']);

        $this->assertEquals($company1->id, $user1->company_id);
        $this->assertEquals($company2->id, $user2->company_id);

        // Verify Company A only sees its users
        $this->assertCount(1, $company1->users);
        $this->assertTrue($company1->users->contains($user1));
        $this->assertFalse($company1->users->contains($user2));

        // Verify Company B only sees its users
        $this->assertCount(1, $company2->users);
        $this->assertTrue($company2->users->contains($user2));
        $this->assertFalse($company2->users->contains($user1));
    }

    public function test_expired_subscription_blocks_access()
    {
        $company = Company::factory()->create([
            'trial_ends_at' => now()->subDay(), // Expired trial
            'stripe_subscription_status' => 'canceled',
        ]);

        $user = User::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        // Should be blocked by CheckPlanLimits middleware
        // Note: The middleware returns JSON 403 for API, but might need adjustment for web routes
        // Current implementation in CheckPlanLimits returns JSON 403.
        
        $response->assertStatus(403);
        $response->assertJsonFragment(['error' => 'subscription_expired']);
    }

    public function test_active_subscription_allows_access()
    {
        $company = Company::factory()->create([
            'trial_ends_at' => now()->addDays(14), // Active trial
        ]);

        $user = User::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
    }
}
