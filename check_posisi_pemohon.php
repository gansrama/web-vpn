<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING POSISI PEMOHON ===\n\n";

// Check all teleworking requests
$requests = DB::table('teleworking_requests')->get();

echo "Total requests: " . $requests->count() . "\n\n";

foreach ($requests as $request) {
    echo "Request ID: " . $request->id . "\n";
    echo "Posisi Pemohon: " . ($request->posisi_pemohon ?? 'NULL') . "\n";
    echo "Signature Path: " . ($request->signature_path ?? 'NULL') . "\n";
    echo "Employee ID: " . $request->employee_id . "\n";
    
    if ($request->employee_id) {
        $employee = DB::table('employees')->find($request->employee_id);
        if ($employee) {
            echo "Employee Name: " . $employee->nama_lengkap . "\n";
        }
    }
    echo "---\n";
}

// Fix posisi_pemohon for request with signature
echo "\n=== FIXING POSISI PEMOHON ===\n";

$requestWithSignature = DB::table('teleworking_requests')
    ->whereNotNull('signature_path')
    ->first();

if ($requestWithSignature) {
    echo "Found request with signature: ID " . $requestWithSignature->id . "\n";
    
    if (is_null($requestWithSignature->posisi_pemohon)) {
        // Set a default posisi_pemohon value
        $defaultPosisi = "Jakarta, " . date('d F Y');
        
        DB::table('teleworking_requests')
            ->where('id', $requestWithSignature->id)
            ->update(['posisi_pemohon' => $defaultPosisi]);
        
        echo "✅ Updated posisi_pemohon to: $defaultPosisi\n";
    } else {
        echo "✅ posisi_pemohon already set: " . $requestWithSignature->posisi_pemohon . "\n";
    }
    
    // Verify the update
    $updatedRequest = DB::table('teleworking_requests')->find($requestWithSignature->id);
    echo "Updated posisi_pemohon: " . ($updatedRequest->posisi_pemohon ?? 'NULL') . "\n";
} else {
    echo "❌ No request with signature found\n";
}

echo "\n=== END CHECK ===\n";
