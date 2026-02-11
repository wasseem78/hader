<?php
/**
 * CyberPanel Full Session Manager 
 * Handles: Login, Database creation, User privileges
 */

class CyberPanelClient
{
    private string $baseUrl;
    private string $cookieFile;
    private ?string $csrf = null;
    private bool $loggedIn = false;

    public function __construct(string $host, int $port = 8090)
    {
        $this->baseUrl = "https://{$host}:{$port}";
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'cp_');
    }

    public function __destruct()
    {
        if (file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    public function login(string $username, string $password): bool
    {
        // Step 1: Get login page to obtain CSRF cookie
        $this->request('GET', '/');
        $this->loadCsrfFromCookie();

        if (!$this->csrf) {
            echo "  ✗ No CSRF token found\n";
            return false;
        }

        // Step 2: Login
        $result = $this->request('POST', '/verifyLogin', [
            'username' => $username,
            'password' => $password,
        ]);

        $data = json_decode($result, true);
        if (isset($data['loginStatus']) && $data['loginStatus'] == 1) {
            $this->loggedIn = true;
            // Reload CSRF after login (it may change)
            $this->loadCsrfFromCookie();
            return true;
        }

        echo "  Login response: {$result}\n";
        return false;
    }

    public function fetchDatabases(string $website): ?array
    {
        // Navigate to database page first (to establish session context)
        $this->request('GET', "/dataBases/{$website}");
        $this->loadCsrfFromCookie();

        $result = $this->request('POST', '/dataBases/fetchDatabases', [
            'selectedWebsite' => $website,
        ]);

        return json_decode($result, true);
    }

    public function createDatabase(string $website, string $dbName, string $dbUser, string $dbPass): ?array
    {
        // Navigate to create database page first (establishes session)
        $this->request('GET', "/dataBases/{$website}");
        $this->loadCsrfFromCookie();

        $result = $this->request('POST', '/dataBases/submitDBCreation', [
            'databaseWebsite' => $website,
            'dbName' => $dbName,
            'dbUsername' => $dbUser,
            'dbPassword' => $dbPass,
        ]);

        return json_decode($result, true);
    }

    public function deleteDatabase(string $dbName): ?array
    {
        $result = $this->request('POST', '/dataBases/submitDatabaseDeletion', [
            'dbName' => $dbName,
        ]);
        return json_decode($result, true);
    }

    public function fetchUsers(string $website): ?array
    {
        $this->request('GET', "/dataBases/{$website}");
        $this->loadCsrfFromCookie();

        $result = $this->request('POST', '/dataBases/fetchDBUsers', [
            'selectedWebsite' => $website,
        ]);
        return json_decode($result, true);
    }

    public function enableAPI(): ?array
    {
        // Navigate to API page first
        $this->request('GET', '/api/');
        $this->loadCsrfFromCookie();
        
        $result = $this->request('POST', '/cloudAPI/enableDisableAPI', [
            'controller' => 'enableAPI',
        ]);
        return json_decode($result, true);
    }

    private function request(string $method, string $path, ?array $data = null): string
    {
        $ch = curl_init();
        $url = $this->baseUrl . $path;
        
        $headers = [
            'X-Requested-With: XMLHttpRequest',
            'Referer: ' . $this->baseUrl . '/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ];

        if ($this->csrf) {
            $headers[] = 'X-CSRFToken: ' . $this->csrf;
        }

        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
        ];

        if ($method === 'POST' && $data !== null) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($data);
            $headers[] = 'Content-Type: application/json';
        }

        $opts[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $opts);
        
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            echo "  cURL Error: {$error}\n";
        }

        return $result ?: '';
    }

    private function loadCsrfFromCookie(): void
    {
        if (!file_exists($this->cookieFile)) return;
        $content = file_get_contents($this->cookieFile);
        if (preg_match('/csrftoken\s+(\S+)/', $content, $m)) {
            $this->csrf = $m[1];
        }
    }
}

// ============================================================
// MAIN EXECUTION
// ============================================================

echo "=== CyberPanel Database Operations ===\n\n";

$client = new CyberPanelClient('91.108.112.113', 8090);

// Login
echo "1. Logging in...\n";
if (!$client->login('admin', 'Araory@2014@2014')) {
    echo "   ✗ Login failed!\n";
    exit(1);
}
echo "   ✓ Login successful!\n\n";

// Fetch existing databases
echo "2. Fetching existing databases...\n";
$dbs = $client->fetchDatabases('uhdor.com');
echo "   Response: " . json_encode($dbs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Fetch DB users
echo "3. Fetching database users...\n";
$users = $client->fetchUsers('uhdor.com');
echo "   Response: " . json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Try to enable API
echo "4. Trying to enable API access...\n";
$apiResult = $client->enableAPI();
echo "   Response: " . json_encode($apiResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test creating a database
echo "5. Creating test database...\n";
$createResult = $client->createDatabase('uhdor.com', 'test_tenant_123', 'root', 'Araory@2014@2014');
echo "   Response: " . json_encode($createResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== Done ===\n";
