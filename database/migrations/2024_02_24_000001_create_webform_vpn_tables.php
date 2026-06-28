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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap');
            $table->string('nomor_ktp')->unique();
            $table->string('email');
            $table->string('posisi_jabatan');
            $table->string('nama_organisasi');
            $table->string('nomer_hp_wa');
            $table->timestamps();
        });

        Schema::create('vpn_requests', function (Blueprint $table) {
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
            $table->timestamps();
        });

        Schema::create('vpn_servers', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sistem');
            $table->string('server_location');
            $table->string('ip_address')->unique();
            $table->string('port');
            $table->string('protocol');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vpn_request_id')->constrained('vpn_requests')->onDelete('cascade');
            $table->string('user_identifier');
            $table->string('ip_address');
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->string('session_duration')->nullable();
            $table->string('status'); // connected, disconnected, error
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('vpn_servers');
        Schema::dropIfExists('vpn_requests');
        Schema::dropIfExists('employees');
    }
};
