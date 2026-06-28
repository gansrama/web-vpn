<?php

namespace App\Models;

use App\Models\VpnRequest;
use App\Models\AksesLogicRequest;
use App\Models\TeleworkingRequest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'nama_lengkap',
        'nomor_ktp',
        'email',
        'username_vpn',
        'posisi_jabatan',
        'nama_organisasi',
        'nomer_hp_wa',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function vpnRequests(): HasMany
    {
        return $this->hasMany(VpnRequest::class);
    }

    public function aksesLogicRequests(): HasMany
    {
        return $this->hasMany(AksesLogicRequest::class);
    }

    public function teleworkingRequests(): HasMany
    {
        return $this->hasMany(TeleworkingRequest::class);
    }
}
