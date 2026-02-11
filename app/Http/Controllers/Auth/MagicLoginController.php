<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class MagicLoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(403, 'No token provided.');
        }

        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (\Exception $e) {
            abort(403, 'Invalid token.');
        }

        if (now()->timestamp > $payload['expires_at']) {
            abort(403, 'Token expired.');
        }

        // Get tenant from payload
        $tenantId = $payload['tenant_id'] ?? null;
        
        if (!$tenantId) {
            abort(403, 'No tenant specified.');
        }

        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        // Configure tenant database connection
        $this->configureTenantConnection($tenant);

        // Store tenant_id in session
        session(['tenant_id' => $tenant->id]);

        // Now find user in tenant database
        $user = User::where('email', $payload['email'])->first();

        if (!$user) {
            // Try to find any admin if the specific email doesn't exist (fallback)
            $user = User::role('company-admin')->first();
        }

        if (!$user) {
            abort(404, 'User not found.');
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Logged in as ' . $user->name);
    }

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
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        DB::setDefaultConnection('tenant');

        app()->instance('currentTenant', $tenant);
    }
}
