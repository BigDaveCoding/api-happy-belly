<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FoodDiary extends Model
{
    /** @use HasFactory<\Database\Factories\FoodDiaryFactory> */
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'food_diary_ingredient', 'food_diary_id', 'ingredient_id')->withPivot('quantity', 'unit');
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'food_diary_recipe', 'food_diary_id', 'recipe_id');
    }
}
