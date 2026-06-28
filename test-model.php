<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\VpnServer;

echo "Testing VpnServer model...\n";
$servers = VpnServer::all();
echo "Total servers: " . $servers->count() . "\n";
echo "First server: " . $servers->first()->nama_sistem . "\n";

// Debug: check if model is working
echo "Model class exists: " . (class_exists('App\Models\VpnServer') ? 'Yes' : 'No') . "\n";
echo "Table exists: " . (DB::table('vpn_servers')->exists() ? 'Yes' : 'No') . "\n";
