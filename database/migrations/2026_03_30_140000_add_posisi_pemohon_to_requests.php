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
        // Add posisi_pemohon to teleworking_requests
        if (!Schema::hasColumn('teleworking_requests', 'posisi_pemohon')) {
            Schema::table('teleworking_requests', function (Blueprint $table) {
                $table->string('posisi_pemohon', 255)->nullable()->after('signature_path');
            });
        }

        // Add posisi_pemohon to akses_logic_requests
        if (!Schema::hasColumn('akses_logic_requests', 'posisi_pemohon')) {
            Schema::table('akses_logic_requests', function (Blueprint $table) {
                $table->string('posisi_pemohon', 255)->nullable()->after('signature_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop posisi_pemohon from teleworking_requests
        if (Schema::hasColumn('teleworking_requests', 'posisi_pemohon')) {
            Schema::table('teleworking_requests', function (Blueprint $table) {
                $table->dropColumn('posisi_pemohon');
            });
        }

        // Drop posisi_pemohon from akses_logic_requests
        if (Schema::hasColumn('akses_logic_requests', 'posisi_pemohon')) {
            Schema::table('akses_logic_requests', function (Blueprint $table) {
                $table->dropColumn('posisi_pemohon');
            });
        }
    }
};
