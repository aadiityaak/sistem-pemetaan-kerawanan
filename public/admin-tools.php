<?php

/**
 * Emergency Admin Tools - Refactored
 *
 * This file provides basic admin functions when the main application is not accessible.
 * Access: http://yoursite.com/admin-tools.php
 *
 * Security: This file should be removed or secured in production.
 * You can add HTTP basic auth or IP restrictions for additional security.
 */

// Security check - only allow admin access
session_start();

// Dynamically get password from Laravel .env file
function getAdminPasswordFromEnv()
{
    // Get the Laravel root directory
    // admin-tools.php is in public_html, laravel-app is at /../laravel-app (one level up)
    $currentDir = __DIR__;
    $possiblePaths = [
        // Priority 1: Standard structure - laravel-app one level up from public_html
        dirname($currentDir).'/laravel-app',
        // Priority 2: Alternative hosting structures
        dirname(dirname($currentDir)).'/laravel-app',
        dirname(dirname(dirname($currentDir))).'/laravel-app',
        // Priority 3: Development structure
        dirname(__DIR__),
        // Priority 4: Same level (unlikely but possible)
        $currentDir.'/laravel-app',
    ];

    $laravelRoot = null;
    foreach ($possiblePaths as $path) {
        if (is_dir($path) && file_exists($path.'/artisan')) {
            $laravelRoot = $path;
            break;
        }
    }

    if (! $laravelRoot) {
        return 'admin123'; // Fallback if Laravel not found
    }

    $envPath = $laravelRoot.'/.env';
    if (! file_exists($envPath)) {
        return 'admin123'; // Fallback if .env not found
    }

    $envContent = file_get_contents($envPath);

    // Extract DB_PASSWORD from .env
    if (preg_match('/^DB_PASSWORD=(.*)$/m', $envContent, $matches)) {
        $password = trim($matches[1], '"\'');

        return ! empty($password) ? $password : 'admin123';
    }

    return 'admin123'; // Fallback if DB_PASSWORD not found
}

$admin_password = getAdminPasswordFromEnv();

if (! isset($_SESSION['admin_authenticated'])) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_authenticated'] = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Get Laravel root directory
function getLaravelRoot()
{
    static $laravelRoot = null;

    if ($laravelRoot !== null) {
        return $laravelRoot;
    }

    $currentDir = __DIR__;
    $possiblePaths = [
        dirname($currentDir).'/laravel-app',
        dirname(dirname($currentDir)).'/laravel-app',
        dirname(dirname(dirname($currentDir))).'/laravel-app',
        dirname(__DIR__),
        $currentDir.'/laravel-app',
    ];

    foreach ($possiblePaths as $path) {
        if (is_dir($path) && file_exists($path.'/artisan')) {
            $laravelRoot = $path;

            return $laravelRoot;
        }
    }

    $laravelRoot = dirname($currentDir).'/laravel-app'; // Default assumption

    return $laravelRoot;
}

