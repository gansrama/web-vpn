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
        Schema::create('akses_logic_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akses_logic_request_id')->constrained('akses_logic_requests')->onDelete('cascade');
            $table->string('nama_sistem');
            $table->string('ip_address');
            $table->string('jenis_akses');
            $table->timestamps();
        });

        // Migrate existing single access logic data to items
        $existingRequests = \DB::table('akses_logic_requests')->get();
        foreach ($existingRequests as $request) {
            \DB::table('akses_logic_items')->insert([
                'akses_logic_request_id' => $request->id,
                'nama_sistem' => $request->nama_sistem,
                'ip_address' => $request->ip_address,
                'jenis_akses' => $request->jenis_akses,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akses_logic_items');
    }
};
