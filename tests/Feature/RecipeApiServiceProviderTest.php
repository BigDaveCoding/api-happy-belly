<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Providers\RecipeApiServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RecipeApiServiceProviderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_create_recipe_creates_and_returns_recipe(): void
    {
        $user = User::factory()->create(['id' => 1]);

        $recipeData = [
            'recipe_name' => 'Pasta',
            'recipe_description' => 'Description',
            'recipe_cooking_time' => 30,
            'recipe_serves' => 2,
            'recipe_cuisine' => 'french',
        ];

        $response = RecipeApiServiceProvider::createRecipe($recipeData, $user->id, 'image');

        $this->assertInstanceOf(Recipe::class, $response);
        $this->assertDatabaseHas('recipes', [
            'name' => 'Pasta',
            'description' => 'Description',
            'cooking_time' => 30,
            'serves' => 2,
            'cuisine' => 'french',
            'user_id' => $user->id,
            'image' => 'image',
        ]);
    }

    public function test_create_recipe_user_doesnt_exist(): void
    {
        $recipeData = [
            'recipe_name' => 'Pasta',
            'recipe_description' => 'Description',
            'recipe_cooking_time' => 30,
            'recipe_serves' => 2,
            'recipe_cuisine' => 'french',
        ];
        $this->expectException(QueryException::class);
        $response = RecipeApiServiceProvider::createRecipe($recipeData, 1, '');
    }

    public function test_add_ingredients_successful(): void
    {
        $user = User::factory()->create(['id' => 1]);

        $recipe = Recipe::factory()->create([
            'id' => 1,
            'name' => 'pasta',
            'description' => 'Description',
            'cooking_time' => 30,
            'serves' => 2,
            'cuisine' => 'italian',
            'user_id' => $user->id,
        ]);

        $ingredientData = [
            'ingredient_name' => ['ingredient', 'peas', 'null'],
            'ingredient_quantity' => [1, 2, 2],
            'ingredient_unit' => ['grams', 'cups', null],
            'ingredient_allergen' => [0, 1, 0],
        ];

        RecipeApiServiceProvider::addIngredients($ingredientData, $recipe);

        $this->assertDatabaseHas('ingredients', ['name' => 'ingredient', 'food_group' => 'food_group', 'allergen' => 0]);
        $this->assertDatabaseHas('ingredients', ['name' => 'peas', 'food_group' => 'food_group', 'allergen' => 1]);
        $this->assertDatabaseHas('ingredients', ['name' => 'null', 'food_group' => 'food_group', 'allergen' => 0]);
        $this->assertDatabaseHas('ingredient_recipe', ['recipe_id' => 1, 'ingredient_id' => 1, 'quantity' => 1, 'unit' => 'grams']);
        $this->assertDatabaseHas('ingredient_recipe', ['recipe_id' => 1, 'ingredient_id' => 2, 'quantity' => 2, 'unit' => 'cups']);
        $this->assertDatabaseHas('ingredient_recipe', ['recipe_id' => 1, 'ingredient_id' => 3, 'quantity' => 2, 'unit' => null]);
    }

    public function test_add_dietary_restrictions_successful(): void
    {
        $recipe = Recipe::factory()->create(['id' => 1]);
        $data = [
            'is_vegan' => true,
            'is_vegetarian' => true,
            'is_gluten_free' => true,
            'is_dairy_free' => true,
            'is_low_fodmap' => true,
            'is_ostomy_friendly' => true,
        ];
        RecipeApiServiceProvider::addDietaryRestrictions($data, $recipe);
        $this->assertDatabaseHas('dietary_restrictions', $data);
    }

    public function test_add_cooking_instructions_successful(): void
    {
        $recipe = Recipe::factory()->create(['id' => 1]);
        $data = [
            'cooking_instruction' => [
                'instruction 1',
                'instruction 2',
                'instruction 3',
            ],
        ];
        RecipeApiServiceProvider::addCookingInstructions($data, $recipe);
        $this->assertDatabaseHas('cooking_instructions', ['id' => 1, 'recipe_id' => 1, 'step' => 1, 'instruction' => 'instruction 1']);
        $this->assertDatabaseHas('cooking_instructions', ['id' => 2, 'recipe_id' => 1, 'step' => 2, 'instruction' => 'instruction 2']);
        $this->assertDatabaseHas('cooking_instructions', ['id' => 3, 'recipe_id' => 1, 'step' => 3, 'instruction' => 'instruction 3']);
    }
}
