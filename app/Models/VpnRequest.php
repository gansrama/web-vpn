<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VpnRequest extends Model
{
    use HasFactory;

    protected $table = 'vpn_requests';

    protected $fillable = [
        'employee_id',
        'nama_sistem',
        'ip_address',
        'jenis_akses',
        'masa_berlaku',
        'keperluan_vpn',
        'pengguna_hak_akses',
        'sudah_menandatangani_surat_pernyataan',
        'memahami_kebijakan_keamanan',
        'status',
        'catatan',
        'signature_path', // Tambah path signature
    ];

    protected $casts = [
        'sudah_menandatangani_surat_pernyataan' => 'boolean',
        'memahami_kebijakan_keamanan' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    public function getMasaBerlakuAttribute($value): string
    {
        return match($value) {
            'q1' => 'Januari s.d Maret',
            'q2' => 'April s.d Juni',
            'q3' => 'Juli s.d September',
            'q4' => 'Oktober s.d Desember',
            default => $value,
        };
    }
}
