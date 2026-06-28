<?php

echo "=== TESTING DIRECT IMAGE ACCESS ===\n\n";

$filename = 'alhabib.adelia@jsclab.id.png';
$imageUrl = "http://127.0.0.1:8000/storage/signatures/" . urlencode($filename);

echo "Testing direct image URL: $imageUrl\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $imageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Content-Type: $contentType\n";

if ($httpCode == 200) {
    echo "✅ Direct image access working!\n";
    
    // Also test with the API for comparison
    echo "\nComparing with API...\n";
    $apiUrl = "http://127.0.0.1:8000/api/teleworking-requests/23/signature";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $apiResponse = curl_exec($ch);
    $apiHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "API Status: $apiHttpCode\n";
    
    if ($apiHttpCode == 200) {
        $data = json_decode($apiResponse, true);
        if ($data && $data['success']) {
            echo "✅ API also working!\n";
            echo "Base64 length: " . strlen($data['data']['signature_base64']) . " chars\n";
        }
    }
} else {
    echo "❌ Direct image access failed\n";
    echo "Response: " . substr($response, 0, 200) . "...\n";
}

echo "\n=== SOLUTIONS FOR FRONTEND ===\n";
echo "1. Use current API (base64): ✅ Working\n";
echo "2. Use direct image URL: " . ($httpCode == 200 ? "✅ Available" : "❌ Not working") . "\n";
echo "3. Combined approach: Use direct URL when available, fallback to base64\n";

echo "\n=== END TEST ===\n";
