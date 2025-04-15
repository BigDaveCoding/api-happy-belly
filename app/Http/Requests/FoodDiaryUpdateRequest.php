<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodDiaryUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|integer|exists:users,id',
            'diary_entry' => 'sometimes|nullable|string|max:5000',
            'diary_meal_type' => 'sometimes|nullable|string',
            'diary_date' => 'sometimes|nullable|date',
            'diary_time' => 'sometimes|nullable|string',
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

            if (is_array($names) && is_array($quantities) && is_array($units) && is_array($allergens)) {
                $array_lengths = [
                    count($names),
                    count($quantities),
                    count($units),
                    count($allergens),
                ];
                $uniqueLengths = array_unique($array_lengths);
                if (count($uniqueLengths) > 1) {
                    $validator->errors()->add(
                        'diary_ingredient_arrays',
                        'Ingredient arrays (name, quantity, unit, allergen) must all have the same length.'
                    );
                }
            }
        });
    }
}
