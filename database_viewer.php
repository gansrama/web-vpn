<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all tables
echo "=== WEBFORM VPN DATABASE STRUCTURE ===" . PHP_EOL . PHP_EOL;

$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");

echo "Total Tables: " . count($tables) . PHP_EOL . PHP_EOL;

foreach ($tables as $table) {
    echo "📋 TABLE: " . $table->name . PHP_EOL;
    echo str_repeat("=", 50) . PHP_EOL;
    
    // Get table structure
    $columns = DB::select("PRAGMA table_info(" . $table->name . ")");
    
    foreach ($columns as $column) {
        $type = $column->type;
        $null = $column->notnull ? 'NOT NULL' : 'NULL';
        $default = $column->dflt_value !== null ? "DEFAULT " . $column->dflt_value : '';
        $pk = $column->pk ? 'PRIMARY KEY' : '';
        
        echo sprintf("  %-20s %-15s %-10s %s %s", 
            $column->name, 
            $type, 
            $null, 
            $default, 
            $pk
        ) . PHP_EOL;
    }
    
    // Get record count
    $count = DB::table($table->name)->count();
    echo "  Record Count: " . $count . PHP_EOL;
    
    // Show sample data if table has records
    if ($count > 0 && $count <= 5) {
        echo "  Sample Data:" . PHP_EOL;
        $sampleData = DB::table($table->name)->limit(3)->get();
        foreach ($sampleData as $row) {
            $data = (array) $row;
            echo "    " . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
        }
    } elseif ($count > 5) {
        echo "  Sample Data (first 3 records):" . PHP_EOL;
        $sampleData = DB::table($table->name)->limit(3)->get();
        foreach ($sampleData as $row) {
            $data = (array) $row;
            echo "    " . json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
        }
    }
    
    echo PHP_EOL . PHP_EOL;
}

echo "=== VPN SERVERS DATA ===" . PHP_EOL;
$servers = DB::table('vpn_servers')->get();
foreach ($servers as $server) {
    echo "🖥️  {$server->nama_sistem} ({$server->server_location}) - {$server->ip_address}:{$server->port}" . PHP_EOL;
}

echo PHP_EOL . "=== REQUEST SUMMARY ===" . PHP_EOL;
$aksesCount = DB::table('akses_logic_requests')->count();
$teleworkingCount = DB::table('teleworking_requests')->count();
$employeeCount = DB::table('employees')->count();

echo "👥 Total Employees: {$employeeCount}" . PHP_EOL;
echo "🔐 Akses Logic Requests: {$aksesCount}" . PHP_EOL;
echo "🏠 Teleworking Requests: {$teleworkingCount}" . PHP_EOL;
