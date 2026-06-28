<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CHECKING ALL SIGNATURES ===\n\n";

// Check all teleworking requests
$requests = DB::table('teleworking_requests')->get();
echo "Total teleworking requests: " . $requests->count() . "\n\n";

$signatureCount = 0;
foreach ($requests as $request) {
    echo "Request ID: " . $request->id . "\n";
    echo "Signature Path: " . ($request->signature_path ?? 'NULL') . "\n";
    
    if ($request->signature_path) {
        $signatureCount++;
        $fullPath = public_path($request->signature_path);
        echo "File Exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";
        if (file_exists($fullPath)) {
            echo "File Size: " . filesize($fullPath) . " bytes\n";
        }
    }
    echo "---\n";
}

echo "\nSummary:\n";
echo "Requests with signatures: $signatureCount\n";
echo "Requests without signatures: " . ($requests->count() - $signatureCount) . "\n";

// Check storage directory
$signatureDir = public_path('storage/signatures');
echo "\nStorage Directory: $signatureDir\n";
echo "Directory Exists: " . (is_dir($signatureDir) ? 'YES' : 'NO') . "\n";

if (is_dir($signatureDir)) {
    $files = glob($signatureDir . '/*.png');
    echo "PNG files in directory: " . count($files) . "\n";
    foreach ($files as $file) {
        echo "  - " . basename($file) . " (" . filesize($file) . " bytes)\n";
    }
} else {
    echo "Creating signature directory...\n";
    mkdir($signatureDir, 0755, true);
    echo "Directory created: " . (is_dir($signatureDir) ? 'YES' : 'NO') . "\n";
}

echo "\n=== END CHECK ===\n";
