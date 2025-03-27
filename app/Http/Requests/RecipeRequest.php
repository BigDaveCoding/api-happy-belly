<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'recipe_name' => 'required|string|max:255|min:4',
            'recipe_description' => 'required|string|min:10',
            'recipe_cooking_time' => 'required|integer|min:1|max:999',
            'recipe_serves' => 'required|integer|numeric|min:1',
            'recipe_cuisine' => 'required|string',
            'ingredient_name' => 'required|array',
            'ingredient_name.*' => 'required|string|min:1',
            'ingredient_quantity' => 'required|array',
            'ingredient_quantity.*' => 'required|integer|min:1',
            'ingredient_unit' => 'required|array',
            'ingredient_unit.*' => 'nullable|string',
            'ingredient_allergen' => 'required|array',
            'ingredient_allergen.*' => 'required|boolean',
            'cooking_instruction' => 'required|array',
            'cooking_instruction.*' => 'required|string',
            'is_vegetarian' => 'required|boolean',
            'is_vegan' => 'required|boolean',
            'is_gluten_free' => 'required|boolean',
            'is_dairy_free' => 'required|boolean',
            'is_low_fodmap' => 'required|boolean',
            'is_ostomy_friendly' => 'required|boolean',
        ];
    }
}
