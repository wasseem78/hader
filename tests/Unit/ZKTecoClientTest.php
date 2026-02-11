<?php

// =============================================================================
// ZKTecoClient Unit Tests
// =============================================================================

namespace Tests\Unit;

use App\Models\Device;
use App\Services\ZKTeco\ZKTecoClient;
use PHPUnit\Framework\TestCase;

class ZKTecoClientTest extends TestCase
{
    protected Device $device;
    protected ZKTecoClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock device
        $this->device = new Device([
            'name' => 'Test Device',
            'ip_address' => '127.0.0.1',
            'port' => 4370,
            'protocol' => 'tcp',
            'timezone' => 'UTC',
        ]);

        $this->client = new ZKTecoClient($this->device);
    }

    /** @test */
    public function it_can_be_instantiated_with_device()
    {
        $this->assertInstanceOf(ZKTecoClient::class, $this->client);
    }

    /** @test */
    public function it_can_set_device()
    {
        $newDevice = new Device([
            'name' => 'Another Device',
            'ip_address' => '192.168.1.100',
            'port' => 4370,
        ]);

        $result = $this->client->setDevice($newDevice);

        $this->assertInstanceOf(ZKTecoClient::class, $result);
    }

    /** @test */
    public function test_connection_returns_expected_structure()
    {
        $result = $this->client->testConnection();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('latency_ms', $result);
        $this->assertIsBool($result['success']);
    }

    /** @test */
    public function get_users_returns_array()
    {
        // Since we can't actually connect to a device in tests,
        // we expect an empty array when connection fails
        $result = $this->client->getUsers();

        $this->assertIsArray($result);
    }

    /** @test */
    public function get_attendance_logs_returns_array()
    {
        $result = $this->client->getAttendanceLogs();

        $this->assertIsArray($result);
    }

    /** @test */
    public function get_attendance_logs_accepts_since_parameter()
    {
        $since = new \DateTime('-1 day');
        $result = $this->client->getAttendanceLogs($since);

        $this->assertIsArray($result);
    }

    /** @test */
    public function push_user_accepts_user_data()
    {
        $userData = [
            'user_id' => '1',
            'name' => 'Test User',
            'card_number' => '12345678',
            'password' => '1234',
            'privilege' => 0,
        ];

        // Will return false since we can't connect
        $result = $this->client->pushUser($userData);

        $this->assertIsBool($result);
    }
}
