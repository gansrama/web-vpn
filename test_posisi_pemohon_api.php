<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING POSISI PEMOHON IN API ===\n\n";

// Get request with signature
$request = DB::table('teleworking_requests')
    ->whereNotNull('signature_path')
    ->first();

if (!$request) {
    echo "❌ No request with signature found\n";
    exit;
}

echo "Request Details:\n";
echo "ID: " . $request->id . "\n";
echo "Posisi Pemohon: " . ($request->posisi_pemohon ?? 'NULL') . "\n";
echo "Signature Path: " . ($request->signature_path ?? 'NULL') . "\n\n";

// Test the main API endpoint
echo "Testing main API endpoint...\n";
$apiUrl = "http://127.0.0.1:8000/api/teleworking-requests/" . $request->id;

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
        $requestData = $data['data'];
        echo "✅ API Success!\n";
        echo "Request posisi_pemohon: " . ($requestData['posisi_pemohon'] ?? 'NULL') . "\n";
        echo "Request signature_path: " . ($requestData['signature_path'] ?? 'NULL') . "\n";
    }
} else {
    echo "❌ API Failed: $response\n";
}

// Test signature API endpoint
echo "\nTesting signature API endpoint...\n";
$signatureUrl = "http://127.0.0.1:8000/api/teleworking-requests/" . $request->id . "/signature";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $signatureUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
$signatureResponse = curl_exec($ch);
$signatureHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $signatureHttpCode\n";

if ($signatureHttpCode == 200) {
    $signatureData = json_decode($signatureResponse, true);
    if ($signatureData && $signatureData['success']) {
        echo "✅ Signature API Success!\n";
        echo "Signature Path: " . $signatureData['data']['signature_path'] . "\n";
        echo "Base64 Length: " . strlen($signatureData['data']['signature_base64']) . " chars\n";
    }
} else {
    echo "❌ Signature API Failed: $signatureResponse\n";
}

// Test print template data
echo "\n=== PRINT TEMPLATE CHECK ===\n";
echo "Frontend should receive:\n";
echo "- request.posisi_pemohon: " . ($request->posisi_pemohon ?? 'NULL') . "\n";
echo "- request.signature_path: " . ($request->signature_path ?? 'NULL') . "\n";
echo "- signatureSrc: Should be populated from API\n";

echo "\nTemplate logic:\n";
echo "1. Fetch signature from API\n";
echo "2. Check if signatureSrc exists\n";
echo "3. Display signature image\n";
echo "4. Show posisi_pemohon below signature\n";

echo "\nExpected result:\n";
echo "- Signature image should display\n";
echo "- Position text should appear below signature\n";
echo "- Format: 'Jakarta, 30 March 2026'\n";

echo "\n=== END TEST ===\n";
