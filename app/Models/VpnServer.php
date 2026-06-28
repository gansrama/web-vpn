<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VpnServer extends Model
{
    use HasFactory;

    protected $table = 'vpn_servers';

    protected $fillable = [
        'nama_sistem',
        'server_location',
        'ip_address',
        'project',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
