<?php
// Database checker
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check if table exists
    $tables = \DB::select("SHOW TABLES LIKE 'akses_logic_requests'");
    echo "<h1>Database Check</h1>";
    
    if (empty($tables)) {
        echo "<p style='color: red;'>Table 'akses_logic_requests' does not exist!</p>";
    } else {
        echo "<p style='color: green;'>Table 'akses_logic_requests' exists!</p>";
        
        // Check count
        $count = \DB::table('akses_logic_requests')->count();
        echo "<p>Total records: " . $count . "</p>";
        
        // Show recent records
        $records = \DB::table('akses_logic_requests')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        echo "<h3>Recent Records:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nama Sistem</th><th>Status</th><th>Created At</th></tr>";
        
        foreach ($records as $record) {
            echo "<tr>";
            echo "<td>" . $record->id . "</td>";
            echo "<td>" . $record->nama_sistem . "</td>";
            echo "<td>" . $record->status . "</td>";
            echo "<td>" . $record->created_at . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Check employee relationship
        echo "<h3>Employee Relationship Check:</h3>";
        $recordsWithEmployee = \DB::table('akses_logic_requests')
            ->leftJoin('employees', 'akses_logic_requests.employee_id', '=', 'employees.id')
            ->select('akses_logic_requests.id', 'akses_logic_requests.employee_id', 'employees.nama_lengkap')
            ->limit(5)
            ->get();
            
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Request ID</th><th>Employee ID</th><th>Employee Name</th></tr>";
        
        foreach ($recordsWithEmployee as $record) {
            echo "<tr>";
            echo "<td>" . $record->id . "</td>";
            echo "<td>" . $record->employee_id . "</td>";
            echo "<td>" . ($record->nama_lengkap ?? 'Not found') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
