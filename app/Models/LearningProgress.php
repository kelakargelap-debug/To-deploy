<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'material_id',
    'completed_at',
    'last_opened_at',
])]
class LearningProgress extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'learning_progress';
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'last_opened_at' => 'datetime',
        ];
    }

    /**
     * A learning progress record belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A learning progress record belongs to a material.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}