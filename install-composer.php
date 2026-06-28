<?php

/**
 * Composer Installer for cPanel (no SSH required)
 * Upload this file to your project directory and access via browser
 */

// Security: Remove this file after installation
define('ALLOWED_IPS', ['127.0.0.1', '::1']); // Add your IP if needed

// Check if accessed via web
if (php_sapi_name() !== 'cli') {
    // Simple IP check (remove for production)
    if (!in_array($_SERVER['REMOTE_ADDR'], ALLOWED_IPS)) {
        die('Access denied. Please add your IP to ALLOWED_IPS.');
    }
}

echo "<h1>🎵 Composer Dependencies Installer</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
</style>";

// Check if composer.json exists
if (!file_exists('composer.json')) {
    echo "<p class='error'>❌ composer.json not found in current directory</p>";
    echo "<p>Current directory: " . __DIR__ . "</p>";
    exit;
}

echo "<p class='success'>✅ composer.json found</p>";

// Check if composer is available
echo "<h2>🔍 Checking Composer Availability</h2>";

// Try different composer paths
$composerPaths = [
    '/usr/local/bin/composer',
    '/opt/cpanel/composer/bin/composer',
    'composer',
    '/usr/bin/composer'
];

$composerPath = null;
foreach ($composerPaths as $path) {
    if (is_executable($path) || (shell_exec("which $path") !== '')) {
        $composerPath = $path;
        break;
    }
}

if (!$composerPath) {
    echo "<p class='error'>❌ Composer not found</p>";
    echo "<p>Please install Composer or ask your hosting provider</p>";
    
    // Try to download composer.phar
    echo "<h2>📥 Downloading Composer</h2>";
    $composerPhar = 'composer.phar';
    if (!file_exists($composerPhar)) {
        echo "<p class='info'>📥 Downloading composer.phar...</p>";
        $composerInstall = shell_exec("curl -sS https://getcomposer.org/installer | php");
        echo "<pre>$composerInstall</pre>";
    }
    
    if (file_exists($composerPhar)) {
        $composerPath = 'php composer.phar';
        echo "<p class='success'>✅ Using local composer.phar</p>";
    } else {
        echo "<p class='error'>❌ Failed to download Composer</p>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Composer found: $composerPath</p>";
}

// Install dependencies
echo "<h2>📦 Installing Dependencies</h2>";
echo "<p>Running: $composerPath install --no-dev --optimize-autoloader</p>";

$command = "$composerPath install --no-dev --optimize-autoloader --no-interaction 2>&1";
$output = shell_exec($command);

echo "<h3>Output:</h3>";
echo "<pre>$output</pre>";

// Check results
if (file_exists('vendor/autoload.php')) {
    echo "<p class='success'>✅ Installation successful!</p>";
    echo "<p>Vendor folder size: " . formatBytes(directorySize('vendor')) . "</p>";
    
    // Show next steps
    echo "<h2>🎯 Next Steps</h2>";
    echo "<ol>";
    echo "<li>Copy .env.production to .env: <code>cp .env.production .env</code></li>";
    echo "<li>Generate app key: <code>php artisan key:generate</code></li>";
    echo "<li>Update database credentials in .env</li>";
    echo "<li>Run migrations: <code>php artisan migrate --force</code></li>";
    echo "<li>Create storage link: <code>php artisan storage:link</code></li>";
    echo "<li>Delete this file for security!</li>";
    echo "</ol>";
    
    // Generate commands for easy copy-paste
    echo "<h2>📋 Quick Commands</h2>";
    echo "<textarea rows='8' style='width: 100%;'>";
    echo "cp .env.production .env\n";
    echo "php artisan key:generate\n";
    echo "php artisan migrate --force\n";
    echo "php artisan storage:link\n";
    echo "php artisan config:cache\n";
    echo "php artisan route:cache\n";
    echo "php artisan view:cache\n";
    echo "rm install-composer.php  # Delete this file!";
    echo "</textarea>";
    
} else {
    echo "<p class='error'>❌ Installation failed!</p>";
    echo "<p>Please check the output above for errors</p>";
}

// Helper functions
function directorySize($path) {
    $totalSize = 0;
    $files = scandir($path);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $path . '/' . $file;
        if (is_dir($filePath)) {
            $totalSize += directorySize($filePath);
        } else {
            $totalSize += filesize($filePath);
        }
    }
    
    return $totalSize;
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

echo "<hr>";
echo "<p><small>⚠️  Remember to delete this file after installation for security!</small></p>";
?>
