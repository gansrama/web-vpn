<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING SIGNATURE API ===\n\n";

// Get teleworking request with signature
try {
    $request = DB::table('teleworking_requests')->whereNotNull('signature_path')->first();
    
    if (!$request) {
        echo "❌ No teleworking requests found\n";
        echo "Please create a teleworking request first\n";
        exit;
    }
    
    echo "✅ Found teleworking request:\n";
    echo "ID: " . $request->id . "\n";
    echo "Signature Path: " . ($request->signature_path ?? 'NULL') . "\n";
    echo "Employee ID: " . $request->employee_id . "\n\n";
    
    // Test signature API
    $apiUrl = "http://127.0.0.1:8000/api/teleworking-requests/{$request->id}/signature";
    echo "Testing API: $apiUrl\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $httpCode\n";
    echo "Response: " . substr($response, 0, 500) . "...\n\n";
    
    if ($httpCode == 200) {
        $data = json_decode($response, true);
        if ($data && $data['success']) {
            echo "✅ API Working!\n";
            echo "Signature Path: " . $data['data']['signature_path'] . "\n";
            echo "MIME Type: " . $data['data']['mime_type'] . "\n";
            echo "Base64 Length: " . strlen($data['data']['signature_base64']) . " chars\n";
        } else {
            echo "❌ API Response Error: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "❌ HTTP Error: $httpCode\n";
    }
    
    // Check if signature file exists
    if ($request->signature_path) {
        $fullPath = public_path($request->signature_path);
        echo "\nFile Check:\n";
        echo "Path: $fullPath\n";
        echo "Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        if (file_exists($fullPath)) {
            echo "Size: " . filesize($fullPath) . " bytes\n";
        }
    } else {
        echo "\nNo signature path in database\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== END TEST ===\n";
