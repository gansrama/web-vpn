<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== FIXING SIGNATURE PATHS ===\n\n";

// Get the signature file
$signatureDir = public_path('storage/signatures');
$files = glob($signatureDir . '/*.png');

if (empty($files)) {
    echo "❌ No signature files found\n";
    exit;
}

echo "Found signature files:\n";
foreach ($files as $file) {
    echo "  - " . basename($file) . "\n";
}

// Get the latest request that might have this signature
$latestRequest = DB::table('teleworking_requests')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$latestRequest) {
    echo "❌ No teleworking requests found\n";
    exit;
}

echo "\nLatest request:\n";
echo "ID: " . $latestRequest->id . "\n";
echo "Employee ID: " . $latestRequest->employee_id . "\n";
echo "Created: " . $latestRequest->created_at . "\n";

// Get employee info
$employee = DB::table('employees')->find($latestRequest->employee_id);
if ($employee) {
    echo "Employee Email: " . $employee->email . "\n";
    
    // Try to match signature file with employee email
    $expectedFileName = preg_replace('/[^a-zA-Z0-9]/', '_', $employee->email) . '.png';
    echo "Expected signature file: $expectedFileName\n";
    
    foreach ($files as $file) {
        if (strpos(basename($file), $expectedFileName) !== false || 
            strpos(basename($file), $employee->email) !== false) {
            echo "✅ Matching signature file found: " . basename($file) . "\n";
            
            // Update the database
            $signaturePath = 'storage/signatures/' . basename($file);
            DB::table('teleworking_requests')
                ->where('id', $latestRequest->id)
                ->update(['signature_path' => $signaturePath]);
            
            echo "✅ Updated request ID " . $latestRequest->id . " with signature path: $signaturePath\n";
            
            // Test the API
            $apiUrl = "http://127.0.0.1:8000/api/teleworking-requests/{$latestRequest->id}/signature";
            echo "\nTesting API: $apiUrl\n";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "HTTP Status: $httpCode\n";
            if ($httpCode == 200) {
                $data = json_decode($response, true);
                if ($data && $data['success']) {
                    echo "✅ API Working! Base64 length: " . strlen($data['data']['signature_base64']) . " chars\n";
                }
            }
            exit;
        }
    }
    
    echo "❌ No matching signature file found for employee\n";
} else {
    echo "❌ Employee not found for request\n";
}

// If no match found, assign the first signature file to the latest request
echo "\nAssigning first available signature to latest request...\n";
$firstFile = reset($files);
$signaturePath = 'storage/signatures/' . basename($firstFile);

DB::table('teleworking_requests')
    ->where('id', $latestRequest->id)
    ->update(['signature_path' => $signaturePath]);

echo "✅ Updated request ID " . $latestRequest->id . " with signature path: $signaturePath\n";

echo "\n=== END FIX ===\n";
