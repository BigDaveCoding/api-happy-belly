<?php

namespace Tests\Feature;

use App\Models\CookingInstruction;
use App\Models\DietaryRestriction;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class RecipeApiControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_recipe_api_controller_all_recipes_returned_successfully(): void
    {
        Recipe::factory()->has(DietaryRestriction::factory())->count(20)->create();
        $response = $this->get('/api/recipes');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->whereAllType([
                        'message' => 'string',
                        'data' => 'array',
                    ])
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('recipes', 'pagination')
                            ->has('pagination', function (AssertableJson $pagination) {
                                $pagination->hasAll(
                                    'current_page',
                                    'total_recipes',
                                    'next_page_url',
                                    'previous_page_url',
                                    'all_page_urls'
                                )
                                    ->whereAllType([
                                        'current_page' => 'integer',
                                        'total_recipes' => 'integer',
                                        'next_page_url' => 'string',
                                        'previous_page_url' => 'null',
                                        'all_page_urls' => 'array',
                                    ]);
                            })
                            ->has('recipes', 5, function (AssertableJson $data) {
                                $data->hasAll(
                                    'id',
                                    'name',
                                    'image',
                                    'cooking_time',
                                    'serves',
                                    'cuisine',
                                    'dietary_restrictions'
                                )
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string',
                                        'image' => 'string',
                                        'cooking_time' => 'integer',
                                        'serves' => 'integer',
                                        'cuisine' => 'string',
                                        'dietary_restrictions' => 'array',
                                    ])
                                    ->has('dietary_restrictions', function (AssertableJson $dietaryRestrictions) {
                                        $dietaryRestrictions->hasAll([
                                            'is_vegetarian',
                                            'is_vegan',
                                            'is_gluten_free',
                                            'is_dairy_free',
                                            'is_low_fodmap',
                                            'is_ostomy_friendly',
                                        ]);
                                    });
                            });
                    });
            });
    }

    public function test_recipe_api_controller_all_recipes_empty_response(): void
    {
        $response = $this->get('/api/recipes');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->whereAllType([
                        'message' => 'string',
                        'data' => 'array',
                    ])
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('recipes', 'pagination')
                            ->where('recipes', [])
                            ->has('pagination', function (AssertableJson $pagination) {
                                $pagination->hasAll(
                                    'current_page',
                                    'total_recipes',
                                    'next_page_url',
                                    'previous_page_url',
                                    'all_page_urls'
                                )
                                    ->whereAllType([
                                        'current_page' => 'integer',
                                        'total_recipes' => 'integer',
                                        'next_page_url' => 'null',
                                        'previous_page_url' => 'null',
                                        'all_page_urls' => 'array',
                                    ]);
                            });
                    });
            });
    }

    public function test_recipe_api_controller_find_recipe_correct_response_and_datatypes(): void
    {
        $recipe = Recipe::factory()->has(DietaryRestriction::factory())->create(['id' => 1]);
        $ingredient = Ingredient::factory()->create(['id' => 1]);
        CookingInstruction::factory()->create(['id' => 1, 'recipe_id' => $recipe->id]);

        $recipe->ingredients()->attach($ingredient, ['quantity' => 10, 'unit' => 'grams']);

        $response = $this->get('/api/recipes/1');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll(
                            'id',
                            'name',
                            'description',
                            'image',
                            'cooking_time',
                            'serves',
                            'cuisine',
                            'user_id',
                            'ingredients',
                            'cooking_instructions',
                            'dietary_restrictions'
                        )
                            ->whereAllType([
                                'id' => 'integer',
                                'name' => 'string',
                                'description' => 'string',
                                'image' => 'string',
                                'cooking_time' => 'integer',
                                'serves' => 'integer',
                                'cuisine' => 'string',
                                'user_id' => 'integer',
                                'ingredients' => 'array',
                                'cooking_instructions' => 'array',
                                'dietary_restrictions' => 'array',
                            ])
                            ->has('cooking_instructions', 1, function (AssertableJson $cookingInstructions) {
                                $cookingInstructions->hasAll([
                                    'id',
                                    'step',
                                    'instruction'
                                ]);
                            })
                            ->has('dietary_restrictions', function (AssertableJson $dietaryRestrictions) {
                                $dietaryRestrictions->hasAll([
                                    'is_vegetarian',
                                    'is_vegan',
                                    'is_gluten_free',
                                    'is_dairy_free',
                                    'is_low_fodmap',
                                    'is_ostomy_friendly',
                                ]);
                            })
                            ->has('ingredients', 1, function (AssertableJson $ingredients) {
                                $ingredients->hasAll(
                                    'id',
                                    'name',
                                    'food_group',
                                    'allergen',
                                    'pivot_data'
                                )
                                    ->whereAllType([
                                        'id' => 'integer',
                                        'name' => 'string',
                                        'food_group' => 'string',
                                        'allergen' => 'integer',
                                        'pivot_data' => 'array',
                                    ])
                                    ->has('pivot_data', function (AssertableJson $pivotData) {
                                        $pivotData->hasAll('quantity', 'unit')
                                            ->whereAllType([
                                                'quantity' => 'integer',
                                                'unit' => 'string',
                                            ]);
                                    });
                            });
                    });
            });
    }

    public function test_recipe_api_controller_admin_recipes_returns_correct_data(): void
    {
        User::factory()->create(['id' => 1]);
        User::factory()->create(['id' => 2]);
        Recipe::factory()->has(DietaryRestriction::factory())->count(2)->create(['user_id' => 1]);
        Recipe::factory()->count(2)->create(['user_id' => 2]);

        $response = $this->get('/api/recipes/admin');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', 2, function (AssertableJson $data) {
                        $data->hasAll('id', 'name', 'image', 'cooking_time', 'serves', 'cuisine', 'dietary_restrictions')
                            ->has('dietary_restrictions', function (AssertableJson $dietaryRestrictions) {
                                $dietaryRestrictions->hasAll([
                                    'is_vegetarian',
                                    'is_vegan',
                                    'is_gluten_free',
                                    'is_dairy_free',
                                    'is_low_fodmap',
                                    'is_ostomy_friendly',
                                ]);
                            });
                    });
            });
    }

    public function test_recipe_api_controller_user_recipes_returns_correct_data(): void
    {
        User::factory()->create(['id' => 2]);
        Recipe::factory()->count(2)->create(['user_id' => 2]);

        $response = $this->get('api/recipes/user/2');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', 2, function (AssertableJson $data) {
                        $data->hasAll('id', 'name', 'image', 'cooking_time', 'serves', 'cuisine');
                    });
            });
    }

    public function test_recipe_api_controller_user_no_recipes_return_empty_array(): void
    {
        User::factory()->create(['id' => 2]);

        $response = $this->get('/api/recipes/user/2');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->where('data', []);
            });
    }
}
