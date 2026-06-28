<?php

/**
 * Webform VPN Production Health Check
 * Run this script to verify your deployment is working correctly
 */

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/plain; charset=utf-8');

echo "🔍 Webform VPN Production Health Check\n";
echo str_repeat("=", 50) . "\n\n";

// Check PHP version
echo "📋 PHP Version Check:\n";
$phpVersion = PHP_VERSION;
echo "   Current PHP Version: $phpVersion\n";
if (version_compare($phpVersion, '8.2.0', '>=')) {
    echo "   ✅ PHP version is supported\n";
} else {
    echo "   ❌ PHP version should be 8.2 or higher\n";
}
echo "\n";

// Check required PHP extensions
echo "📋 PHP Extensions Check:\n";
$requiredExtensions = [
    'pdo_mysql' => 'MySQL Database',
    'mbstring' => 'Multi-byte String',
    'openssl' => 'OpenSSL',
    'tokenizer' => 'Tokenizer',
    'xml' => 'XML',
    'ctype' => 'CType',
    'json' => 'JSON',
    'fileinfo' => 'File Info',
    'bcmath' => 'BCMath',
    'gd' => 'Image Processing (GD)'
];

$missingExtensions = [];
foreach ($requiredExtensions as $ext => $description) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext - $description\n";
    } else {
        echo "   ❌ $ext - $description (MISSING)\n";
        $missingExtensions[] = $ext;
    }
}
echo "\n";

// Check Laravel environment
echo "📋 Laravel Environment Check:\n";
if (file_exists('.env')) {
    echo "   ✅ .env file exists\n";
    
    // Check key variables
    $envContent = file_get_contents('.env');
    $requiredEnvVars = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_URL', 'DB_CONNECTION'];
    
    foreach ($requiredEnvVars as $var) {
        if (preg_match("/^$var=.+$/m", $envContent)) {
            echo "   ✅ $var is set\n";
        } else {
            echo "   ❌ $var is not set\n";
        }
    }
    
    // Check if debug is off in production
    if (preg_match("/APP_ENV=production/", $envContent)) {
        if (preg_match("/APP_DEBUG=false/", $envContent)) {
            echo "   ✅ Debug is disabled in production\n";
        } else {
            echo "   ❌ Debug should be disabled in production\n";
        }
    }
} else {
    echo "   ❌ .env file not found\n";
}
echo "\n";

// Check file permissions
echo "📋 File Permissions Check:\n";
$directories = ['storage', 'bootstrap/cache', 'public/storage'];
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "   ✅ $dir is writable\n";
        } else {
            echo "   ❌ $dir is not writable\n";
        }
    } else {
        echo "   ❌ $dir does not exist\n";
    }
}
echo "\n";

// Check storage link
echo "📋 Storage Link Check:\n";
if (is_dir('public/storage')) {
    if (is_link('public/storage')) {
        echo "   ✅ Storage link exists\n";
    } else {
        echo "   ⚠️  Storage directory exists but is not a symbolic link\n";
    }
} else {
    echo "   ❌ Storage link does not exist\n";
}
echo "\n";

// Check database connection
echo "📋 Database Connection Check:\n";
try {
    if (file_exists('.env')) {
        // Load environment variables
        $envContent = file_get_contents('.env');
        $lines = explode("\n", $envContent);
        $env = [];
        
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
                list($key, $value) = explode('=', $line, 2);
                $env[trim($key)] = trim($value);
            }
        }
        
        if (isset($env['DB_CONNECTION']) && $env['DB_CONNECTION'] === 'mysql') {
            $host = $env['DB_HOST'] ?? 'localhost';
            $port = $env['DB_PORT'] ?? '3306';
            $database = $env['DB_DATABASE'] ?? '';
            $username = $env['DB_USERNAME'] ?? '';
            $password = $env['DB_PASSWORD'] ?? '';
            
            try {
                $dsn = "mysql:host=$host;port=$port;dbname=$database";
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Test a simple query
                $stmt = $pdo->query("SELECT 1");
                echo "   ✅ Database connection successful\n";
                
                // Check if required tables exist
                $tables = ['teleworking_requests', 'akses_logic_requests', 'users'];
                foreach ($tables as $table) {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                    $stmt->execute([$table]);
                    if ($stmt->rowCount() > 0) {
                        echo "   ✅ Table '$table' exists\n";
                    } else {
                        echo "   ⚠️  Table '$table' not found (run migrations)\n";
                    }
                }
                
            } catch (PDOException $e) {
                echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
            }
        } else {
            echo "   ⚠️  Database connection not configured for MySQL\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database check failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Check Composer dependencies
echo "📋 Composer Dependencies Check:\n";
if (file_exists('vendor/autoload.php')) {
    echo "   ✅ Composer dependencies installed\n";
    
    // Check if Laravel is installed
    if (class_exists('Illuminate\Foundation\Application')) {
        echo "   ✅ Laravel framework is installed\n";
    } else {
        echo "   ❌ Laravel framework not found\n";
    }
} else {
    echo "   ❌ Composer dependencies not installed\n";
}
echo "\n";

// Security check
echo "📋 Security Check:\n";
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    
    // Check if debug is off
    if (preg_match("/APP_DEBUG=false/", $envContent)) {
        echo "   ✅ Debug mode is disabled\n";
    } else {
        echo "   ❌ Debug mode should be disabled in production\n";
    }
    
    // Check if app key is set
    if (preg_match("/APP_KEY=base64:/", $envContent)) {
        echo "   ✅ Application key is set\n";
    } else {
        echo "   ❌ Application key is not properly set\n";
    }
    
    // Check for default passwords
    if (preg_match("/password=sail|password=password|password=secret/", $envContent)) {
        echo "   ❌ Default or weak passwords detected\n";
    } else {
        echo "   ✅ No obvious weak passwords detected\n";
    }
}
echo "\n";

// Final summary
echo "📊 Health Check Summary:\n";
echo str_repeat("-", 30) . "\n";

$totalIssues = count($missingExtensions);
if ($totalIssues === 0) {
    echo "✅ All critical checks passed!\n";
} else {
    echo "⚠️  $totalIssues issue(s) found that need attention\n";
}

echo "\n📝 Next Steps:\n";
echo "1. Fix any ❌ issues listed above\n";
echo "2. Run 'php artisan migrate' if tables are missing\n";
echo "3. Test the application in your browser\n";
echo "4. Monitor logs at storage/logs/laravel.log\n";

echo "\n🔗 Useful Commands:\n";
echo "php artisan migrate --force\n";
echo "php artisan storage:link\n";
echo "php artisan config:cache\n";
echo "php artisan route:cache\n";
echo "php artisan view:cache\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "Health check completed at: " . date('Y-m-d H:i:s') . "\n";
