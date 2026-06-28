<?php
// Test submit functionality
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "<h1>Submit Test</h1>";

try {
    // Test creating a simple record
    $employee = \App\Models\Employee::first();
    
    if (!$employee) {
        echo "<p style='color: red;'>No employees found in database!</p>";
    } else {
        echo "<p style='color: green;'>Found employee: " . $employee->nama_lengkap . "</p>";
        
        // Test creating akses logic request
        $request = new \App\Models\AksesLogicRequest();
        $request->employee_id = $employee->id;
        $request->nama_sistem = 'Test System';
        $request->ip_address = '127.0.0.1';
        $request->jenis_akses = 'clientless';
        $request->masa_berlaku = 'q1';
        $request->keperluan_vpn = 'Test purpose';
        $request->pengguna_hak_akses = 'asn';
        $request->sudah_menandatangani_surat_pernyataan = true;
        $request->memahami_kebijakan_keamanan = true;
        $request->status = 'pending';
        $request->request_type = 'akses_logic';
        $request->posisi_pemohon = 'Test Location';
        
        if ($request->save()) {
            echo "<p style='color: green;'>Test record created successfully! ID: " . $request->id . "</p>";
            
            // Test retrieving
            $retrieved = \App\Models\AksesLogicRequest::with('employee')->find($request->id);
            echo "<p>Retrieved record: " . $retrieved->nama_sistem . " - Employee: " . $retrieved->employee->nama_lengkap . "</p>";
            
            // Clean up
            $request->delete();
            echo "<p style='color: blue;'>Test record cleaned up</p>";
        } else {
            echo "<p style='color: red;'>Failed to create test record</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
?>
