<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'tryout_id',
    'status',
    'started_at',
    'submitted_at',
    'expires_at',
    'score',
    'total_correct',
    'total_answered',
    'snapshot',
])]
class Attempt extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * An attempt belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * An attempt belongs to a tryout.
     */
    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    /**
     * An attempt has many answers.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}