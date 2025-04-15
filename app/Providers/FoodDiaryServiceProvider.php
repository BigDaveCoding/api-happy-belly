<?php

namespace App\Providers;

use App\Models\FoodDiary;
use App\Models\Ingredient;
use Illuminate\Support\ServiceProvider;

class FoodDiaryServiceProvider extends ServiceProvider
{
    public static function createFoodDiaryEntry(array $validatedData): FoodDiary
    {
        $entry = new FoodDiary;
        $entry->user_id = $validatedData['user_id'];
        $entry->entry = $validatedData['diary_entry'];
        $entry->meal_type = $validatedData['diary_meal_type'];
        $entry->entry_date = $validatedData['diary_date'];
        $entry->entry_time = $validatedData['diary_time'];
        $entry->save();

        return $entry;
    }

    public static function createIngredientsAddPivot(array $validatedData, FoodDiary $entry): void
    {
        foreach ($validatedData['diary_ingredient_name'] as $index => $ingredient) {
            $ingredient = Ingredient::firstOrCreate([
                'name' => $ingredient,
                'food_group' => null, // static value
                'allergen' => $validatedData['diary_ingredient_allergen'][$index],
            ]);
            $entry->ingredients()->attach($ingredient, [
                'quantity' => $validatedData['diary_ingredient_quantity'][$index],
                'unit' => $validatedData['diary_ingredient_unit'][$index],
            ]);
        }
    }

    public static function addRecipePivot(array $validatedData, FoodDiary $entry): void
    {
        foreach ($validatedData['diary_recipes'] as $recipe) {
            $entry->recipes()->attach($recipe);
        }
    }
}
