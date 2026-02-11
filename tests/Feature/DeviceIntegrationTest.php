<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PlanSeeder::class);

        $this->company = Company::factory()->create();
        $this->user = User::factory()->create(['company_id' => $this->company->id]);
        $this->user->assignRole('company-admin');
    }

    public function test_can_view_create_device_page()
    {
        $response = $this->actingAs($this->user)->get(route('devices.create'));
        $response->assertStatus(200);
        $response->assertSee('Add New Device');
    }

    public function test_can_create_device()
    {
        $response = $this->actingAs($this->user)->post(route('devices.store'), [
            'name' => 'Main Entrance',
            'ip_address' => '192.168.1.201',
            'port' => 4370,
            'location' => 'Lobby',
        ]);

        $response->assertRedirect(route('devices.index'));
        $this->assertDatabaseHas('devices', [
            'name' => 'Main Entrance',
            'ip_address' => '192.168.1.201',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_view_device_details()
    {
        $device = Device::create([
            'company_id' => $this->company->id,
            'name' => 'Test Device',
            'ip_address' => '127.0.0.1',
            'port' => 4370,
            'protocol' => 'tcp',
        ]);

        $response = $this->actingAs($this->user)->get(route('devices.show', $device->uuid));
        $response->assertStatus(200);
        $response->assertSee('Test Device');
        $response->assertSee('Test Connection');
    }

    public function test_connection_simulation_failure()
    {
        // We expect failure because 127.0.0.1:4370 is likely not listening
        $device = Device::create([
            'company_id' => $this->company->id,
            'name' => 'Offline Device',
            'ip_address' => '127.0.0.1',
            'port' => 4370,
        ]);

        $response = $this->actingAs($this->user)->post(route('devices.test', $device->uuid));
        
        $response->assertSessionHas('error');
        $this->assertEquals('offline', $device->fresh()->status);
    }
}
