<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLog extends Model
{
    use HasFactory;

    protected $table = 'access_logs';

    protected $fillable = [
        'vpn_request_id',
        'user_identifier',
        'ip_address',
        'login_time',
        'logout_time',
        'session_duration',
        'status',
        'user_agent',
    ];

    protected $casts = [
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function vpnRequest(): BelongsTo
    {
        return $this->belongsTo(VpnRequest::class);
    }

    public function getSessionDurationFormattedAttribute(): string
    {
        if (!$this->session_duration) {
            return 'N/A';
        }

        $duration = $this->session_duration;
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
