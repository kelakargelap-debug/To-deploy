<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id', 'trusted_device_id', 'session_token_hash', 'ip_address', 
    'user_agent', 'last_seen_at', 'idle_expires_at', 'absolute_expires_at', 
    'revoked_at', 'revoked_reason'
])]
class AuthSession extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'idle_expires_at' => 'datetime',
            'absolute_expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
