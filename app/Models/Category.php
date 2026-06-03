<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'order'])]
class Category extends Model
{
    /**
     * A category has many tryouts.
     */
    public function tryouts(): HasMany
    {
        return $this->hasMany(Tryout::class);
    }

    /**
     * A category has many materials.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}