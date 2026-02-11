<?php
/**
 * Update server files via CyberPanel File Manager
 * 1. Update .env with CyberPanel settings
 * 2. Upload updated TenantProvisioner.php
 * 3. Clear config cache
 */

class CyberPanelFileManager
{
    private string $baseUrl;
    private string $cookieFile;
    private ?string $csrf = null;

    public function __construct(string $host, int $port = 8090)
    {
        $this->baseUrl = "https://{$host}:{$port}";
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'cpfm_');
    }

    public function __destruct()
    {
        @unlink($this->cookieFile);
    }

    public function login(string $user, string $pass): bool
    {
        $this->get('/');
        $this->loadCsrf();
        $result = json_decode($this->post('/verifyLogin', ['username' => $user, 'password' => $pass]), true);
        if (isset($result['loginStatus']) && $result['loginStatus'] == 1) {
            $this->loadCsrf();
            return true;
        }
        return false;
    }

    public function readFile(string $website, string $path): ?string
    {
        $this->get("/filemanager/{$website}/");
        $this->loadCsrf();
        $result = json_decode($this->post('/filemanager/controller', [
            'domainName' => $website,
            'method' => 'readFileContents',
            'fileName' => $path,
            'domainRandomSeed' => '',
        ]), true);
        return $result['fileContents'] ?? null;
    }

    public function writeFile(string $website, string $path, string $content): bool
    {
        $this->get("/filemanager/{$website}/");
        $this->loadCsrf();
        $result = json_decode($this->post('/filemanager/controller', [
            'domainName' => $website,
            'method' => 'writeFileContents',
            'fileName' => $path,
            'fileContent' => $content,
            'domainRandomSeed' => '',
        ]), true);
        return isset($result['status']) && $result['status'] == 1;
    }

    private function get(string $path): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r ?: '';
    }

    private function post(string $path, array $data): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $path,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest',
                'X-CSRFToken: ' . ($this->csrf ?? ''),
                'Referer: ' . $this->baseUrl . '/',
            ],
            CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r ?: '';
    }

    private function loadCsrf(): void
    {
        if (!file_exists($this->cookieFile)) return;
        if (preg_match('/csrftoken\s+(\S+)/', file_get_contents($this->cookieFile), $m)) {
            $this->csrf = $m[1];
        }
    }
}

// ============================================================
echo "=== Updating Server Files ===\n\n";

$fm = new CyberPanelFileManager('91.108.112.113', 8090);

echo "1. Logging in to CyberPanel...\n";
if (!$fm->login('admin', 'Araory@2014@2014')) {
    die("   ✗ Login failed!\n");
}
echo "   ✓ Login successful!\n\n";

$website = 'uhdor.com';
$basePath = '/home/uhdor.com/public_html';

// ============================================================
// Step 2: Update .env file
// ============================================================
echo "2. Reading current .env...\n";
$currentEnv = $fm->readFile($website, "{$basePath}/.env");
if (!$currentEnv) {
    die("   ✗ Failed to read .env!\n");
}
echo "   ✓ Read .env (" . strlen($currentEnv) . " bytes)\n\n";

// Build new .env content
$newEnv = $currentEnv;

// Fix APP_URL
$newEnv = preg_replace('/^APP_URL=.*/m', 'APP_URL=http://uhdor.com/public', $newEnv);

// Fix APP_DOMAIN
$newEnv = preg_replace('/^APP_DOMAIN=.*/m', 'APP_DOMAIN=uhdor.com', $newEnv);

// Fix TENANCY_CENTRAL_DOMAINS
$newEnv = preg_replace('/^TENANCY_CENTRAL_DOMAINS=.*/m', 'TENANCY_CENTRAL_DOMAINS=uhdor.com,www.uhdor.com,91.108.112.113', $newEnv);

