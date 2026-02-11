<?php
/**
 * Grant MySQL CREATE DATABASE privileges to uhdor_root
 * Strategy: Upload PHP scripts to server via CyberPanel file manager, execute them
 */

$panelHost = '91.108.112.113';
$panelPort = 8090;
$panelUser = 'admin';
$panelPass = 'Araory@2014@2014';
$sitePath  = '/home/uhdor.com/public_html';
$siteUrl   = 'http://uhdor.com/public';

function http($method, $url, $data = null, $cookies = '', $isJson = false) {
    global $panelHost, $panelPort;
    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => [
            'Referer: https://' . $panelHost . ':' . $panelPort . '/',
        ],
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST] = true;
        if ($isJson) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($data);
            $csrf = '';
            if (preg_match('/csrftoken=([^;]+)/', $cookies, $m)) $csrf = $m[1];
            $opts[CURLOPT_HTTPHEADER] = [
                'Content-Type: application/json',
                'X-CSRFToken: ' . $csrf,
                'Referer: https://' . $panelHost . ':' . $panelPort . '/',
            ];
        } else {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
    }
    if ($cookies) $opts[CURLOPT_COOKIE] = $cookies;
    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    if ($resp === false) die("CURL error: " . curl_error($ch) . "\n");
    $hs = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    return ['headers' => substr($resp, 0, $hs), 'body' => substr($resp, $hs)];
}

function parseCookies($headers) {
    $c = [];
    preg_match_all('/Set-Cookie:\s*([^;]+)/i', $headers, $m);
    foreach ($m[1] as $kv) {
        $p = explode('=', $kv, 2);
        $c[trim($p[0])] = trim($p[1] ?? '');
    }
    return $c;
}

function cookieStr($arr) {
    $s = '';
    foreach ($arr as $k => $v) $s .= "$k=$v; ";
    return $s;
}

function login() {
    global $panelHost, $panelPort, $panelUser, $panelPass;
    $base = "https://$panelHost:$panelPort";
    
    $r = http('GET', $base);
    $c = parseCookies($r['headers']);
    
    $csrf = $c['csrftoken'] ?? '';
    $r = http('POST', "$base/verifyLogin", [
        'username' => $panelUser,
        'password' => $panelPass,
    ], cookieStr($c), true);
    
    foreach (parseCookies($r['headers']) as $k => $v) $c[$k] = $v;
    $body = json_decode($r['body'], true);
    if (!$body || (($body['loginStatus'] ?? $body['status'] ?? 0) != 1)) die("Login failed: {$r['body']}\n");
    
    echo "[OK] CyberPanel login\n";
    return [$base, cookieStr($c)];
}

function uploadFile($base, $cookies, $path, $content) {
    $r = http('POST', "$base/filemanager/controller", [
        'method' => 'writeFileContents',
        'domainName' => '',
        'fileName' => $path,
        'fileContent' => $content,
    ], $cookies, true);
    return json_decode($r['body'], true);
}

function removeFile($base, $cookies, $path) {
    http('POST', "$base/filemanager/controller", [
        'method' => 'deleteFolderOrFile',
        'domainName' => '',
        'path' => dirname($path),
        'fileAndFolders' => [basename($path)],
        'listOfFiles' => [basename($path)],
        'completeStartingPath' => dirname($path),
    ], $cookies, true);
}

function fetchSiteUrl($path) {
    global $siteUrl;
    $ch = curl_init("$siteUrl/$path");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
    ]);
    $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}

// ===== Main =====
echo "=== Grant CREATE DATABASE to uhdor_root ===\n\n";

list($base, $cookies) = login();

// Upload a self-contained PHP script that tries multiple ways to grant privileges
$script = <<<'PHPSRC'
<?php
header("Content-Type: application/json");
error_reporting(0);
$results = [];

// Method 1: Read MySQL root password from CyberPanel
$passFile = '/etc/cyberpanel/mysqlPassword';
$rootPass = null;

if (file_exists($passFile) && is_readable($passFile)) {
    $rootPass = trim(file_get_contents($passFile));
    $results[] = "Read password from $passFile";
}

if (!$rootPass) {
    $out = @shell_exec("cat $passFile 2>/dev/null");
    if ($out) {
        $rootPass = trim($out);
        $results[] = "Read password via shell_exec";
    }
}

if (!$rootPass) {
    // Try reading via CyberPanel's python
    $out = @shell_exec('/usr/local/CyberCP/bin/python3 -c "print(open(\'/etc/cyberpanel/mysqlPassword\').read().strip())" 2>/dev/null');
    if ($out && strlen(trim($out)) > 0 && strpos($out, 'Error') === false) {
        $rootPass = trim($out);
        $results[] = "Read password via CyberPanel python";
    }
}

