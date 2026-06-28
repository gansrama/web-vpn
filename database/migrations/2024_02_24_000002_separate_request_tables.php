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
        // Create table for Akses Logic requests
        Schema::create('akses_logic_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('nama_sistem');
            $table->string('ip_address');
            $table->string('jenis_akses');
            $table->string('masa_berlaku'); // q1, q2, q3, q4
            $table->string('keperluan_vpn');
            $table->string('pengguna_hak_akses'); // asn, non-asn
            $table->boolean('sudah_menandatangani_surat_pernyataan')->default(false);
            $table->boolean('memahami_kebijakan_keamanan')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->string('request_type')->default('akses_logic'); // identifier
            $table->timestamps();
        });

        // Create table for Teleworking requests
        Schema::create('teleworking_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('masa_berlaku'); // q1, q2, q3, q4
            $table->string('keperluan_vpn');
            $table->string('pengguna_hak_akses'); // asn, non-asn
            $table->boolean('sudah_menandatangani_surat_pernyataan')->default(false);
            $table->boolean('memahami_kebijakan_keamanan')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->string('request_type')->default('teleworking'); // identifier
            $table->timestamps();
        });

        // Create access logs for Akses Logic
        Schema::create('akses_logic_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akses_logic_request_id')->constrained('akses_logic_requests')->onDelete('cascade');
            $table->string('user_identifier');
            $table->string('ip_address');
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->string('session_duration')->nullable();
            $table->string('status'); // connected, disconnected, error
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // Create access logs for Teleworking
        Schema::create('teleworking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teleworking_request_id')->constrained('teleworking_requests')->onDelete('cascade');
            $table->string('user_identifier');
            $table->string('ip_address');
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->string('session_duration')->nullable();
            $table->string('status'); // connected, disconnected, error
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // Migrate existing data to Akses Logic table
        $existingRequests = \DB::table('vpn_requests')->get();
        foreach ($existingRequests as $request) {
            \DB::table('akses_logic_requests')->insert([
                'employee_id' => $request->employee_id,
                'nama_sistem' => $request->nama_sistem,
                'ip_address' => $request->ip_address,
                'jenis_akses' => $request->jenis_akses,
                'masa_berlaku' => $request->masa_berlaku,
                'keperluan_vpn' => $request->keperluan_vpn,
                'pengguna_hak_akses' => $request->pengguna_hak_akses,
                'sudah_menandatangani_surat_pernyataan' => $request->sudah_menandatangani_surat_pernyataan,
                'memahami_kebijakan_keamanan' => $request->memahami_kebijakan_keamanan,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'request_type' => 'akses_logic',
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
            ]);
        }

        // Drop old tables
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('vpn_requests');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teleworking_logs');
        Schema::dropIfExists('akses_logic_logs');
        Schema::dropIfExists('teleworking_requests');
        Schema::dropIfExists('akses_logic_requests');
    }
};
