<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id', 'device_id', 'device_name', 'browser', 'os', 
    'user_agent', 'last_ip', 'last_location', 'trusted_at', 
    'last_seen_at', 'revoked_at'
])]
class TrustedDevice extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'trusted_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