function executeCommand($command)
{
    // Check if shell_exec is available
    if (! function_exists('shell_exec')) {
        return 'Error: shell_exec function is disabled on this server';
    }

    // Add full path to PHP if needed
    if (strpos($command, 'php ') === 0) {
        // Try multiple methods to find PHP executable
        $phpPath = null;

        // Method 1: Use PHP_BINARY constant (most reliable)
        if (defined('PHP_BINARY') && is_executable(PHP_BINARY)) {
            $phpPath = PHP_BINARY;
        }
        // Method 2: Try common shared hosting paths
        elseif (is_executable('/usr/bin/php')) {
            $phpPath = '/usr/bin/php';
        } elseif (is_executable('/usr/local/bin/php')) {
            $phpPath = '/usr/local/bin/php';
        } elseif (is_executable('/opt/cpanel/ea-php81/root/usr/bin/php')) {
            $phpPath = '/opt/cpanel/ea-php81/root/usr/bin/php';
        } elseif (is_executable('/opt/cpanel/ea-php82/root/usr/bin/php')) {
            $phpPath = '/opt/cpanel/ea-php82/root/usr/bin/php';
        } elseif (is_executable('/opt/cpanel/ea-php83/root/usr/bin/php')) {
            $phpPath = '/opt/cpanel/ea-php83/root/usr/bin/php';
        } elseif (is_executable('/opt/cpanel/ea-php84/root/usr/bin/php')) {
            $phpPath = '/opt/cpanel/ea-php84/root/usr/bin/php';
        }
        // Method 3: Try to use 'php' directly (might work on some hosting)
        else {
            // Test if 'php' command works directly
            $testResult = @shell_exec('php --version 2>/dev/null');
            if ($testResult && strpos($testResult, 'PHP') !== false) {
                $phpPath = 'php';
            }
        }

        // Method 4: Use which/where command (if available)
        if (! $phpPath) {
            $which = trim(@shell_exec('which php 2>/dev/null') ?: '');
            if ($which && is_executable($which)) {
                $phpPath = $which;
            }
        }

        // Method 5: Try common hosting-specific paths
        if (! $phpPath) {
            $commonPaths = [
                '/usr/local/php83/bin/php',    // PHP 8.3 prioritized
                '/usr/local/php84/bin/php',    // PHP 8.4 for future
                '/usr/local/php82/bin/php',
                '/usr/local/php81/bin/php',
                '/usr/local/lsws/lsphp83/bin/php',  // LiteSpeed PHP 8.3
                '/usr/local/lsws/lsphp84/bin/php',  // LiteSpeed PHP 8.4
                '/usr/local/lsws/lsphp82/bin/php',
                '/usr/local/lsws/lsphp81/bin/php',
                '/home/'.get_current_user().'/public_html/cgi-bin/php83',
                '/home/'.get_current_user().'/public_html/cgi-bin/php',
                '/usr/local/bin/php83',
                '/usr/bin/php83',
            ];

            foreach ($commonPaths as $path) {
                if (is_executable($path)) {
                    $phpPath = $path;
                    break;
                }
            }
        }

        if ($phpPath) {
            // Only escape if it's a full path (contains /)
            if (strpos($phpPath, '/') !== false) {
                $command = str_replace('php ', escapeshellarg($phpPath).' ', $command);
            } else {
                $command = str_replace('php ', $phpPath.' ', $command);
            }
        } else {
            // Last resort: try without path (some hosting allows this)
            $debugInfo = "PHP Detection Debug:\n";
            $debugInfo .= 'PHP_BINARY: '.(defined('PHP_BINARY') ? PHP_BINARY : 'Not defined')."\n";
            $debugInfo .= 'PHP_BINARY executable: '.(defined('PHP_BINARY') && is_executable(PHP_BINARY) ? 'Yes' : 'No')."\n";
            $debugInfo .= 'Current user: '.get_current_user()."\n";
            $debugInfo .= 'Server software: '.($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown')."\n";
            $debugInfo .= 'PHP SAPI: '.php_sapi_name()."\n";

            return 'Error: Could not find PHP executable. '.$debugInfo.
                'Contact your hosting provider for the correct PHP path.';
        }
    }

    $output = shell_exec($command.' 2>&1');

    return $output ?: 'Command executed (no output)';
}

// Format bytes to human readable
function formatBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision).' '.$units[$i];
}

// Get directory size
function getDirSize($dir)
{
    if (! is_dir($dir)) {
        return 0;
    }

    $size = 0;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($files as $file) {
        $size += $file->getSize();
    }

    return $size;
}

