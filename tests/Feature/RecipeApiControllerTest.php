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
                                    'instruction',
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

    public function test_recipe_api_controller_find_recipe_doesnt_exist(): void
    {
        $response = $this->get('/api/recipes/9999');
        $response->assertStatus(404);
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
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('admin_recipes', 'pagination')
                            ->has('pagination', function (AssertableJson $pagination) {
                                $pagination->hasAll(
                                    'current_page',
                                    'total_recipes',
                                    'next_page_url',
                                    'previous_page_url',
                                    'all_page_urls'
                                );
                            })
                            ->has('admin_recipes', 2, function (AssertableJson $recipes) {
                                $recipes->hasAll('id', 'name', 'image', 'cooking_time', 'serves', 'cuisine', 'dietary_restrictions')
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

    public function test_recipe_api_controller_user_recipes_returns_correct_data(): void
    {
        User::factory()->create(['id' => 2]);
        Recipe::factory()->has(DietaryRestriction::factory())->count(2)->create(['user_id' => 2]);

        $response = $this->get('api/recipes/user/2');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('user_recipes', 'pagination')
                            ->has('pagination', function (AssertableJson $pagination) {
                                $pagination->hasAll(
                                    'current_page',
                                    'total_recipes',
                                    'next_page_url',
                                    'previous_page_url',
                                    'all_page_urls'
                                );
                            })
                            ->has('user_recipes', 2, function (AssertableJson $recipes) {
                                $recipes->hasAll('id', 'name', 'image', 'cooking_time', 'serves', 'cuisine', 'dietary_restrictions')
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

    public function test_recipe_api_controller_user_no_recipes_return_empty_array(): void
    {
        User::factory()->create(['id' => 2]);

        $response = $this->get('/api/recipes/user/2');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('user_recipes', 'pagination')
                            ->where('user_recipes', []);
                    });
            });
    }

    public function test_recipe_api_controller_user_recipes_user_doesnt_exist(): void
    {
        $response = $this->get('/api/recipes/9999');
        $response->assertStatus(404);
    }

    public function test_recipe_api_controller_user_recipes_invalid_id_url(): void
    {
        $response = $this->get('/api/recipes/user/invalid');
        $response->assertStatus(404);
    }

    public function test_recipe_api_controller_create_recipe_successful_all_data_in_databases_checked(): void
    {
        User::factory()->create(['id' => 1]);
        $data = [
            "user_id" => 1,
            "recipe_name" => "postman recipe",
            "recipe_description" => "A classic Italian pasta dish made with a rich and savory meat sauce.",
            "recipe_cooking_time" => 45,
            "recipe_serves" => 4,
            "recipe_cuisine" => "Italian",
            "ingredient_name" => [
                "Spaghetti",
            ],
            "ingredient_quantity" => [
                200,
            ],
            "ingredient_unit" => [
                "g",
            ],
            "ingredient_allergen" => [
                false
            ],
            "cooking_instruction" => [
                "Boil the spaghetti according to package instructions.",
            ],
            "is_vegetarian" => true,
            "is_vegan" => false,
            "is_gluten_free" => false,
            "is_dairy_free" => true,
            "is_low_fodmap" => false,
            "is_ostomy_friendly" => true
        ];

        $response = $this->postJson('/api/recipes/create', $data);
        $response->assertStatus(201)
            ->assertJson(function (AssertableJson $response) {
                $response->has('message');
            });
        $this->assertDatabaseHas('recipes', [
            'id' => 1,
            'name' => 'postman recipe',
            'description' => 'A classic Italian pasta dish made with a rich and savory meat sauce.',
            'cooking_time' => 45,
            'serves' => 4,
            'cuisine' => "Italian",
        ]);
        $this->assertDatabaseHas('ingredients', [
            'id' => 1,
            'name' => 'Spaghetti',
            'food_group' => 'food_group',
            'allergen' => 0
        ]);
        $this->assertDatabaseHas('cooking_instructions', [
            'id' => 1,
            'recipe_id' => 1,
            'step' => 1,
            'instruction' => "Boil the spaghetti according to package instructions.",
        ]);
        $this->assertDatabaseHas('ingredient_recipe', [
            'id' => 1,
            'recipe_id' => 1,
            'ingredient_id' => 1,
            'quantity' => 200,
            'unit' => "g",
        ]);
        $this->assertDatabaseHas('dietary_restrictions', [
            'id' => 1,
            'recipe_id' => 1,
            'is_vegetarian' => true,
            'is_vegan' => false,
            'is_gluten_free' => false,
            'is_dairy_free' => true,
            'is_low_fodmap' => false,
            'is_ostomy_friendly' => true,
        ]);
    }

    public function test_recipe_api_controller_create_recipe_invalid_data(): void
    {
        $data = [
            "user_id" => "one", // Should be an integer
            "recipe_name" => 12345, // Should be a string
            "recipe_description" => false, // Should be a string
            "recipe_cooking_time" => "forty-five", // Should be an integer
            "recipe_serves" => null, // Should be an integer
            "recipe_cuisine" => ["Italian"], // Should be a string
            "ingredient_name" => 100, // Should be an array of strings
            "ingredient_quantity" => "two hundred", // Should be an array of integers
            "ingredient_unit" => false, // Should be an array of strings
            "ingredient_allergen" => "no", // Should be an array of booleans
            "cooking_instruction" => true, // Should be an array of strings
            "is_vegetarian" => "yes", // Should be a boolean
            "is_vegan" => "string", // Should be a boolean
            "is_gluten_free" => "false", // Should be a boolean
            "is_dairy_free" => "sfks", // Should be a boolean
            "is_low_fodmap" => "low", // Should be a boolean
            "is_ostomy_friendly" => [], // Should be a boolean
        ];
        $response = $this->postJson('/api/recipes/create', $data);
        $response->assertStatus(422)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'errors')
                    ->has('errors', function (AssertableJson $errors) {
                        $errors->hasAll(
                            'user_id',
                            'recipe_name',
                            'recipe_description',
                            'recipe_cooking_time',
                            'recipe_serves',
                            'recipe_cuisine',
                            'ingredient_name',
                            'ingredient_quantity',
                            'ingredient_unit',
                            'ingredient_allergen',
                            'cooking_instruction',
                            'is_vegetarian',
                            'is_vegan',
                            'is_gluten_free',
                            'is_dairy_free',
                            'is_low_fodmap',
                            'is_ostomy_friendly',
                        );
                    });
            });
    }
}
