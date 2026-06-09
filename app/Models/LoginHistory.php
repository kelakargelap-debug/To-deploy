<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'user_id', 'email_or_phone_input', 'activity_type', 'status', 
    'failure_reason', 'ip_address', 'user_agent', 'browser', 'os', 
    'location', 'device_id', 'otp_required', 'otp_passed'
])]
class LoginHistory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'otp_required' => 'boolean',
            'otp_passed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
