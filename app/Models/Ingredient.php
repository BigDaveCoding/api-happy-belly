<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    /** @use HasFactory<\Database\Factories\IngredientFactory> */
    use HasFactory;

    protected $fillable = ['name', 'food_group', 'allergen'];

    protected $hidden = ['created_at', 'updated_at'];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class)->withPivot('quantity', 'unit');
    }
}
