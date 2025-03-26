<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CookingInstruction extends Model
{
    /** @use HasFactory<\Database\Factories\CookingInstructionFactory> */
    use HasFactory;

    protected $fillable = ['recipe_id', 'step', 'instruction'];

    protected $hidden = ['created_at', 'updated_at', 'recipe_id'];

    // Define a One-to-Many relationship (Each step belongs to one recipe)
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
