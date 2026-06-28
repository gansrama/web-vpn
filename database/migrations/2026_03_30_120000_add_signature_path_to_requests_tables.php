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
        // Add signature_path to teleworking_requests if not exists
        if (!Schema::hasColumn('teleworking_requests', 'signature_path')) {
            Schema::table('teleworking_requests', function (Blueprint $table) {
                $table->string('signature_path', 255)->nullable()->after('memahami_kebijakan_keamanan');
            });
        }

        // Add signature_path to akses_logic_requests if table exists and column not exists
        if (Schema::hasTable('akses_logic_requests') && !Schema::hasColumn('akses_logic_requests', 'signature_path')) {
            Schema::table('akses_logic_requests', function (Blueprint $table) {
                $table->string('signature_path', 255)->nullable()->after('memahami_kebijakan_keamanan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop signature_path from teleworking_requests if exists
        if (Schema::hasColumn('teleworking_requests', 'signature_path')) {
            Schema::table('teleworking_requests', function (Blueprint $table) {
                $table->dropColumn('signature_path');
            });
        }

        // Drop signature_path from akses_logic_requests if table and column exist
        if (Schema::hasTable('akses_logic_requests') && Schema::hasColumn('akses_logic_requests', 'signature_path')) {
            Schema::table('akses_logic_requests', function (Blueprint $table) {
                $table->dropColumn('signature_path');
            });
        }
    }
};
