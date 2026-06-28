<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== TESTING SIGNATURE UPLOAD ===\n\n";

// Check if there are any recent requests with signatures
$recentRequests = DB::table('teleworking_requests')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "Recent requests:\n";
foreach ($recentRequests as $req) {
    echo "ID: {$req->id}, Signature: " . ($req->signature_path ? 'YES' : 'NO') . ", Posisi: " . ($req->posisi_pemohon ?? 'NULL') . "\n";
}

// Check storage directory
$signatureDir = public_path('storage/signatures');
echo "\nStorage directory: $signatureDir\n";
echo "Directory exists: " . (is_dir($signatureDir) ? 'YES' : 'NO') . "\n";

if (is_dir($signatureDir)) {
    $files = glob($signatureDir . '/*.png');
    echo "PNG files: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "  - " . basename($file) . " (" . filesize($file) . " bytes, " . date('Y-m-d H:i:s', filemtime($file)) . ")\n";
    }
}

// Test API endpoint
echo "\nTesting POST /api/teleworking-requests...\n";

// Create a test file
$testContent = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==";
$testFile = tempnam(sys_get_temp_dir(), 'test_signature_');
file_put_contents($testFile, base64_decode($testContent));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/teleworking-requests');
curl_setopt($ch, CURLOPT_POST, true);

// Create FormData
$postData = [
    'nama_lengkap' => 'Test User',
    'nomor_ktp' => '1234567890123456',
    'email' => 'test@example.com',
    'posisi_jabatan' => 'Test Position',
    'nama_organisasi' => 'Test Organization',
    'nomer_hp_wa' => '08123456789',
    'masa_berlaku' => 'q1',
    'keperluan_vpn' => 'Test Purpose',
    'pengguna_hak_akses' => 'asn',
    'sudah_menandatangani_surat_pernyataan' => true,
    'memahami_kebijakan_keamanan' => true,
    'posisi_pemohon' => 'Jakarta, ' . date('d F Y'),
    'signature_image' => new CURLFile($testFile, 'test_signature.png', 'test_signature.png')
];

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-CSRF-TOKEN: ' . csrf_token()
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Clean up
unlink($testFile);

echo "HTTP Status: $httpCode\n";
echo "Response: " . substr($response, 0, 500) . "...\n";

if ($httpCode == 200 || $httpCode == 201) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ Upload test successful!\n";
        echo "Request ID: " . ($data['data']['id'] ?? 'Unknown') . "\n";
        
        // Check if signature was saved
        if (isset($data['data']['id'])) {
            $newRequest = DB::table('teleworking_requests')->find($data['data']['id']);
            if ($newRequest && $newRequest->signature_path) {
                echo "✅ Signature path saved: " . $newRequest->signature_path . "\n";
            } else {
                echo "❌ Signature path not saved\n";
            }
        }
    } else {
        echo "❌ Upload failed: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ HTTP Error: $httpCode\n";
}

echo "\n=== END TEST ===\n";
