<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FoodDiary extends Model
{
    /** @use HasFactory<\Database\Factories\FoodDiaryFactory> */
    use HasFactory;

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'food_diary_ingredient', 'food_diary_id', 'ingredient_id');
    }
}
