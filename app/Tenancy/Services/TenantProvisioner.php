<?php

namespace App\Tenancy\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class TenantProvisioner
{
    /**
     * Provision a new tenant.
     *
     * @param array $data
     * @return Tenant
     * @throws \Exception
     */
    public function provision(array $data): Tenant
    {
        try {
            $panelType = env('HOSTING_PANEL', 'none'); // none, cyberpanel, cpanel

            // Generate DB name
            $shortName = Str::slug($data['name'], '_') . '_' . Str::random(6);

            if ($panelType === 'cyberpanel') {
                // CyberPanel: webUserName_ prefix is added automatically
                // MySQL name limit = 32 chars total
                $webUserName = $this->getWebUserName();
                $maxShort = 32 - strlen($webUserName) - 1; // -1 for underscore
                $shortName = substr($shortName, 0, $maxShort);
                $dbName = $webUserName . '_' . $shortName;
            } elseif ($panelType === 'cpanel') {
                $panelPrefix = env('PANEL_DB_PREFIX', '');
                $maxShortLen = 64 - strlen($panelPrefix);
                $shortName = substr($shortName, 0, $maxShortLen);
                $dbName = $panelPrefix . $shortName;
            } else {
                $dbName = 'attendance_tenant_' . $shortName;
            }

            // Determine DB credentials
            $dbUsername = env('DB_USERNAME', 'root');
            $dbPassword = env('DB_PASSWORD', '');

            // For CyberPanel, each tenant gets its own MySQL user
            $cyberPanelDbPass = null;
            if ($panelType === 'cyberpanel') {
                $cyberPanelDbPass = Str::random(16);
                $dbUsername = $dbName; // CyberPanel creates user with same name
                $dbPassword = $cyberPanelDbPass;
            }

            // 1. Create Tenant Record in Central DB
            $tenant = Tenant::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'subdomain' => $data['subdomain'],
                'db_name' => $dbName,
                'db_host' => env('DB_HOST', '127.0.0.1'),
                'db_port' => env('DB_PORT', '3306'),
                'db_username_enc' => \Illuminate\Support\Facades\Crypt::encryptString($dbUsername),
                'db_password_enc' => \Illuminate\Support\Facades\Crypt::encryptString($dbPassword),
                'trial_ends_at' => now()->addDays(14),
                'status' => 'provisioning',
            ]);

            // 2. Create Database
            switch ($panelType) {
                case 'cyberpanel':
                    $this->createDatabaseViaCyberPanel($shortName, $cyberPanelDbPass);
                    break;
                case 'cpanel':
                    $this->createDatabaseViaCpanel($dbName);
                    break;
                default:
                    DB::statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    break;
            }
            
            Log::info("Created database: {$dbName} for tenant: {$tenant->name}");

            // 3. Run Migrations on New DB
            $this->runTenantMigrations($tenant);

            // 4. Seed Default Data
            $this->seedTenantData($tenant, $data);

            $tenant->update(['status' => 'active']);
            
