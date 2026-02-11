<?php
/**
 * CyberPanel Database Operations - Corrected API calls
 */

class CyberPanelClient2
{
    private string $baseUrl;
    private string $cookieFile;
    private ?string $csrf = null;

    public function __construct(string $host, int $port = 8090)
    {
        $this->baseUrl = "https://{$host}:{$port}";
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'cp2_');
    }

    public function __destruct()
    {
        @unlink($this->cookieFile);
    }

    public function login(string $username, string $password): bool
    {
        $this->get('/');
        $this->loadCsrf();
        $result = $this->post('/verifyLogin', ['username' => $username, 'password' => $password]);
        $data = json_decode($result, true);
        if (isset($data['loginStatus']) && $data['loginStatus'] == 1) {
            $this->loadCsrf();
            return true;
        }
        return false;
    }

    public function fetchDatabases(string $website): ?array
    {
        // Visit page first
        $this->get("/dataBases/{$website}");
        $this->loadCsrf();
        return json_decode($this->post('/dataBases/fetchDatabases', [
            'databaseWebsite' => $website,
            'selectedWebsite' => $website,
        ]), true);
    }

    public function createDatabase(string $website, string $dbName, string $dbUser, string $dbPass): ?array
    {
        $this->get("/dataBases/{$website}");
        $this->loadCsrf();
        
        // CyberPanel expects these fields
        return json_decode($this->post('/dataBases/submitDBCreation', [
            'databaseWebsite' => $website,
            'dbName' => $dbName,
            'dbUsername' => $dbUser,
            'dbPassword' => $dbPass,
            'webUserName' => $website,
        ]), true);
    }

    public function listWebsites(): ?array
    {
        $this->get('/websites/');
        $this->loadCsrf();
        return json_decode($this->post('/websites/fetchWebsites', [
            'page' => 1,
        ]), true);
    }

    // Try to run a command via CyberPanel terminal/file manager
    public function runCommand(string $website, string $command): ?array
    {
        $this->get("/websites/{$website}/");
        $this->loadCsrf();
        
        // Try the web terminal endpoint
        return json_decode($this->post('/CloudLinux/runCommand', [
            'command' => $command,
        ]), true);
    }

    // Try to write a file via file manager
    public function writeFile(string $website, string $filePath, string $content): ?array
    {
        $this->get("/filemanager/{$website}/");
        $this->loadCsrf();
        
        return json_decode($this->post('/filemanager/controller', [
            'domainName' => $website,
            'method' => 'writeFileContents',
            'fileName' => $filePath,
            'fileContent' => $content,
        ]), true);
    }

    public function readFile(string $website, string $filePath): ?array
    {
        $this->get("/filemanager/{$website}/");
        $this->loadCsrf();
        
        return json_decode($this->post('/filemanager/controller', [
            'domainName' => $website,
            'method' => 'readFileContents',
            'fileName' => $filePath,
            'domainRandomSeed' => '',
        ]), true);
    }

    public function listFiles(string $website, string $path): ?array
    {
        $this->get("/filemanager/{$website}/");
        $this->loadCsrf();
        
        return json_decode($this->post('/filemanager/controller', [
            'domainName' => $website,
            'method' => 'listForTable',
            'home' => $path,
            'completeStartingPath' => $path,
        ]), true);
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
            CURLOPT_HTTPHEADER => [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ?: '';
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
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ?: '';
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
echo "=== CyberPanel Operations v2 ===\n\n";

$cp = new CyberPanelClient2('91.108.112.113', 8090);

echo "1. Logging in...\n";
if (!$cp->login('admin', 'Araory@2014@2014')) {
    die("   ✗ Login failed!\n");
}
echo "   ✓ Login successful!\n\n";

// List websites
echo "2. Listing websites...\n";
$websites = $cp->listWebsites();
echo "   " . json_encode($websites, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// List files to find the site path
echo "3. Finding site files...\n";
$files = $cp->listFiles('uhdor.com', '/home/uhdor.com/');
if ($files) {
    echo "   Files in /home/uhdor.com/: " . json_encode($files, JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "   Trying /home/uhdor.com/public_html/...\n";
    $files = $cp->listFiles('uhdor.com', '/home/uhdor.com/public_html/');
    echo "   " . json_encode($files, JSON_PRETTY_PRINT) . "\n\n";
}

// Read .env file
echo "4. Reading .env file...\n";
$envPaths = [
    '/home/uhdor.com/public_html/.env',
    '/home/uhdor.com/.env',
    '/var/www/uhdor.com/.env',
    '/var/www/html/.env',
];
foreach ($envPaths as $path) {
    echo "   Trying {$path}... ";
    $env = $cp->readFile('uhdor.com', $path);
    if ($env && !empty($env['fileContent'] ?? $env['data'] ?? null)) {
        echo "✓ FOUND!\n";
        $content = $env['fileContent'] ?? $env['data'] ?? '';
        echo "   Content:\n" . substr($content, 0, 1000) . "\n\n";
        break;
    } else {
        echo json_encode($env) . "\n";
    }
}

// Fetch databases
echo "5. Fetching databases...\n";
$dbs = $cp->fetchDatabases('uhdor.com');
echo "   " . json_encode($dbs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "=== Done ===\n";
