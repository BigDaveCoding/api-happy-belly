<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Providers\RecipeApiServiceProvider;
use ErrorException;
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
}
