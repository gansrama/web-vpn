<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Reset Auto Increment ID ===\n\n";

// Check current auto increment value
echo "1. Current auto increment status:\n";
echo str_repeat("-", 50) . "\n";

try {
    $maxId = DB::table('akses_logic_requests')->max('id');
    echo "Current maximum ID: " . ($maxId ?? 'NULL') . "\n";
    
    // Get SQLite sequence info
    $sequenceInfo = DB::select("SELECT seq FROM sqlite_sequence WHERE name = 'akses_logic_requests'");
    
    if (!empty($sequenceInfo)) {
        echo "Current sequence value: " . $sequenceInfo[0]->seq . "\n";
    } else {
        echo "No sequence found for table 'akses_logic_requests'\n";
    }
    
} catch (Exception $e) {
    echo "Error checking current status: " . $e->getMessage() . "\n";
}

echo "\n2. Backing up current data:\n";
echo str_repeat("-", 50) . "\n";

try {
    $currentData = DB::table('akses_logic_requests')->orderBy('id')->get();
    
    echo "Current records (" . $currentData->count() . "):\n";
    foreach ($currentData as $record) {
        echo "  ID: {$record->id} - Employee: {$record->employee_id} - Created: {$record->created_at}\n";
    }
    
} catch (Exception $e) {
    echo "Error backing up data: " . $e->getMessage() . "\n";
}

echo "\n3. Resetting auto increment to start from 1:\n";
echo str_repeat("-", 50) . "\n";

try {
    // Delete all records
    $deleted = DB::table('akses_logic_requests')->delete();
    echo "Deleted {$deleted} records from akses_logic_requests\n";
    
    // Reset sequence
    $resetSequence = DB::statement("DELETE FROM sqlite_sequence WHERE name = 'akses_logic_requests'");
    echo "Reset SQLite sequence for akses_logic_requests\n";
    
    // Insert new records with sequential IDs starting from 1
    $newRecords = [
        [
            'employee_id' => '1',
            'nama_sistem' => '192.168.3.132',
            'ip_address' => '192.168.3.132',
            'jenis_akses' => 'clientless',
            'masa_berlaku' => '30',
            'keperluan_vpn' => 'remote-database',
            'pengguna_hak_akses' => 'asn',
            'sudah_menandatangani_surat_pernyataan' => 1,
            'memahami_kebijakan_keamanan' => 1,
            'status' => 'pending',
            'catatan' => 'Submitted by: Alhabib Adelia Saputra (alhabib.adelia@jsclab.id)',
            'request_type' => 'akses_logic',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'employee_id' => '2',
            'nama_sistem' => '192.168.3.132',
            'ip_address' => '192.168.3.132',
            'jenis_akses' => 'clientless',
            'masa_berlaku' => '30',
            'keperluan_vpn' => 'remote-database',
            'pengguna_hak_akses' => 'non-asn',
            'sudah_menandatangani_surat_pernyataan' => 1,
            'memahami_kebijakan_keamanan' => 1,
            'status' => 'pending',
            'catatan' => 'Submitted by: Andika Bayhaki AL (andika.bayhaki@jsclab.id)',
            'request_type' => 'akses_logic',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ],
        [
            'employee_id' => '3',
            'nama_sistem' => '192.168.3.133',
            'ip_address' => '192.168.3.133',
            'jenis_akses' => 'clientless',
            'masa_berlaku' => 'q1',
            'keperluan_vpn' => 'internal-network',
            'pengguna_hak_akses' => 'asn',
            'sudah_menandatangani_surat_pernyataan' => 1,
            'memahami_kebijakan_keamanan' => 1,
            'status' => 'pending',
            'catatan' => 'Submitted by: Celsa Bella (celsa.bella@jsclab.id)',
            'request_type' => 'akses_logic',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    foreach ($newRecords as $index => $record) {
        $inserted = DB::table('akses_logic_requests')->insert($record);
        echo "Inserted record " . ($index + 1) . " with auto-generated ID\n";
    }
    
    echo "\nInserted " . count($newRecords) . " new records\n";
    
} catch (Exception $e) {
    echo "Error resetting auto increment: " . $e->getMessage() . "\n";
}

echo "\n4. Verification:\n";
echo str_repeat("-", 50) . "\n";

try {
    $newData = DB::table('akses_logic_requests')->orderBy('id')->get();
    
    echo "New records (" . $newData->count() . "):\n";
    foreach ($newData as $record) {
        echo "  ID: {$record->id} - Employee: {$record->employee_id} - Created: {$record->created_at}\n";
    }
    
    // Check sequence
    $newSequenceInfo = DB::select("SELECT seq FROM sqlite_sequence WHERE name = 'akses_logic_requests'");
    
    if (!empty($newSequenceInfo)) {
        echo "\nNew sequence value: " . $newSequenceInfo[0]->seq . "\n";
        echo "Next ID will be: " . ($newSequenceInfo[0]->seq + 1) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error verifying: " . $e->getMessage() . "\n";
}

echo "\n5. Testing next insert:\n";
echo str_repeat("-", 50) . "\n";

try {
    $testRecord = [
        'employee_id' => '4',
        'nama_sistem' => '192.168.3.134',
        'ip_address' => '192.168.3.134',
        'jenis_akses' => 'clientless',
        'masa_berlaku' => '30',
        'keperluan_vpn' => 'testing',
        'pengguna_hak_akses' => 'asn',
        'sudah_menandatangani_surat_pernyataan' => 1,
        'memahami_kebijakan_keamanan' => 1,
        'status' => 'pending',
        'catatan' => 'Submitted by: Test User (test@example.com)',
        'request_type' => 'akses_logic',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $insertedId = DB::table('akses_logic_requests')->insertGetId($testRecord);
    echo "Test record inserted with ID: {$insertedId}\n";
    
    // Clean up test record
    DB::table('akses_logic_requests')->where('id', $insertedId)->delete();
    echo "Test record cleaned up\n";
    
} catch (Exception $e) {
    echo "Error testing next insert: " . $e->getMessage() . "\n";
}

echo "\n=== Auto Increment Reset Complete ===\n";
echo "✓ Auto increment now starts from 1\n";
echo "✓ Sequential IDs: 1, 2, 3, ...\n";
echo "✓ Next new record will get ID: " . (DB::table('akses_logic_requests')->count() + 1) . "\n";
