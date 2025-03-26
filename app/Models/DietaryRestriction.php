<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietaryRestriction extends Model
{
    /** @use HasFactory<\Database\Factories\DietaryRestrictionFactory> */
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'recipe_id',
        'id'
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
