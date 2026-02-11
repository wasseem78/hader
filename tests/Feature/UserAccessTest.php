<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Notifications\InviteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        // Create company
        $this->company = Company::create([
            'name' => 'Test Company',
            'domain' => 'test',
        ]);
    }

    public function test_admin_can_view_user_management()
    {
        $admin = User::factory()->create(['company_id' => $this->company->id]);
        $admin->assignRole('company-admin');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_employee_cannot_view_user_management()
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);
        $employee->assignRole('employee');

        $response = $this->actingAs($employee)->get(route('admin.users.index'));

        // Should be 403 Forbidden or redirect depending on middleware handling
        // Assuming Spatie permission middleware throws 403
        $response->assertStatus(403);
    }

    public function test_admin_can_invite_user()
    {
        Notification::fake();

        $admin = User::factory()->create(['company_id' => $this->company->id]);
        $admin->assignRole('company-admin');

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'New Manager',
            'email' => 'manager@test.com',
            'role' => 'manager',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        
        $this->assertDatabaseHas('users', [
            'email' => 'manager@test.com',
            'company_id' => $this->company->id,
        ]);

        $user = User::where('email', 'manager@test.com')->first();
        $this->assertTrue($user->hasRole('manager'));

        Notification::assertSentTo($user, InviteUser::class);
    }

    public function test_invited_user_can_accept_invitation()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'password' => 'temp',
        ]);
        
        $url = URL::temporarySignedRoute(
            'admin.users.accept-invite',
            now()->addDays(3),
            ['user' => $user->uuid]
        );

        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertSee('Set Your Password');
    }

    public function test_invited_user_can_set_password()
    {
        $user = User::factory()->create([
            'company_id' => $this->company->id,
            'password' => 'temp',
        ]);

        $response = $this->post(route('admin.users.store-password', $user), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
