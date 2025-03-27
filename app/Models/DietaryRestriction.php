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
        'id',
    ];

    protected $fillable = [
        'is_vegan',
        'is_vegetarian',
        'is_gluten_free',
        'is_dairy_free',
        'is_low_fodmap',
        'is_ostomy_friendly'
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
