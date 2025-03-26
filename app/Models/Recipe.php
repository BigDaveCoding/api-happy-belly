<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;

    protected $hidden = ['created_at', 'updated_at'];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('quantity', 'unit');
    }

    public function cookingInstructions()
    {
        return $this->hasMany(CookingInstruction::class);
    }
}
