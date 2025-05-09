<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('quantity', 'unit');
    }

    public function cookingInstructions(): HasMany
    {
        return $this->hasMany(CookingInstruction::class);
    }

    public function dietaryRestrictions(): HasOne
    {
        return $this->hasOne(DietaryRestriction::class);
    }

    public function favouritedByUsers(): BelongsToMany
    {
        return $this->BelongsToMany(User::class, 'favourite_recipes', 'recipe_id', 'user_id');
    }

    public function foodDiaries(): BelongsToMany
    {
        return $this->belongsToMany(FoodDiary::class, 'food_diary_recipe', 'recipe_id', 'food_diary_id');
    }
}
