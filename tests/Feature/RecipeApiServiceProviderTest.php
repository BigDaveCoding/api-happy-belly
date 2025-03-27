<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Providers\RecipeApiServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
            'recipe_cuisine' => 'french'
        ];

        $response = RecipeApiServiceProvider::createRecipe($recipeData, $user->id);

        $this->assertInstanceOf(Recipe::class, $response);
        $this->assertDatabaseHas('recipes', [
            'name' => 'Pasta',
            'description' => 'Description',
            'cooking_time' => 30,
            'serves' => 2,
            'cuisine' => 'french',
            'user_id' => $user->id,
        ]);
    }

    public function test_create_recipe_user_doesnt_exist(): void
    {
        $recipeData = [
            'recipe_name' => 'Pasta',
            'recipe_description' => 'Description',
            'recipe_cooking_time' => 30,
            'recipe_serves' => 2,
            'recipe_cuisine' => 'french'
        ];
        $this->expectException(QueryException::class);
        $response = RecipeApiServiceProvider::createRecipe($recipeData, 1);
    }

    public function test_add_ingredients_adds_ingredients_to_database(): void
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
}