// Remove directory recursively
function removeDirectory($dir)
{
    if (! is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir.'/'.$file;
        if (is_dir($path)) {
            removeDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

// Define admin tool actions with grouping
$toolGroups = [
    'cache' => [
        'title' => 'Cache Management',
        'description' => 'Clear and manage application cache',
        'actions' => [
            'clear_cache' => [
                'label' => 'Clear All Cache',
                'description' => 'Clear cache, config, routes, and views',
                'variant' => 'primary',
                'commands' => [
                    'php artisan cache:clear',
                    'php artisan config:clear',
                    'php artisan route:clear',
                    'php artisan view:clear',
                ],
            ],
            'optimize_clear' => [
                'label' => 'Clear Optimization',
                'description' => 'Clear all optimized files',
                'variant' => 'secondary',
                'commands' => ['php artisan optimize:clear'],
            ],
        ],
    ],
    'optimization' => [
        'title' => 'Application Optimization',
        'description' => 'Optimize application performance',
        'actions' => [
            'optimize' => [
                'label' => 'Optimize App',
                'description' => 'Optimize routes, config, and views',
                'variant' => 'success',
                'commands' => ['php artisan optimize'],
            ],
            'config_cache' => [
                'label' => 'Cache Config',
                'description' => 'Cache configuration files',
                'variant' => 'secondary',
                'commands' => ['php artisan config:cache'],
            ],
            'route_cache' => [
                'label' => 'Cache Routes',
                'description' => 'Cache route definitions',
                'variant' => 'secondary',
                'commands' => ['php artisan route:cache'],
            ],
        ],
    ],
    'storage' => [
        'title' => 'Storage Management',
        'description' => 'Manage storage links and permissions',
        'actions' => [
            'storage_link' => [
                'label' => 'Create Storage Link',
                'description' => 'Create symbolic link for storage',
                'variant' => 'primary',
                'custom' => 'handleStorageLink',
            ],
            'fix_storage_permissions' => [
                'label' => 'Fix Storage Permissions',
                'description' => 'Set proper storage permissions',
                'variant' => 'warning',
                'custom' => 'handleStoragePermissions',
            ],
            'clear_logs' => [
                'label' => 'Clear Log Files',
                'description' => 'Delete all log files',
                'variant' => 'warning',
                'custom' => 'handleClearLogs',
            ],
        ],
    ],
    'database' => [
        'title' => 'Database Operations',
        'description' => 'Database migrations and seeding',
        'actions' => [
            'migrate' => [
                'label' => 'Run Migrations',
                'description' => 'Execute database migrations',
                'variant' => 'primary',
                'commands' => ['php artisan migrate --force'],
            ],
            'db_seed' => [
                'label' => 'Run Database Seeder',
                'description' => 'Seed database with sample data',
                'variant' => 'success',
                'commands' => ['php artisan db:seed --force'],
            ],
            'migrate_fresh' => [
                'label' => 'Fresh Migration',
                'description' => 'Drop all tables and re-migrate',
                'variant' => 'destructive',
                'commands' => ['php artisan migrate:fresh --force'],
                'confirm' => 'This will delete all data. Are you sure?',
            ],
        ],
    ],
    'maintenance' => [
        'title' => 'Maintenance Mode',
        'description' => 'Control application maintenance mode',
        'actions' => [
            'maintenance_down' => [
                'label' => 'Enable Maintenance',
                'description' => 'Put application in maintenance mode',
                'variant' => 'warning',
                'commands' => ['php artisan down --secret=admin-secret'],
            ],
            'maintenance_up' => [
                'label' => 'Disable Maintenance',
                'description' => 'Bring application back online',
                'variant' => 'success',
                'commands' => ['php artisan up'],
            ],
        ],
    ],
    'security' => [
        'title' => 'Security',
        'description' => 'Security and key management',
        'actions' => [
            'key_generate' => [
                'label' => 'Generate App Key',
                'description' => 'Generate new application encryption key',
                'variant' => 'destructive',
                'commands' => ['php artisan key:generate --force'],
                'confirm' => 'This will generate a new APP_KEY. Continue?',
            ],
        ],
    ],
    'environment' => [
        'title' => 'Environment',
        'description' => 'Environment file management',
        'actions' => [
            'check_env' => [
                'label' => 'Check .env File',
                'description' => 'Validate environment configuration',
                'variant' => 'secondary',
                'custom' => 'handleCheckEnv',
            ],
            'show_env' => [
                'label' => 'Show .env Content',
                'description' => 'Display environment file (masked)',
                'variant' => 'secondary',
                'custom' => 'handleShowEnv',
            ],
            'backup_env' => [
                'label' => 'Backup .env File',
                'description' => 'Create backup of environment file',
                'variant' => 'warning',
                'custom' => 'handleBackupEnv',
            ],
        ],
    ],
    'diagnostics' => [
        'title' => 'System Diagnostics',
        'description' => 'System health and debugging tools',
        'actions' => [
            'health_check' => [
                'label' => 'System Health Check',
                'description' => 'Complete system health diagnosis',
                'variant' => 'primary',
                'custom' => 'handleHealthCheck',
            ],
            'debug_500_error' => [
                'label' => 'Debug 500 Error',
                'description' => 'Diagnose HTTP 500 errors',
                'variant' => 'destructive',
                'custom' => 'handleDebug500',
            ],
            'debug_hosting_structure' => [
                'label' => 'Debug Hosting Structure',
                'description' => 'Analyze hosting directory structure',
                'variant' => 'destructive',
                'custom' => 'handleDebugHosting',
            ],
            'disk_space' => [
                'label' => 'Disk Space Usage',
                'description' => 'Check disk space and file sizes',
                'variant' => 'secondary',
                'custom' => 'handleDiskSpace',
            ],
        ],
    ],
];

$output = '';
$error = '';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'logout') {
        session_destroy();
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }

    try {
        $laravelRoot = getLaravelRoot();

        if (! is_dir($laravelRoot) || ! file_exists($laravelRoot.'/artisan')) {
            throw new Exception('Laravel directory not found at: '.$laravelRoot);
        }

        chdir($laravelRoot);

        // Find the action in tool groups
        $actionFound = false;
        foreach ($toolGroups as $group) {
            if (isset($group['actions'][$action])) {
                $actionConfig = $group['actions'][$action];
                $actionFound = true;

                if (isset($actionConfig['commands'])) {
                    // Execute commands
                    $outputs = [];
                    foreach ($actionConfig['commands'] as $command) {
                        $outputs[] = executeCommand($command);
                    }
                    $output = implode("\n\n", $outputs);
                } elseif (isset($actionConfig['custom'])) {
                    // Execute custom handler
                    $handler = $actionConfig['custom'];
                    if (function_exists($handler)) {
                        $output = $handler();
                    } else {
                        throw new Exception("Custom handler not found: {$handler}");
                    }
                }
                break;
            }
        }

        if (! $actionFound) {
            throw new Exception("Unknown action: {$action}");
        }

    } catch (Exception $e) {
        $error = 'Error: '.$e->getMessage();
    }
}

// Custom action handlers
function handleStorageLink()
{
    $laravelRoot = getLaravelRoot();
    $currentDir = dirname($laravelRoot); // Parent of laravel-app
    $publicDir = $currentDir.'/public_html'; // Assuming standard structure

    $storagePath = $publicDir.'/storage';
    $storageTarget = $laravelRoot.'/storage/app/public';

    $output = "Storage Link Management:\n";
    $output .= "Public Directory: {$publicDir}\n";
    $output .= "Storage Link Path: {$storagePath}\n";
    $output .= "Storage Target: {$storageTarget}\n";
    $output .= 'Target exists: '.(is_dir($storageTarget) ? 'Yes' : 'No')."\n";
    $output .= 'Link exists: '.(file_exists($storagePath) ? 'Yes' : 'No')."\n\n";

    if (file_exists($storagePath)) {
        if (is_link($storagePath)) {
            $target = readlink($storagePath);
            $output .= "Current link target: {$target}\n";
            if ($target !== $storageTarget) {
                $output .= "Recreating link with correct target...\n";
                unlink($storagePath);
                if (symlink($storageTarget, $storagePath)) {
                    $output .= "✅ Link recreated successfully!\n";
                } else {
                    $output .= "❌ Failed to recreate link\n";
                }
            } else {
                $output .= "✅ Link is correct\n";
            }
        } else {
            $output .= "⚠️ Storage exists but is not a symlink\n";
        }
    } else {
        if (is_dir($storageTarget)) {
            $output .= "Creating new storage link...\n";
            if (symlink($storageTarget, $storagePath)) {
                $output .= "✅ Storage link created successfully!\n";
            } else {
                $output .= "❌ Failed to create storage link\n";
            }
        } else {
            $output .= "❌ Target directory does not exist\n";
        }
    }

    return $output;
}

function handleStoragePermissions()
{
    $laravelRoot = getLaravelRoot();
    $storageDir = $laravelRoot.'/storage';

    $output = "Fixing Storage Permissions:\n";

    if (! is_dir($storageDir)) {
        return "❌ Storage directory not found: {$storageDir}";
    }

    $dirs = ['storage', 'storage/app', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
    $fixed = 0;

    foreach ($dirs as $dir) {
        $path = $laravelRoot.'/'.$dir;
        if (is_dir($path)) {
            if (chmod($path, 0755)) {
                $output .= "✅ Fixed permissions for {$dir}\n";
                $fixed++;
            } else {
                $output .= "❌ Failed to fix permissions for {$dir}\n";
            }
        }
    }

    $output .= "\n✅ Fixed permissions for {$fixed} directories\n";

    return $output;
}

function handleClearLogs()
{
    $laravelRoot = getLaravelRoot();
    $logPath = $laravelRoot.'/storage/logs';

    if (! is_dir($logPath)) {
        return 'Log directory not found';
    }

    $files = glob($logPath.'/*.log');
    $count = 0;
    foreach ($files as $file) {
        if (unlink($file)) {
            $count++;
        }
    }

    return "Deleted {$count} log files";
}

function handleCheckEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';
    $envExamplePath = $laravelRoot.'/.env.example';

    $output = "Environment File Check:\n";
    $output .= '.env exists: '.(file_exists($envPath) ? 'Yes' : 'No')."\n";
    $output .= '.env.example exists: '.(file_exists($envExamplePath) ? 'Yes' : 'No')."\n";

    if (file_exists($envPath)) {
        $envSize = filesize($envPath);
        $output .= '.env size: '.$envSize." bytes\n";
        $output .= '.env modified: '.date('Y-m-d H:i:s', filemtime($envPath))."\n";

        $envContent = file_get_contents($envPath);
        $requiredVars = ['APP_KEY', 'DB_CONNECTION', 'DB_DATABASE'];
        foreach ($requiredVars as $var) {
            $exists = strpos($envContent, $var.'=') !== false;
            $output .= "{$var}: ".($exists ? 'Set' : 'Missing')."\n";
        }
    }

    return $output;
}

function handleShowEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';

    if (! file_exists($envPath)) {
        return '❌ Environment file not found';
    }

    $envContent = file_get_contents($envPath);
    // Mask sensitive values
    $maskedContent = preg_replace('/(APP_KEY|DB_PASSWORD|.*_SECRET|.*_TOKEN|.*_KEY)=(.+)/i', '$1=***MASKED***', $envContent);

    return "Environment File Content (sensitive values masked):\n\n".$maskedContent;
}

