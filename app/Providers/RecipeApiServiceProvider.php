<?php

namespace App\Providers;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RecipeApiServiceProvider
{
    public static function pagination(LengthAwarePaginator $data): array
    {
        return [
            'current_page' => $data->currentPage(),
            'total_recipes' => $data->total(),
            'next_page_url' => $data->nextPageUrl(),
            'previous_page_url' => $data->previousPageUrl(),
            'all_page_urls' => $data->getUrlRange(1, $data->lastPage()),
        ];
    }

    public static function paginationCollection(LengthAwarePaginator $data): Collection
    {
        return $data->getCollection()->transform(function ($recipe) {
            return $recipe->setHidden(['description', 'user_id', 'created_at', 'updated_at']);
        });
    }

    public static function addIngredients(array $ingredientData, Recipe $recipe): void
    {
        $ingredientNames = $ingredientData['ingredient_name'];
        $ingredientQuantity = $ingredientData['ingredient_quantity'];
        $ingredientUnit = $ingredientData['ingredient_unit'];
        $ingredientAllergen = $ingredientData['ingredient_allergen'];

        foreach ($ingredientNames as $index => $ingredientName) {

            $ingredient = Ingredient::firstOrCreate(['name' => $ingredientName,
                'food_group' => null, // static value
                'allergen' => $ingredientAllergen[$index]]);

            $recipe->ingredients()->attach($ingredient, [
                'quantity' => $ingredientQuantity[$index],
                'unit' => $ingredientUnit[$index],
            ]);
        }
    }

    public static function addCookingInstructions(array $cookingInstructionData, Recipe $recipe): void
    {
        $cookingInstructions = $cookingInstructionData['cooking_instruction'];

        foreach ($cookingInstructions as $index => $instruction) {
            $recipe->cookingInstructions()->create([
                'step' => $index + 1,
                'instruction' => $instruction,
            ]);
        }
    }

    public static function createRecipe(array $recipeData, int $userId, string $imageUrl): Recipe
    {

        $recipe = new Recipe;
        $recipe->name = $recipeData['recipe_name'];
        $recipe->description = $recipeData['recipe_description'];
        $recipe->image = $imageUrl;
        $recipe->cooking_time = $recipeData['recipe_cooking_time'];
        $recipe->serves = $recipeData['recipe_serves'];
        $recipe->cuisine = $recipeData['recipe_cuisine'];
        $recipe->user_id = $userId;

        $recipe->save();

        return $recipe;
    }

    public static function addDietaryRestrictions(array $dietaryRestrictionData, Recipe $recipe): void
    {
        $recipe->dietaryRestrictions()->create([
            'is_vegan' => $dietaryRestrictionData['is_vegan'],
            'is_vegetarian' => $dietaryRestrictionData['is_vegetarian'],
            'is_gluten_free' => $dietaryRestrictionData['is_gluten_free'],
            'is_dairy_free' => $dietaryRestrictionData['is_dairy_free'],
            'is_low_fodmap' => $dietaryRestrictionData['is_low_fodmap'],
            'is_ostomy_friendly' => $dietaryRestrictionData['is_ostomy_friendly'],
        ]);
    }
}
