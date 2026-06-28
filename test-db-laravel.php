<?php

define('LARAVEL_START', microtime(true));

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "Testing Laravel bootstrap...\n";

try {
    // Test basic connection
    $result = \Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as count FROM vpn_servers');
    echo "VPN servers count: " . $result[0]->count . "\n";
    
    // Test table exists
    $tableExists = \Illuminate\Support\Facades\DB::getSchemaBuilder()->hasTable('vpn_servers');
    echo "Table exists: " . ($tableExists ? 'Yes' : 'No') . "\n";
    
    // Test sample query
    $servers = \Illuminate\Support\Facades\DB::table('vpn_servers')->limit(3)->get();
    echo "Sample data:\n";
    foreach ($servers as $server) {
        echo "ID: " . $server->id . ", IP: " . $server->ip_address . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Connection failed!\n";
}