function handleBackupEnv()
{
    $laravelRoot = getLaravelRoot();
    $envPath = $laravelRoot.'/.env';
    $backupPath = $laravelRoot.'/.env.backup.'.date('Y-m-d_H-i-s');

    if (! file_exists($envPath)) {
        return '❌ Environment file not found';
    }

    if (copy($envPath, $backupPath)) {
        return '✅ Environment file backed up to: '.basename($backupPath);
    } else {
        return '❌ Failed to backup environment file';
    }
}

function handleHealthCheck()
{
    $laravelRoot = getLaravelRoot();

    $output = "System Health Check:\n\n";

    // PHP Info
    $output .= "🔧 PHP Information:\n";
    $output .= 'PHP Version: '.PHP_VERSION."\n";
    $output .= 'Memory Limit: '.ini_get('memory_limit')."\n";
    $output .= 'Max Execution Time: '.ini_get('max_execution_time')."s\n";
    $output .= 'Upload Max Size: '.ini_get('upload_max_filesize')."\n";

    // Extensions
    $output .= "\n🔌 Extensions:\n";
    $requiredExtensions = ['pdo', 'mbstring', 'tokenizer', 'json', 'openssl', 'curl'];
    foreach ($requiredExtensions as $ext) {
        $loaded = extension_loaded($ext);
        $output .= "{$ext}: ".($loaded ? '✅ Loaded' : '❌ Missing')."\n";
    }

    // Laravel Files
    $output .= "\n📁 Laravel Files:\n";
    $files = ['artisan', 'composer.json', '.env', 'bootstrap/app.php'];
    foreach ($files as $file) {
        $exists = file_exists($laravelRoot.'/'.$file);
        $output .= "{$file}: ".($exists ? '✅ Exists' : '❌ Missing')."\n";
    }

    // Directories
    $output .= "\n📂 Directories:\n";
    $dirs = ['storage', 'storage/app', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
    foreach ($dirs as $dir) {
        $path = $laravelRoot.'/'.$dir;
        $exists = is_dir($path);
        $writable = $exists ? is_writable($path) : false;
        $output .= "{$dir}: ".($exists ? '✅ Exists' : '❌ Missing').
            ($writable ? ' (Writable)' : ($exists ? ' (Not Writable)' : ''))."\n";
    }

    return $output;
}

function handleDebug500()
{
    $laravelRoot = getLaravelRoot();

    $output = "HTTP 500 Error Diagnostic:\n\n";

    // Check PHP version and extensions
    $output .= "1. PHP Environment:\n";
    $output .= '   Version: '.phpversion()."\n";
    $output .= '   SAPI: '.php_sapi_name()."\n";

    $requiredExtensions = ['openssl', 'pdo', 'mbstring', 'tokenizer', 'xml', 'ctype', 'json', 'curl'];
    $output .= "   Required Extensions:\n";
    foreach ($requiredExtensions as $ext) {
        $loaded = extension_loaded($ext);
        $output .= "   - $ext: ".($loaded ? '✅ Loaded' : '❌ Missing')."\n";
    }

    // Check critical files
    $output .= "\n2. Critical Files Check:\n";
    $criticalFiles = [
        '.env' => $laravelRoot.'/.env',
        'artisan' => $laravelRoot.'/artisan',
        'index.php' => dirname($laravelRoot).'/public_html/index.php',
        'composer.json' => $laravelRoot.'/composer.json',
    ];

    foreach ($criticalFiles as $name => $path) {
        $exists = file_exists($path);
        $readable = $exists && is_readable($path);
        $output .= "   - $name: ".($readable ? '✅ OK' : ($exists ? '⚠️ Not readable' : '❌ Missing'))."\n";
    }

    // Quick fixes
    $output .= "\n3. Quick Fix Recommendations:\n";
    $output .= "   1. Generate APP_KEY: Use 'Generate App Key' button\n";
    $output .= "   2. Fix Permissions: Use 'Fix Storage Permissions' button\n";
    $output .= "   3. Clear Cache: Use 'Clear All Cache' button\n";
    $output .= "   4. Check .env: Use 'Show .env Content' button\n";

    return $output;
}

function handleDebugHosting()
{
    $laravelRoot = getLaravelRoot();

    $output = "Hosting Structure Diagnostic:\n\n";

    // Current location info
    $output .= "1. Current Location:\n";
    $output .= '   Current Dir: '.__DIR__."\n";
    $output .= '   Document Root: '.($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown')."\n";

    // Path analysis
    $output .= "\n2. Laravel Root Detection:\n";
    $output .= "   Selected Laravel Root: {$laravelRoot}\n";
    $output .= '   Directory exists: '.(is_dir($laravelRoot) ? 'Yes' : 'No')."\n";
    $output .= '   Artisan exists: '.(file_exists($laravelRoot.'/artisan') ? 'Yes' : 'No')."\n";

    return $output;
}

function handleDiskSpace()
{
    $laravelRoot = getLaravelRoot();

    $output = "Disk Space Information:\n";

    // Laravel directory size
    $laravelSize = getDirSize($laravelRoot);
    $output .= 'Laravel directory: '.formatBytes($laravelSize)."\n";

    // Storage directory size
    $storageSize = getDirSize($laravelRoot.'/storage');
    $output .= 'Storage directory: '.formatBytes($storageSize)."\n";

    // Available disk space
    $freeSpace = disk_free_space($laravelRoot);
    $totalSpace = disk_total_space($laravelRoot);
    $usedSpace = $totalSpace - $freeSpace;

    $output .= "\nDisk Usage:\n";
    $output .= 'Used: '.formatBytes($usedSpace)."\n";
    $output .= 'Free: '.formatBytes($freeSpace)."\n";
    $output .= 'Total: '.formatBytes($totalSpace)."\n";
    $output .= 'Usage: '.round(($usedSpace / $totalSpace) * 100, 2)."%\n";

    return $output;
}

function showLoginForm()
{
    ?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Tools - Login</title>
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --muted: 210 40% 98%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --radius: 0.5rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            background: hsl(210 40% 98%);
            color: hsl(var(--foreground));
            line-height: 1.6;
            margin: 0;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: hsl(var(--background));
            border: 1px solid hsl(var(--border));
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            color: hsl(var(--foreground));
        }

        .login-header p {
            color: hsl(var(--muted-foreground));
            font-size: 0.875rem;
        }

        .alert {
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid;
        }

        .alert-warning {
            background: hsl(38 100% 97%);
            border-color: hsl(38 92% 50% / 0.2);
            color: hsl(38 92% 30%);
        }

        .alert-info {
            background: hsl(204 100% 97%);
            border-color: hsl(204 93% 85%);
            color: hsl(204 90% 30%);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: hsl(var(--foreground));
        }

        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid hsl(var(--border));
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.875rem;
            transition: border-color 0.2s, box-shadow 0.2s;
            background: hsl(var(--background));
        }

        .form-input:focus {
            outline: none;
            border-color: hsl(var(--primary));
            box-shadow: 0 0 0 3px hsl(var(--primary) / 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid transparent;
            padding: 0.75rem 1.5rem;
            width: 100%;
            cursor: pointer;
            background: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }

        .btn:hover {
            background: hsl(var(--primary) / 0.9);
        }

        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px hsl(var(--primary) / 0.2);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Tools</h1>
            <p>Emergency administration interface</p>
        </div>
        
        <div class="alert alert-warning">
            <strong>Warning:</strong> This is an emergency admin tool. Remove this file in production.
        </div>
        
        <div class="alert alert-info">
            <strong>Info:</strong> Password is automatically retrieved from Laravel .env DB_PASSWORD.<br>
            <small>Fallback to 'admin123' if .env file not found or DB_PASSWORD empty.</small>
        </div>
        
        <form method="post">
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="form-input" 
                    required 
                    autocomplete="current-password"
                >
            </div>
            <button type="submit" class="btn">Sign In</button>
        </form>
    </div>
</body>
</html>
<?php
}

$laravelRoot = getLaravelRoot();
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Admin Tools</title>
    <style>
        :root {
            --background: 0 0% 100%;
            --foreground: 222.2 84% 4.9%;
            --card: 0 0% 100%;
            --card-foreground: 222.2 84% 4.9%;
            --primary: 222.2 47.4% 11.2%;
            --primary-foreground: 210 40% 98%;
            --secondary: 210 40% 96%;
            --secondary-foreground: 222.2 47.4% 11.2%;
            --muted: 210 40% 98%;
            --muted-foreground: 215.4 16.3% 46.9%;
            --accent: 210 40% 96%;
            --accent-foreground: 222.2 47.4% 11.2%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 210 40% 98%;
            --warning: 38 92% 50%;
            --warning-foreground: 222.2 84% 4.9%;
            --success: 142 76% 36%;
            --success-foreground: 210 40% 98%;
            --border: 214.3 31.8% 91.4%;
            --input: 214.3 31.8% 91.4%;
            --ring: 222.2 84% 4.9%;
            --radius: 0.5rem;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
            background: hsl(210 40% 98%);
            color: hsl(var(--foreground));
            line-height: 1.6;
            margin: 0;
            padding: 1.5rem;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: hsl(var(--background));
            border-radius: 0.75rem;
            border: 1px solid hsl(var(--border));
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            overflow: hidden;
        }

        .header {
            background: hsl(var(--card));
            border-bottom: 1px solid hsl(var(--border));
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: hsl(var(--foreground));
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }

        .header-content p {
            color: hsl(var(--muted-foreground));
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin: 1.5rem 2rem;
            padding: 1.5rem;
            background: hsl(var(--muted));
            border-radius: var(--radius);
            border: 1px solid hsl(var(--border));
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
        }

        .info-label {
            font-weight: 500;
            color: hsl(var(--foreground));
        }

        .info-value {
            color: hsl(var(--muted-foreground));
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 0.8rem;
        }

        .content {
            padding: 2rem;
        }

        .alert {
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid;
        }

        .alert-warning {
            background: hsl(38 100% 97%);
            border-color: hsl(38 92% 50% / 0.2);
            color: hsl(38 92% 30%);
        }

        .alert-success {
            background: hsl(143 85% 96%);
            border-color: hsl(145 92% 91%);
            color: hsl(140 100% 27%);
        }

        .alert-error {
            background: hsl(0 93% 94%);
            border-color: hsl(0 84% 60% / 0.2);
            color: hsl(0 74% 42%);
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
            gap: 1.5rem;
        }

        .tool-group {
            background: hsl(var(--card));
            border: 1px solid hsl(var(--border));
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        }

        .tool-group-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid hsl(var(--border));
        }

        .tool-group-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: hsl(var(--foreground));
            margin: 0 0 0.5rem 0;
        }

        .tool-group-description {
            font-size: 0.875rem;
            color: hsl(var(--muted-foreground));
            margin: 0;
        }

        .tool-group-content {
            padding: 1.5rem;
        }

        .tool-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border-radius: calc(var(--radius) - 2px);
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            border: 1px solid transparent;
            padding: 0.75rem 1rem;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-primary {
            background: hsl(var(--primary));
            color: hsl(var(--primary-foreground));
        }

        .btn-primary:hover:not(:disabled) {
            background: hsl(var(--primary) / 0.9);
        }

        .btn-secondary {
            background: hsl(var(--secondary));
            color: hsl(var(--secondary-foreground));
        }

        .btn-secondary:hover:not(:disabled) {
            background: hsl(var(--secondary) / 0.8);
        }

        .btn-success {
            background: hsl(var(--success));
            color: hsl(var(--success-foreground));
        }

        .btn-success:hover:not(:disabled) {
            background: hsl(var(--success) / 0.9);
        }

        .btn-warning {
            background: hsl(var(--warning));
            color: hsl(var(--warning-foreground));
        }

        .btn-warning:hover:not(:disabled) {
            background: hsl(var(--warning) / 0.9);
        }

        .btn-destructive {
            background: hsl(var(--destructive));
            color: hsl(var(--destructive-foreground));
        }

        .btn-destructive:hover:not(:disabled) {
            background: hsl(var(--destructive) / 0.9);
        }

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .output {
            background: hsl(var(--muted));
            border: 1px solid hsl(var(--border));
            padding: 1rem;
            border-radius: var(--radius);
            margin: 1.5rem 0;
            white-space: pre-wrap;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 0.8rem;
            line-height: 1.5;
            max-height: 400px;
            overflow-y: auto;
        }

        .footer {
            margin-top: 3rem;
            padding: 2rem;
            border-top: 1px solid hsl(var(--border));
            background: hsl(var(--muted) / 0.5);
            font-size: 0.8rem;
            color: hsl(var(--muted-foreground));
            line-height: 1.6;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer strong {
            color: hsl(var(--foreground));
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .header {
                padding: 1.5rem;
                flex-direction: column;
                align-items: stretch;
            }

            .content {
                padding: 1.5rem;
            }

            .system-info {
                grid-template-columns: 1fr;
                margin: 1rem;
            }

            .tools-grid {
                grid-template-columns: 1fr;
            }

            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1>Emergency Admin Tools</h1>
                <p>Laravel application management interface</p>
            </div>
            <div class="header-actions">
                <form method="post" style="margin: 0;">
                    <button type="submit" name="action" value="logout" class="btn btn-destructive btn-sm">
                        Sign Out
                    </button>
                </form>
            </div>
        </header>

        <div class="system-info">
            <div class="info-item">
                <span class="info-label">PHP Version</span>
                <span class="info-value"><?php echo PHP_VERSION; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Laravel Root</span>
                <span class="info-value"><?php echo $laravelRoot; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Environment</span>
                <span class="info-value"><?php echo file_exists($laravelRoot.'/.env') ? 'File exists' : 'Missing'; ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Storage Link</span>
                <span class="info-value"><?php echo file_exists(dirname($laravelRoot).'/public_html/storage') ? 'Exists' : 'Missing'; ?></span>
            </div>
        </div>

        <div class="content">
            <div class="alert alert-warning">
                <strong>Warning:</strong> This is an emergency administration tool. Remove this file in production or restrict access via IP/authentication.
            </div>

            <?php if ($error) { ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php } ?>

            <?php if ($output) { ?>
                <div class="alert alert-success">Command executed successfully!</div>
                <div class="output"><?php echo htmlspecialchars($output); ?></div>
            <?php } ?>

            <div class="tools-grid">
                <?php foreach ($toolGroups as $groupKey => $group) { ?>
                <div class="tool-group">
                    <div class="tool-group-header">
                        <h3 class="tool-group-title"><?php echo htmlspecialchars($group['title']); ?></h3>
                        <p class="tool-group-description"><?php echo htmlspecialchars($group['description']); ?></p>
                    </div>
                    <div class="tool-group-content">
                        <div class="tool-actions">
                            <?php foreach ($group['actions'] as $actionKey => $action) { ?>
                                <form method="post" style="margin: 0;">
                                    <button 
                                        type="submit" 
                                        name="action" 
                                        value="<?php echo htmlspecialchars($actionKey); ?>" 
                                        class="btn btn-<?php echo htmlspecialchars($action['variant']); ?>"
                                        <?php if (isset($action['confirm'])) { ?>
                                            onclick="return confirm('<?php echo htmlspecialchars($action['confirm']); ?>')"
                                        <?php } ?>
                                        title="<?php echo htmlspecialchars($action['description']); ?>"
                                    >
                                        <?php echo htmlspecialchars($action['label']); ?>
                                    </button>
                                </form>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="footer">
                <p><strong>Emergency Access:</strong> If the main application is broken, you can use this tool to clear cache and fix common issues.</p>
                <p><strong>Security Note:</strong> This file should be removed or secured in production. Consider adding HTTP basic authentication or IP restrictions.</p>
                <p><strong>Laravel Root:</strong> <?php echo htmlspecialchars($laravelRoot); ?></p>
            </div>
        </div>
    </div>
</body>
</html>