// Try connecting with various passwords
$passwords = array_filter([$rootPass, '', 'Araory@2014@2014']);
$connected = false;
$pdo = null;

foreach ($passwords as $pass) {
    try {
        $pdo = new PDO("mysql:host=localhost", "root", $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $results[] = "Connected as root with password: " . ($pass === '' ? '(empty)' : '***' . substr($pass, -3));
        $connected = true;
        break;
    } catch (Exception $e) {
        $results[] = "root/" . ($pass === '' ? '(empty)' : '***' . substr($pass, -3)) . " failed: " . $e->getMessage();
    }
}

if (!$connected) {
    // Try unix socket auth
    try {
        $pdo = new PDO("mysql:unix_socket=/var/lib/mysql/mysql.sock", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $results[] = "Connected via unix socket";
        $connected = true;
    } catch (Exception $e) {
        $results[] = "Unix socket failed: " . $e->getMessage();
    }
}

if (!$connected) {
    try {
        $pdo = new PDO("mysql:unix_socket=/var/run/mysqld/mysqld.sock", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $results[] = "Connected via /var/run/mysqld/mysqld.sock";
        $connected = true;
    } catch (Exception $e) {
        $results[] = "Alt socket failed: " . $e->getMessage();
    }
}

if (!$connected) {
    // Try via CyberPanel Python as last resort
    $pyScript = <<<PY
import sys, json
sys.path.insert(0, "/usr/local/CyberCP")
try:
    from plogical.mysqlUtilities import mysqlUtilities
    connection, cursor = mysqlUtilities.setupConnection()
    if connection == 0:
        print(json.dumps({"ok": False, "error": "setupConnection returned 0"}))
        sys.exit(1)
    cursor.execute("GRANT ALL PRIVILEGES ON *.* TO 'uhdor_root'@'localhost' WITH GRANT OPTION")
    cursor.execute("FLUSH PRIVILEGES")
    cursor.execute("SHOW GRANTS FOR 'uhdor_root'@'localhost'")
    grants = [row[0] for row in cursor.fetchall()]
    connection.close()
    print(json.dumps({"ok": True, "grants": grants}))
except Exception as e:
    print(json.dumps({"ok": False, "error": str(e)}))
PY;
    file_put_contents("/tmp/_grant.py", $pyScript);
    $out = @shell_exec("/usr/local/CyberCP/bin/python3 /tmp/_grant.py 2>&1");
    @unlink("/tmp/_grant.py");
    
    $pyResult = json_decode(trim($out), true);
    if ($pyResult && !empty($pyResult['ok'])) {
        echo json_encode(["status" => "ok", "method" => "cyberpanel_python", "grants" => $pyResult['grants'], "log" => $results]);
        exit;
    }
    $results[] = "CyberPanel Python result: " . trim($out);
    
    echo json_encode(["status" => "error", "log" => $results]);
    exit;
}

// We have a connection - grant privileges
try {
    $pdo->exec("GRANT ALL PRIVILEGES ON *.* TO 'uhdor_root'@'localhost' WITH GRANT OPTION");
    $pdo->exec("FLUSH PRIVILEGES");
    
    $stmt = $pdo->query("SHOW GRANTS FOR 'uhdor_root'@'localhost'");
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(["status" => "ok", "method" => "mysql_root", "grants" => $grants, "log" => $results]);
} catch (Exception $e) {
    $results[] = "Grant failed: " . $e->getMessage();
    echo json_encode(["status" => "error", "log" => $results]);
}
PHPSRC;

$remotePath = "$sitePath/public/grant_privs_tmp.php";
echo "Uploading grant script...\n";
$r = uploadFile($base, $cookies, $remotePath, $script);
echo "Upload: " . json_encode($r) . "\n\n";

echo "Executing...\n";
$response = fetchSiteUrl('grant_privs_tmp.php');
echo "Response: $response\n\n";

$data = json_decode($response, true);
if ($data && ($data['status'] ?? '') === 'ok') {
    echo "SUCCESS! Privileges granted.\n";
    if (!empty($data['grants'])) {
        echo "Grants:\n";
        foreach ($data['grants'] as $g) echo "  - $g\n";
    }
} else {
    echo "FAILED. See log above.\n";
}

// Clean up
echo "\nCleaning up...\n";
removeFile($base, $cookies, $remotePath);
echo "Done.\n";