// Add CyberPanel settings if not present
if (strpos($newEnv, 'HOSTING_PANEL') === false) {
    $newEnv .= "\n\n# =============================================================================\n";
    $newEnv .= "# CyberPanel Hosting Configuration\n";
    $newEnv .= "# =============================================================================\n";
    $newEnv .= "HOSTING_PANEL=cyberpanel\n";
    $newEnv .= "PANEL_DB_PREFIX=uhdor_\n";
    $newEnv .= "CYBERPANEL_HOST=127.0.0.1\n";
    $newEnv .= "CYBERPANEL_PORT=8090\n";
    $newEnv .= "CYBERPANEL_USERNAME=admin\n";
    $newEnv .= "CYBERPANEL_PASSWORD=\"Araory@2014@2014\"\n";
    $newEnv .= "CYBERPANEL_WEBSITE=uhdor.com\n";
}

echo "3. Writing updated .env...\n";
if ($fm->writeFile($website, "{$basePath}/.env", $newEnv)) {
    echo "   ✓ .env updated successfully!\n\n";
} else {
    echo "   ✗ Failed to write .env!\n\n";
}

// ============================================================
// Step 3: Upload TenantProvisioner.php
// ============================================================
echo "4. Uploading TenantProvisioner.php...\n";
$provisionerCode = file_get_contents(__DIR__ . '/app/Tenancy/Services/TenantProvisioner.php');
$remotePath = "{$basePath}/app/Tenancy/Services/TenantProvisioner.php";

if ($fm->writeFile($website, $remotePath, $provisionerCode)) {
    echo "   ✓ TenantProvisioner.php uploaded!\n\n";
} else {
    echo "   ✗ Failed to upload TenantProvisioner.php!\n\n";
}

// ============================================================
// Step 4: Verify the update
// ============================================================
echo "5. Verifying .env update...\n";
$verifyEnv = $fm->readFile($website, "{$basePath}/.env");
if ($verifyEnv) {
    // Check key values
    $checks = [
        'APP_URL' => 'http://uhdor.com/public',
        'APP_DOMAIN' => 'uhdor.com',
        'HOSTING_PANEL' => 'cyberpanel',
        'PANEL_DB_PREFIX' => 'uhdor_',
        'CYBERPANEL_WEBSITE' => 'uhdor.com',
    ];
    
    foreach ($checks as $key => $expected) {
        if (preg_match("/^{$key}=(.*)/m", $verifyEnv, $m)) {
            $value = trim($m[1], '"\'');
            $status = ($value === $expected) ? '✓' : '✗';
            echo "   {$status} {$key}={$value}\n";
        } else {
            echo "   ✗ {$key} not found!\n";
        }
    }
}

echo "\n6. Verifying TenantProvisioner.php upload...\n";
$verifyProv = $fm->readFile($website, $remotePath);
if ($verifyProv && strpos($verifyProv, 'createDatabaseViaCyberPanel') !== false) {
    echo "   ✓ TenantProvisioner.php contains CyberPanel methods!\n";
} else {
    echo "   ✗ TenantProvisioner.php verification failed\n";
}

// ============================================================
// Step 5: Clear config cache
// ============================================================
echo "\n7. Creating cache clear script on server...\n";
$clearCacheScript = '<?php
require __DIR__ . "/../vendor/autoload.php";
$app = require_once __DIR__ . "/../bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<pre>";
echo "Clearing config cache...\n";
Artisan::call("config:clear");
echo Artisan::output();

echo "Clearing application cache...\n";
Artisan::call("cache:clear");
echo Artisan::output();

echo "Clearing route cache...\n";
Artisan::call("route:clear");
echo Artisan::output();

echo "Clearing view cache...\n";
Artisan::call("view:clear");
echo Artisan::output();

echo "\n✓ All caches cleared!\n";
echo "</pre>";
';

if ($fm->writeFile($website, "{$basePath}/public/clear_cache.php", $clearCacheScript)) {
    echo "   ✓ Cache clear script created!\n";
    
    // Execute the cache clear script
    echo "   Executing cache clear...\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'http://uhdor.com/public/clear_cache.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $clearResult = curl_exec($ch);
    $clearCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP Code: {$clearCode}\n";
    echo "   Result: " . strip_tags($clearResult) . "\n";
} else {
    echo "   ✗ Failed to create cache clear script!\n";
}

echo "\n=== Done ===\n";
