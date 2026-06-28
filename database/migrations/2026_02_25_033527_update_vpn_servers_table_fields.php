<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vpn_servers', function (Blueprint $table) {
            // Remove unnecessary columns
            $table->dropColumn(['port', 'protocol']);
            
            // Keep only required columns: id, ip_address, nama_sistem, server_location, is_active, created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vpn_servers', function (Blueprint $table) {
            // Add back the removed columns
            $table->string('port')->after('ip_address');
            $table->string('protocol')->after('port');
        });
    }
};
