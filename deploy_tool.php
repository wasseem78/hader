<?php
// =============================================================================
// Server Deployment Script
// Executes deployment commands: upload files, run migrations, clear caches
// DELETE THIS FILE AFTER USE - it allows remote command execution!
// =============================================================================

// Simple auth key to prevent unauthorized access
$AUTH_KEY = 'deploy_8f3k2j5h9x_2026';

if (($_GET['key'] ?? '') !== $AUTH_KEY) {
    http_response_code(403);
    die('Forbidden');
}

$action = $_GET['action'] ?? 'status';
header('Content-Type: text/plain; charset=utf-8');

switch ($action) {
    case 'status':
        echo "=== Server Status ===\n";
        echo "PHP: " . PHP_VERSION . "\n";
        echo "Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'unknown') . "\n";
        echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "\n";
        echo "Script: " . __FILE__ . "\n";
        echo "CWD: " . getcwd() . "\n";
        echo "User: " . get_current_user() . " / " . (function_exists('posix_getuid') ? posix_getuid() : 'N/A') . "\n";
        echo "Time: " . date('Y-m-d H:i:s') . "\n";
        
        // Check if artisan exists relative to this script
        $artisanPath = __DIR__ . '/artisan';
        echo "Artisan exists: " . (file_exists($artisanPath) ? 'YES' : 'NO') . "\n";
        echo "Artisan dir: " . __DIR__ . "\n";
        
        // List key directories
        echo "\n=== Directory Listing ===\n";
        $items = scandir(__DIR__);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = __DIR__ . '/' . $item;
            $type = is_dir($path) ? 'DIR' : 'FILE';
            $size = is_file($path) ? filesize($path) : '-';
            echo sprintf("%-6s %10s  %s\n", $type, $size, $item);
        }
        break;

    case 'artisan':
        $cmd = $_GET['cmd'] ?? 'list';
        $artisan = __DIR__ . '/artisan';
        if (!file_exists($artisan)) {
            die("Artisan not found at: $artisan");
        }
        $fullCmd = "cd " . escapeshellarg(__DIR__) . " && php artisan " . $cmd . " 2>&1";
        echo "Running: php artisan $cmd\n";
        echo str_repeat('-', 60) . "\n";
        echo shell_exec($fullCmd);
        break;

    case 'shell':
        $cmd = $_GET['cmd'] ?? 'whoami';
        $fullCmd = "cd " . escapeshellarg(__DIR__) . " && " . $cmd . " 2>&1";
        echo "Running: $cmd\n";
        echo str_repeat('-', 60) . "\n";
        echo shell_exec($fullCmd);
        break;

    case 'upload':
        // Receive file content via POST and write to specified path
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die('POST required for upload');
        }
        $targetPath = $_GET['path'] ?? '';
        if (empty($targetPath)) {
            die('No path specified');
        }
        
        // Make path relative to project root
        $fullPath = __DIR__ . '/' . ltrim($targetPath, '/');
        
        // Create directory if needed
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Get content from POST body
        $content = file_get_contents('php://input');
        
        // Write file
        $bytes = file_put_contents($fullPath, $content);
        if ($bytes === false) {
            http_response_code(500);
            echo "FAILED to write: $fullPath";
        } else {
            echo "OK: Written $bytes bytes to $fullPath";
        }
        break;

    case 'read':
        $targetPath = $_GET['path'] ?? '';
        if (empty($targetPath)) {
            die('No path specified');
        }
        $fullPath = __DIR__ . '/' . ltrim($targetPath, '/');
        if (!file_exists($fullPath)) {
            http_response_code(404);
            die("File not found: $fullPath");
        }
        echo file_get_contents($fullPath);
        break;

    default:
        echo "Unknown action: $action\n";
        echo "Available: status, artisan, shell, upload, read\n";
}
