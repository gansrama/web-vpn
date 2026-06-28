<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "=== Removing Fields from Teleworking Table ===\n\n";

try {
    // Check current table structure
    echo "Current table structure:\n";
    $columns = DB::select("PRAGMA table_info(teleworking)");
    
    $fieldsToRemove = ['nama_sistem', 'ip_address', 'jenis_akses'];
    $existingFields = [];
    
    foreach ($columns as $column) {
        $existingFields[] = $column->name;
        if (in_array($column->name, $fieldsToRemove)) {
            echo "❌ Field to remove: {$column->name} ({$column->type})\n";
        } else {
            echo "✅ Field to keep: {$column->name} ({$column->type})\n";
        }
    }
    
    echo "\n";
    
    // Create backup table
    echo "1. Creating backup table...\n";
    DB::statement("CREATE TABLE teleworking_backup AS SELECT * FROM teleworking");
    echo "✅ Backup table created\n";
    
    // Create new table without unwanted fields
    echo "2. Creating new table structure...\n";
    DB::statement("DROP TABLE teleworking");
    
    $createTableSQL = "
    CREATE TABLE teleworking (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        employee_id INTEGER,
        masa_berlaku TEXT,
        keperluan_vpn TEXT,
        pengguna_hak_akses TEXT,
        sudah_menandatangani_surat_pernyataan INTEGER DEFAULT 0,
        memahami_kebijakan_keamanan INTEGER DEFAULT 0,
        status TEXT DEFAULT 'pending',
        catatan TEXT,
        request_type TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    DB::statement($createTableSQL);
    echo "✅ New table created without unwanted fields\n";
    
    // Restore data from backup
    echo "3. Restoring data from backup...\n";
    $restoreSQL = "
    INSERT INTO teleworking (
        employee_id, masa_berlaku, keperluan_vpn, pengguna_hak_akses,
        sudah_menandatangani_surat_pernyataan, memahami_kebijakan_keamanan,
        status, catatan, request_type, created_at, updated_at
    )
    SELECT 
        employee_id, masa_berlaku, keperluan_vpn, pengguna_hak_akses,
        sudah_menandatangani_surat_pernyataan, memahami_kebijakan_keamanan,
        status, catatan, request_type, created_at, updated_at
    FROM teleworking_backup";
    
    DB::statement($restoreSQL);
    echo "✅ Data restored from backup\n";
    
    // Drop backup table
    echo "4. Cleaning up backup table...\n";
    DB::statement("DROP TABLE teleworking_backup");
    echo "✅ Backup table dropped\n";
    
    // Show new table structure
    echo "\nNew table structure:\n";
    $newColumns = DB::select("PRAGMA table_info(teleworking)");
    
    foreach ($newColumns as $column) {
        echo "✅ {$column->name} ({$column->type})\n";
    }
    
    // Show sample data
    echo "\nSample data (first 2 records):\n";
    $sampleData = DB::table('teleworking')->limit(2)->get();
    
    foreach ($sampleData as $row) {
        echo "ID: {$row->id}, Employee ID: {$row->employee_id}, ";
        echo "Masa Berlaku: {$row->masa_berlaku}, ";
        echo "Keperluan VPN: {$row->keperluan_vpn}, ";
        echo "Pengguna Hak Akses: {$row->pengguna_hak_akses}\n";
    }
    
    echo "\n✅ Table modification complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== Table Modification Complete ===\n";
