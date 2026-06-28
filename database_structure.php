<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== DATABASE STRUCTURE ===\n\n";

$tables = [
    'employees',
    'akses_logic_requests', 
    'teleworking_requests',
    'vpn_servers',
    'akses_logic_logs',
    'teleworking_logs',
    'migrations'
];

foreach ($tables as $table) {
    echo "=== TABLE: $table ===\n";
    
    if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        foreach ($columns as $column) {
            $type = \Illuminate\Support\Facades\Schema::getColumnType($table, $column);
            echo "  - $column ($type)\n";
        }
        
        // Count records
        $count = \Illuminate\Support\Facades\DB::table($table)->count();
        echo "  Records: $count\n";
    } else {
        echo "  Table does not exist\n";
    }
    echo "\n";
}

echo "=== SAMPLE DATA ===\n\n";

// Sample employees
echo "--- Employees (3 records) ---\n";
$employees = \Illuminate\Support\Facades\DB::table('employees')->limit(3)->get();
foreach ($employees as $emp) {
    echo "ID: {$emp->id}, Name: {$emp->nama_lengkap}, Email: {$emp->email}\n";
}
echo "\n";

// Sample akses logic requests
echo "--- Akses Logic Requests (3 records) ---\n";
$requests = \Illuminate\Support\Facades\DB::table('akses_logic_requests')->limit(3)->get();
foreach ($requests as $req) {
    $employee = \Illuminate\Support\Facades\DB::table('employees')->find($req->employee_id);
    echo "ID: {$req->id}, Employee: " . ($employee ? $employee->nama_lengkap : 'Unknown') . ", System: {$req->nama_sistem}\n";
}
echo "\n";

echo "=== RELATIONSHIPS ===\n";
echo "employees -> akses_logic_requests (employee_id)\n";
echo "employees -> teleworking_requests (employee_id)\n";
echo "akses_logic_requests -> akses_logic_logs (akses_logic_request_id)\n";
echo "teleworking_requests -> teleworking_logs (teleworking_request_id)\n";