            return $tenant;

        } catch (\Exception $e) {
            
            // Cleanup: Drop DB if it was created
            if (isset($dbName)) {
                try {
                    switch ($panelType ?? 'none') {
                        case 'cyberpanel':
                            $this->dropDatabaseViaCyberPanel($dbName);
                            break;
                        case 'cpanel':
                            $this->dropDatabaseViaCpanel($dbName);
                            break;
                        default:
                            DB::statement("DROP DATABASE IF EXISTS `{$dbName}`");
                            break;
                    }
                } catch (\Exception $dropEx) {
                    Log::error("Failed to drop database during rollback: " . $dropEx->getMessage());
                }
            }

            // Cleanup: Delete tenant record if created
            if (isset($tenant) && $tenant->exists) {
                $tenant->delete();
            }

            Log::error("Tenant provisioning failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Compute CyberPanel webUserName from website domain (matches CyberPanel JS logic).
     */
    protected function getWebUserName(): string
    {
        $website = env('CYBERPANEL_WEBSITE', '');
        $name = str_replace('-', '', $website);
        $name = explode('.', $name)[0] ?? $name;
        if (strlen($name) > 5) {
            $name = substr($name, 0, 4);
        }
        return $name;
    }

    /**
     * Run migrations for the tenant.
     */
    protected function runTenantMigrations(Tenant $tenant)
    {
        $this->configureTenantConnection($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }

    /**
     * Seed default data for the tenant.
     */
    protected function seedTenantData(Tenant $tenant, array $data)
    {
        $this->configureTenantConnection($tenant);

        // Create Company Record in Tenant DB
        $companyId = DB::connection('tenant')->table('companies')->insertGetId([
            'name' => $data['name'],
            'email' => $data['email'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Roles & Permissions (Spatie)
        $this->seedRolesAndPermissions();

        // Create Admin User (auto-verified so they can access dashboard immediately)
        $userId = DB::connection('tenant')->table('users')->insertGetId([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Admin User',
            'email' => $data['email'],
            'email_verified_at' => now(),
            'password' => bcrypt($data['password'] ?? 'password'),
            'company_id' => $companyId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Assign company-admin role to the first user
        $role = DB::connection('tenant')->table('roles')->where('name', 'company-admin')->first();
        if ($role) {
            DB::connection('tenant')->table('model_has_roles')->insert([
                'role_id' => $role->id,
                'model_type' => 'App\\Models\\User',
                'model_id' => $userId,
            ]);
        }
    }

    /**
     * Seed roles and permissions into the tenant database.
     */
    protected function seedRolesAndPermissions()
    {
        $now = now();

        $permissions = [
            'devices.view', 'devices.create', 'devices.edit', 'devices.delete', 'devices.sync',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
            'attendance.view', 'attendance.edit', 'attendance.export',
            'reports.view', 'reports.export',
            'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete',
            'time-off.view', 'time-off.create', 'time-off.approve',
            'settings.view', 'settings.edit',
            'billing.view', 'billing.manage',
            'tenants.view', 'tenants.create', 'tenants.edit', 'tenants.delete',
            'system.view', 'system.edit',
        ];

        // Insert permissions
        foreach ($permissions as $perm) {
            DB::connection('tenant')->table('permissions')->insertOrIgnore([
                'name' => $perm,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Roles with their permissions
        $roles = [
            'super-admin' => $permissions, // all
            'company-admin' => [
                'devices.view', 'devices.create', 'devices.edit', 'devices.delete', 'devices.sync',
                'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
                'attendance.view', 'attendance.edit', 'attendance.export',
                'reports.view', 'reports.export',
                'shifts.view', 'shifts.create', 'shifts.edit', 'shifts.delete',
                'time-off.view', 'time-off.create', 'time-off.approve',
                'settings.view', 'settings.edit',
                'billing.view', 'billing.manage',
            ],
            'manager' => [
                'devices.view',
                'employees.view', 'employees.create', 'employees.edit',
                'attendance.view', 'attendance.export',
                'reports.view', 'reports.export',
                'shifts.view',
                'time-off.view', 'time-off.approve',
            ],
            'employee' => [
                'attendance.view',
                'time-off.view', 'time-off.create',
            ],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            DB::connection('tenant')->table('roles')->insertOrIgnore([
                'name' => $roleName,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $roleId = DB::connection('tenant')->table('roles')->where('name', $roleName)->value('id');
            $permIds = DB::connection('tenant')->table('permissions')->whereIn('name', $rolePerms)->pluck('id');

            foreach ($permIds as $permId) {
                DB::connection('tenant')->table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    /**
     * Configure the tenant database connection dynamically.
     */
    protected function configureTenantConnection(Tenant $tenant)
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
    }

    // =========================================================================
    // CyberPanel Methods
    // =========================================================================

    /**
     * Create a database via CyberPanel web session API.
     * 
     * @param string $shortDbName DB name without the webUserName_ prefix
     * @param string $dbPassword  Password for the new MySQL user
     */
    protected function createDatabaseViaCyberPanel(string $shortDbName, string $dbPassword): void
    {
        $host = env('CYBERPANEL_HOST', '127.0.0.1');
        $port = env('CYBERPANEL_PORT', '8090');
        $user = env('CYBERPANEL_USERNAME', 'admin');
        $pass = env('CYBERPANEL_PASSWORD');
        $website = env('CYBERPANEL_WEBSITE');

        if (!$pass || !$website) {
            throw new \Exception('CyberPanel credentials not configured. Set CYBERPANEL_PASSWORD and CYBERPANEL_WEBSITE in .env');
        }

        $baseUrl = "https://{$host}:{$port}";
        $webUserName = $this->getWebUserName();
        $cookieFile = tempnam(sys_get_temp_dir(), 'cyberpanel_');

        try {
            // Step 1: Get CSRF token
            $this->curlGet("{$baseUrl}/", $cookieFile);
            $csrf = $this->getCsrfFromCookie($cookieFile);

            if (!$csrf) {
                throw new \Exception('CyberPanel: Failed to get CSRF token');
            }

            // Step 2: Login (JSON format with X-CSRFToken header)
            $loginResult = $this->curlPost("{$baseUrl}/verifyLogin", [
                'username' => $user,
                'password' => $pass,
            ], $cookieFile, $csrf, $baseUrl);

            $loginData = json_decode($loginResult, true);
            if (!$loginData || ($loginData['loginStatus'] ?? 0) != 1) {
                throw new \Exception('CyberPanel: Login failed - ' . ($loginData['error_message'] ?? 'Unknown error'));
            }

            // Refresh CSRF after login
            $csrf = $this->getCsrfFromCookie($cookieFile);

            // Step 3: Create database
            // CyberPanel adds webUserName_ prefix automatically
            // Limit dbUsername to 26 chars max (32 - prefix - underscore)
            $dbUser = substr($shortDbName, 0, 32 - strlen($webUserName) - 1);

            $createResult = $this->curlPost("{$baseUrl}/dataBases/submitDBCreation", [
                'databaseWebsite' => $website,
                'dbName' => $shortDbName,
                'dbUsername' => $dbUser,
                'dbPassword' => $dbPassword,
                'webUserName' => $webUserName,
            ], $cookieFile, $csrf, $baseUrl);

            $createData = json_decode($createResult, true);

            if (!$createData || ($createData['createDBStatus'] ?? 0) != 1) {
                $error = $createData['error_message'] ?? 'Unknown error';
                throw new \Exception("CyberPanel: Failed to create database: {$error}");
            }

            Log::info("CyberPanel: Database '{$webUserName}_{$shortDbName}' created successfully");

        } finally {
            @unlink($cookieFile);
        }
    }

    /**
     * Drop a database via CyberPanel web session API.
     */
    protected function dropDatabaseViaCyberPanel(string $dbName): void
    {
        $host = env('CYBERPANEL_HOST', '127.0.0.1');
        $port = env('CYBERPANEL_PORT', '8090');
        $user = env('CYBERPANEL_USERNAME', 'admin');
        $pass = env('CYBERPANEL_PASSWORD');

        if (!$pass) {
            Log::warning('Cannot drop database via CyberPanel: credentials not configured');
            return;
        }

        $baseUrl = "https://{$host}:{$port}";
        $cookieFile = tempnam(sys_get_temp_dir(), 'cyberpanel_');

        try {
            $this->curlGet("{$baseUrl}/", $cookieFile);
            $csrf = $this->getCsrfFromCookie($cookieFile);

            $loginResult = $this->curlPost("{$baseUrl}/verifyLogin", [
                'username' => $user,
                'password' => $pass,
            ], $cookieFile, $csrf, $baseUrl);

            $loginData = json_decode($loginResult, true);
            if (!$loginData || ($loginData['loginStatus'] ?? 0) != 1) {
                Log::error("CyberPanel: Login failed for DB drop");
                return;
            }

            $csrf = $this->getCsrfFromCookie($cookieFile);

            $result = $this->curlPost("{$baseUrl}/dataBases/submitDatabaseDeletion", [
                'dbName' => $dbName,
            ], $cookieFile, $csrf, $baseUrl);

            $data = json_decode($result, true);
            if ($data && ($data['status'] ?? 0) == 1) {
                Log::info("CyberPanel: Database '{$dbName}' dropped");
            } else {
                Log::warning("CyberPanel: Failed to drop '{$dbName}': " . ($data['error_message'] ?? 'Unknown'));
            }
        } catch (\Exception $e) {
            Log::error("CyberPanel: Error dropping database: " . $e->getMessage());
        } finally {
            @unlink($cookieFile);
        }
    }

    // =========================================================================
    // cPanel API Methods
    // =========================================================================

    protected function createDatabaseViaCpanel(string $dbName): void
    {
        $cpanelUser = env('CPANEL_USERNAME');
        $cpanelToken = env('CPANEL_API_TOKEN');
        $cpanelHost = env('CPANEL_HOST', '127.0.0.1');
        $cpanelPort = env('CPANEL_PORT', '2083');

        if (!$cpanelUser || !$cpanelToken) {
            throw new \Exception('cPanel API credentials not configured.');
        }

        $cpanelPrefix = env('PANEL_DB_PREFIX', $cpanelUser . '_');
        $shortDbName = $dbName;
        if (str_starts_with($dbName, $cpanelPrefix)) {
            $shortDbName = substr($dbName, strlen($cpanelPrefix));
        }

        $response = $this->callCpanelApi($cpanelHost, $cpanelPort, $cpanelUser, $cpanelToken, 
            'Mysql', 'create_database', ['name' => $shortDbName]);

        if (!$response || (isset($response['status']) && $response['status'] == 0)) {
            throw new \Exception("cPanel: Failed to create database '{$dbName}'");
        }

        $dbUser = env('CPANEL_DB_USER', $cpanelUser . '_' . env('DB_USERNAME', 'root'));
        $shortDbUser = str_starts_with($dbUser, $cpanelPrefix) ? substr($dbUser, strlen($cpanelPrefix)) : $dbUser;

        $this->callCpanelApi($cpanelHost, $cpanelPort, $cpanelUser, $cpanelToken,
            'Mysql', 'set_privileges_on_database', [
                'user' => $shortDbUser,
                'database' => $shortDbName,
                'privileges' => 'ALL PRIVILEGES',
            ]);
    }

    protected function dropDatabaseViaCpanel(string $dbName): void
    {
        $cpanelUser = env('CPANEL_USERNAME');
        $cpanelToken = env('CPANEL_API_TOKEN');
        $cpanelHost = env('CPANEL_HOST', '127.0.0.1');
        $cpanelPort = env('CPANEL_PORT', '2083');

        if (!$cpanelUser || !$cpanelToken) return;

        $cpanelPrefix = env('PANEL_DB_PREFIX', $cpanelUser . '_');
        $shortDbName = str_starts_with($dbName, $cpanelPrefix) ? substr($dbName, strlen($cpanelPrefix)) : $dbName;

        try {
            $this->callCpanelApi($cpanelHost, $cpanelPort, $cpanelUser, $cpanelToken,
                'Mysql', 'delete_database', ['name' => $shortDbName]);
        } catch (\Exception $e) {
            Log::error("cPanel: Failed to drop '{$dbName}': " . $e->getMessage());
        }
    }

    protected function callCpanelApi(string $host, string $port, string $user, string $token, 
        string $module, string $function, array $params = []): ?array
    {
        $url = "https://{$host}:{$port}/execute/{$module}/{$function}?" . http_build_query($params);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER => ["Authorization: cpanel {$user}:{$token}"],
            CURLOPT_TIMEOUT => 30,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) throw new \Exception("cPanel cURL error: {$curlError}");
        if ($httpCode !== 200) throw new \Exception("cPanel HTTP error: {$httpCode}");

        $decoded = json_decode($result, true);
        return $decoded['result'] ?? $decoded;
    }

    // =========================================================================
    // HTTP Helpers
    // =========================================================================

    protected function curlGet(string $url, string $cookieFile): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ?: '';
    }

    protected function curlPost(string $url, array $data, string $cookieFile, string $csrf, string $refererBase = ''): string
    {
        $referer = $refererBase ?: preg_replace('#/[^/]*$#', '/', $url);
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest',
                'X-CSRFToken: ' . $csrf,
                'Referer: ' . $referer . '/',
            ],
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ?: '';
    }

    protected function getCsrfFromCookie(string $cookieFile): ?string
    {
        if (!file_exists($cookieFile)) return null;
        $content = file_get_contents($cookieFile);
        if (preg_match('/csrftoken\s+(\S+)/', $content, $m)) {
            return $m[1];
        }
        return null;
    }
}
