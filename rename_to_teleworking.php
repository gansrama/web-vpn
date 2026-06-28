<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Rename Akses Logic Copy to Teleworking ===\n\n";

try {
    // 1. Rename table
    echo "1. Renaming table...\n";
    
    // Check if teleworking table exists
    $teleworkingExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='teleworking'");
    
    if (count($teleworkingExists) > 0) {
        echo "❌ Table 'teleworking' already exists!\n";
        echo "Dropping existing table...\n";
        DB::statement("DROP TABLE IF EXISTS teleworking");
        echo "✅ Existing table dropped\n";
    }
    
    // Check if akses_logic_requests_copy exists
    $copyExists = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='akses_logic_requests_copy'");
    
    if (count($copyExists) > 0) {
        echo "Renaming 'akses_logic_requests_copy' to 'teleworking'...\n";
        DB::statement("ALTER TABLE akses_logic_requests_copy RENAME TO teleworking");
        echo "✅ Table renamed successfully\n";
    } else {
        echo "❌ Table 'akses_logic_requests_copy' not found!\n";
    }
    
    // 2. Update API routes
    echo "\n2. Updating API routes...\n";
    $apiRoutesFile = __DIR__ . '/routes/api.php';
    
    if (file_exists($apiRoutesFile)) {
        $currentContent = file_get_contents($apiRoutesFile);
        
        // Replace old routes with new ones
        $newContent = str_replace(
            [
                "Route::get('/akses-logic-requests-copy', function () {",
                "Route::post('/akses-logic-copy-requests', function (Request \$request) {",
                "DB::table('akses_logic_requests_copy')",
                "'akses_logic_requests_copy'"
            ],
            [
                "Route::get('/teleworking-requests', function () {",
                "Route::post('/teleworking-requests', function (Request \$request) {",
                "DB::table('teleworking')",
                "'teleworking'"
            ],
            $currentContent
        );
        
        file_put_contents($apiRoutesFile, $newContent);
        echo "✅ API routes updated\n";
        echo "   - GET: /api/teleworking-requests\n";
        echo "   - POST: /api/teleworking-requests\n";
    }
    
    // 3. Update Vue components to use new API endpoints
    echo "\n3. Updating Vue components...\n";
    
    // Update form-teleworking.vue
    $formFile = __DIR__ . '/resources/ts/pages/form-teleworking.vue';
    if (file_exists($formFile)) {
        $formContent = file_get_contents($formFile);
        $formContent = str_replace(
            [
                "'/api/akses-logic-copy-requests'",
                "fetch('/api/akses-logic-copy-requests'"
            ],
            [
                "'/api/teleworking-requests'",
                "fetch('/api/teleworking-requests'"
            ],
            $formContent
        );
        file_put_contents($formFile, $formContent);
        echo "✅ Form component updated\n";
    }
    
    // Update data-teleworking.vue
    $dataFile = __DIR__ . '/resources/ts/pages/data-teleworking.vue';
    if (file_exists($dataFile)) {
        $dataContent = file_get_contents($dataFile);
        $dataContent = str_replace(
            [
                "fetch('/api/akses-logic-requests-copy')"
            ],
            [
                "fetch('/api/teleworking-requests')"
            ],
            $dataContent
        );
        file_put_contents($dataFile, $dataContent);
        echo "✅ Data component updated\n";
    }
    
    // 4. Test new API endpoints
    echo "\n4. Testing new API endpoints...\n";
    
    // Test GET endpoint
    $testUrl = 'http://127.0.0.1:8001/api/teleworking-requests';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ GET /api/teleworking-requests working\n";
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            echo "   Total records: " . count($result['data']) . "\n";
        }
    } else {
        echo "❌ GET /api/teleworking-requests failed (HTTP {$httpCode})\n";
    }
    
    // Test POST endpoint
    $testData = [
        'employee_id' => 1,
        'nama_sistem' => '192.168.3.200',
        'ip_address' => '192.168.3.200',
        'jenis_akses' => 'clientless',
        'masa_berlaku' => '30',
        'keperluan_vpn' => 'remote-database',
        'pengguna_hak_akses' => 'non-asn',
        'sudah_menandatangani_surat_pernyataan' => true,
        'memahami_kebijakan_keamanan' => true,
        'agreeToTerms' => 'Ya',
        'agreeToPolicy' => 'Ya',
        'status' => 'pending',
        'catatan' => 'Submitted by: rename-test@example.com (rename-test@example.com)'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8001/api/teleworking-requests');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ POST /api/teleworking-requests working\n";
        $result = json_decode($response, true);
        if ($result && isset($result['success']) && $result['success']) {
            echo "   Request ID: {$result['request_id']}\n";
        }
    } else {
        echo "❌ POST /api/teleworking-requests failed (HTTP {$httpCode})\n";
    }
    
    echo "\n=== Summary ===\n";
    echo "✅ Table renamed: akses_logic_requests_copy → teleworking\n";
    echo "✅ API routes updated:\n";
    echo "   - GET: /api/teleworking-requests\n";
    echo "   - POST: /api/teleworking-requests\n";
    echo "✅ Vue components updated\n";
    echo "✅ Routes updated: /form-teleworking, /data-teleworking\n";
    echo "✅ Navigation updated\n";
    
    echo "\n🎉 Teleworking migration complete!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Process Complete ===\n";
