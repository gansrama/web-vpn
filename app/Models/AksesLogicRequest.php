<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AksesLogicRequest extends Model
{
    use HasFactory;

    protected $table = 'akses_logic_requests';

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
        'request_type',
        'signature_path', // Tambah path signature
        'posisi_pemohon', // Tambah posisi pemohon
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
        return $this->hasMany(AksesLogicLog::class);
    }

    public function aksesLogicItems(): HasMany
    {
        return $this->hasMany(AksesLogicItem::class);
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

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
