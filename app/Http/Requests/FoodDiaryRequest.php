<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodDiaryRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'diary_entry' => 'required|string|max:5000',
            'diary_meal_type' => 'required|string',
            'diary_date' => 'required|date',
            'diary_time' => 'required|string',
            'diary_ingredient_name' => 'nullable|array',
            'diary_ingredient_name.*' => 'nullable|string',
            'diary_ingredient_quantity' => 'nullable|array',
            'diary_ingredient_quantity.*' => 'nullable|integer',
            'diary_ingredient_unit' => 'nullable|array',
            'diary_ingredient_unit.*' => 'nullable|string',
            'diary_ingredient_allergen' => 'nullable|array',
            'diary_ingredient_allergen.*' => 'nullable|boolean',
            'diary_recipes' => 'nullable|array',
            'diary_recipes.*' => 'nullable|integer|exists:recipes,id',
        ];
    }
}
