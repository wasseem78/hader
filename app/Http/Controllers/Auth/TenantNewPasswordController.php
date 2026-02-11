<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class TenantNewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request)
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     * Search across all tenant databases to find the user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $request->input('email');
        
        // Search for user in all tenant databases
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            try {
                // Configure tenant database connection
                $this->configureTenantConnection($tenant);
                
                // Check if user exists in this tenant
                $user = DB::connection('tenant')->table('users')
                    ->where('email', $email)
                    ->first();
                
                if ($user) {
                    // Found the user - attempt password reset
                    $status = Password::broker('users')->reset(
                        $request->only('email', 'password', 'password_confirmation', 'token'),
                        function ($user, $password) {
                            $user->forceFill([
                                'password' => Hash::make($password),
                                'remember_token' => Str::random(60),
                            ])->save();

                            event(new PasswordReset($user));
                        }
                    );

                    return $status == Password::PASSWORD_RESET
                        ? redirect()->route('login')->with('status', __($status))
                        : back()->withInput($request->only('email'))
                                ->withErrors(['email' => __($status)]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to check tenant {$tenant->subdomain} for password reset: " . $e->getMessage());
                continue;
            }
        }

        // If we get here, user was not found
        return back()->withInput($request->only('email'))
                    ->withErrors(['email' => __('passwords.user')]);
    }

    /**
     * Configure the tenant database connection.
     */
    protected function configureTenantConnection(Tenant $tenant): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $tenant->db_host,
            'port' => $tenant->db_port,
            'database' => $tenant->db_name,
            'username' => $tenant->db_username,
            'password' => $tenant->db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Set as default for User model
        Config::set('database.default', 'tenant');
    }
}
