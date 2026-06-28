<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

echo "Testing database connection...\n";

try {
    // Test basic connection
    $result = DB::select('SELECT COUNT(*) as count FROM vpn_servers');
    echo "VPN servers count: " . $result[0]->count . "\n";
    
    // Test table exists
    $tableExists = DB::getSchemaBuilder()->hasTable('vpn_servers');
    echo "Table exists: " . ($tableExists ? 'Yes' : 'No') . "\n";
    
    // Test sample query
    $servers = DB::table('vpn_servers')->limit(3)->get();
    echo "Sample data:\n";
    foreach ($servers as $server) {
        echo "ID: " . $server->id . ", IP: " . $server->ip_address . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Connection failed!\n";
}
