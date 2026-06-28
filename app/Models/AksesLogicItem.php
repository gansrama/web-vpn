<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AksesLogicItem extends Model
{
    use HasFactory;

    protected $table = 'akses_logic_items';

    protected $fillable = [
        'akses_logic_request_id',
        'nama_sistem',
        'ip_address',
        'jenis_akses',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function aksesLogicRequest(): BelongsTo
    {
        return $this->belongsTo(AksesLogicRequest::class);
    }
}
