<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class TenantPasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * Search across all tenant databases to find the user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');
        
        // Search for user in all tenant databases (similar to TenantLoginController)
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
                    // Store tenant_id in session so password reset can find the user
                    session(['password_reset_tenant_id' => $tenant->id]);
                    
                    // Send reset link using tenant connection
                    $status = Password::broker('users')->sendResetLink(
                        $request->only('email')
                    );

                    return $status == Password::RESET_LINK_SENT
                        ? back()->with('status', __($status))
                        : back()->withInput($request->only('email'))
                                ->withErrors(['email' => __($status)]);
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to check tenant {$tenant->subdomain} for password reset: " . $e->getMessage());
                continue;
            }
        }

        // If we get here, user was not found in any tenant
        // Return a generic message for security (don't reveal if email exists)
        return back()->with('status', __('passwords.sent'));
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
