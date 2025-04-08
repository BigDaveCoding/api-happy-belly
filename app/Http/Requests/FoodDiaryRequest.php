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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $names = $this->input('diary_ingredient_name', []);
            $quantities = $this->input('diary_ingredient_quantity', []);
            $units = $this->input('diary_ingredient_unit', []);
            $allergens = $this->input('diary_ingredient_allergen', []);

            $arrays = [
                'diary_ingredient_name' => $names,
                'diary_ingredient_quantity' => $quantities,
                'diary_ingredient_unit' => $units,
                'diary_ingredient_allergen' => $allergens,
            ];

            $lengths = array_map('count', $arrays);
            $uniqueLengths = array_unique($lengths);

            // If more than one unique length, the arrays are mismatched
            if (count($uniqueLengths) > 1) {
                $validator->errors()->add(
                    'diary_ingredient_name',
                    'Ingredient arrays (name, quantity, unit, allergen) must all be the same length.'
                );
            }
        });
    }
}
