<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tryout_id',
    'type',
    'content',
    'image_url',
    'order',
    'explanation',
    'points',
])]
class Question extends Model
{
    /**
     * A question belongs to a tryout.
     */
    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    /**
     * A question has many options.
     */
    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }
}