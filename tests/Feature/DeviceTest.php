<?php

// =============================================================================
// Device Feature Tests - API endpoint testing
// =============================================================================

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Device;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a plan
        $plan = Plan::factory()->create([
            'max_devices' => 5,
            'max_employees' => 100,
        ]);

        // Create a company
        $this->company = Company::factory()->create([
            'plan_id' => $plan->id,
            'max_devices' => 5,
        ]);

        // Create a user
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    /** @test */
    public function user_can_list_devices()
    {
        Device::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/tenants/{$this->company->uuid}/devices");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_create_device()
    {
        $payload = [
            'name' => 'Test Device',
            'ip_address' => '192.168.1.100',
            'port' => 4370,
            'location' => 'Main Entrance',
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/tenants/{$this->company->uuid}/devices", $payload);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'Test Device')
            ->assertJsonPath('data.ip_address', '192.168.1.100');

        $this->assertDatabaseHas('devices', [
            'company_id' => $this->company->id,
            'name' => 'Test Device',
        ]);
    }

    /** @test */
    public function user_cannot_create_device_exceeding_plan_limit()
    {
        // Fill up the device limit
        Device::factory()->count(5)->create([
            'company_id' => $this->company->id,
        ]);

        $payload = [
            'name' => 'Sixth Device',
            'ip_address' => '192.168.1.106',
            'port' => 4370,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/tenants/{$this->company->uuid}/devices", $payload);

        $response->assertForbidden()
            ->assertJsonPath('error', 'plan_limit_exceeded');
    }

    /** @test */
    public function user_cannot_create_duplicate_device()
    {
        Device::factory()->create([
            'company_id' => $this->company->id,
            'ip_address' => '192.168.1.100',
            'port' => 4370,
        ]);

        $payload = [
            'name' => 'Duplicate Device',
            'ip_address' => '192.168.1.100',
            'port' => 4370,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/tenants/{$this->company->uuid}/devices", $payload);

        $response->assertUnprocessable();
    }

    /** @test */
    public function user_can_update_device()
    {
        $device = Device::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/tenants/{$this->company->uuid}/devices/{$device->uuid}", [
                'name' => 'New Name',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name');
    }

    /** @test */
    public function user_can_delete_device()
    {
        $device = Device::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/tenants/{$this->company->uuid}/devices/{$device->uuid}");

        $response->assertOk();
        $this->assertSoftDeleted('devices', ['id' => $device->id]);
    }

    /** @test */
    public function validation_fails_for_invalid_ip()
    {
        $payload = [
            'name' => 'Test Device',
            'ip_address' => 'invalid-ip',
            'port' => 4370,
        ];

        $response = $this->actingAs($this->user)
            ->postJson("/api/tenants/{$this->company->uuid}/devices", $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['ip_address']);
    }
}
