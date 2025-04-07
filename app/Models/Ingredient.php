<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    /** @use HasFactory<\Database\Factories\IngredientFactory> */
    use HasFactory;

    protected $fillable = ['name', 'food_group', 'allergen'];

    protected $hidden = ['created_at', 'updated_at', 'pivot'];

    protected $appends = ['pivot_data'];

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)->withPivot('quantity', 'unit');
    }

    public function getPivotDataAttribute(): ?array
    {
        if (! $this->pivot) {
            return null;
        }

        return [
            'quantity' => $this->pivot->quantity,
            'unit' => $this->pivot->unit,
        ];
    }

    public function foodDiaries(): BelongsToMany
    {
        return $this->belongsToMany(FoodDiary::class, 'food_diary_ingredient', 'ingredient_id', 'food_diary_id');
    }
}
