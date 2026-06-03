<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category_id',
    'title',
    'slug',
    'description',
    'status',
    'required_tier',
    'duration_minutes',
    'total_questions',
    'passing_score',
    'randomize_order',
    'show_result',
])]
class Tryout extends Model
{
    /**
     * A tryout belongs to a category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A tryout has many questions.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * A tryout has many attempts.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }
}