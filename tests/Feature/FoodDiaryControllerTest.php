<?php

namespace Tests\Feature;

use App\Models\FoodDiary;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FoodDiaryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_food_diary_controller_all_entries_by_user(): void
    {
        // create user with id of 1
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);
        // create 3 food diary entries belonging to $user
        FoodDiary::factory()->count(3)->create(['user_id' => $user->id]);
        // assert that table has three entries
        $this->assertDatabaseCount('food_diaries', 3);

        // get response from api route
        $response = $this->getJson("/api/food-diary/{$user->id}");
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('entries', 'pagination')
                            ->has('entries', 3, function (AssertableJson $entry) {
                                $entry->hasAll(
                                    'id',
                                    'meal_type',
                                    'entry_date',
                                    'entry_time',
                                );
                            });
                    });
            });
    }

    public function test_user_cannot_access_other_users_diary(): void
    {
        $user = User::factory()->create(['id' => 1]);
        $otherUser = User::factory()->create(['id' => 2]);

        FoodDiary::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        $response = $this->getJson("/api/food-diary/{$otherUser->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized - These are not your diary entries',
            ]);
    }

    public function test_food_diary_pagination_limit(): void
    {
        $user = User::factory()->create();
        FoodDiary::factory()->count(8)->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->getJson("/api/food-diary/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll('entries', 'pagination')
                            ->has('entries', 5);
                    });
            });
    }

    public function test_food_diary_hides_sensitive_fields(): void
    {
        $user = User::factory()->create();
        FoodDiary::factory()->create([
            'user_id' => $user->id,
            'entry' => 'This should not show',
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/food-diary/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonMissing([
                'user_id',
                'entry',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_food_diary_entries_user_doesnt_exist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/food-diary/1000');
        $response->assertStatus(404);
    }

    public function test_food_diary_controller_single_entry_by_user_correct_data_with_recipe_and_ingredient(): void
    {
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);

        $diaryEntry = FoodDiary::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create();
        $ingredient = Ingredient::factory()->create();

        // Inject data into pivot tables
        $diaryEntry->recipes()->attach($recipe);
        $diaryEntry->ingredients()->attach($ingredient);
        $recipe->ingredients()->attach($ingredient, ['quantity' => 1, 'unit' => 'g']);

        // check databases has all expected entries
        $this->assertDatabaseHas('ingredient_recipe', [
            'ingredient_id' => $ingredient->id,
            'recipe_id' => $recipe->id,
            'quantity' => 1,
            'unit' => 'g',
        ]);
        $this->assertDatabaseHas('food_diaries', [
            'id' => 1,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('food_diary_ingredient', [
            'food_diary_id' => $diaryEntry->id,
            'ingredient_id' => $ingredient->id,
        ]);
        $this->assertDatabaseHas('food_diary_recipe', [
            'food_diary_id' => $diaryEntry->id,
            'recipe_id' => $recipe->id,
        ]);

        $response = $this->getJson('/api/food-diary/entry/1');
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll(
                            'id',
                            'user_id',
                            'entry',
                            'meal_type',
                            'entry_date',
                            'entry_time',
                            'ingredients',
                            'recipes',
                        )
                            ->has('recipes', 1, function (AssertableJson $recipe) {
                                $recipe->hasAll(
                                    'id',
                                    'name',
                                    'ingredients'
                                )
                                    ->has('ingredients', 1, function (AssertableJson $ingredients) {
                                        $ingredients->hasAll(
                                            'id',
                                            'name',
                                            'pivot_data',
                                        );
                                    });
                            })
                            ->has('ingredients', 1, function (AssertableJson $ingredients) {
                                $ingredients->hasAll(
                                    'id',
                                    'name',
                                    'pivot_data'
                                );
                            });
                    });
            });
    }

    public function test_user_cannot_view_another_users_diary_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $entry = FoodDiary::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        $response = $this->getJson("/api/food-diary/entry/{$entry->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized - These are not your diary entries',
            ]);
    }

    public function test_diary_entry_with_no_recipes_returns_correctly(): void
    {
        $user = User::factory()->create();
        $entry = FoodDiary::factory()->create(['user_id' => $user->id]);

        $ingredient = Ingredient::factory()->create();
        $entry->ingredients()->attach($ingredient);

        $this->actingAs($user);

        $response = $this->getJson("/api/food-diary/entry/{$entry->id}");
        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll(
                            'id',
                            'user_id',
                            'entry',
                            'meal_type',
                            'entry_date',
                            'entry_time',
                            'ingredients',
                            'recipes',
                        )
                            ->where('recipes', []);
                    });
            });
    }

    public function test_diary_entry_with_no_ingredients_returns_correctly(): void
    {
        $user = User::factory()->create();
        $entry = FoodDiary::factory()->create(['user_id' => $user->id]);

        $recipe = Recipe::factory()->create();
        $entry->recipes()->attach($recipe);

        $this->actingAs($user);

        $response = $this->getJson("/api/food-diary/entry/{$entry->id}");

        $response->assertStatus(200)
            ->assertJson(function (AssertableJson $response) {
                $response->hasAll('message', 'data')
                    ->has('data', function (AssertableJson $data) {
                        $data->hasAll(
                            'id',
                            'user_id',
                            'entry',
                            'meal_type',
                            'entry_date',
                            'entry_time',
                            'ingredients',
                            'recipes',
                        )
                            ->where('ingredients', []);
                    });
            });
    }

    public function test_diary_entry_does_not_exist(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/food-diary/entry/1000');
        $response->assertStatus(404);
    }

    public function test_diary_entry_create_success_with_additional_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Recipe::factory()->create(['id' => 1]);

        $data = [
            'user_id' => 1,
            'diary_entry' => 'this is the food diary entry',
            'diary_meal_type' => 'breakfast',
            'diary_date' => '2025-04-01',
            'diary_time' => '16:10:10',
            'diary_ingredient_name' => [
                'Ingredient One',
                'Ingredient Two',
            ],
            'diary_ingredient_quantity' => [
                1,
                2,
            ],
            'diary_ingredient_unit' => [
                null,
                'cups',
            ],
            'diary_ingredient_allergen' => [
                0,
                1,
            ],
            'diary_recipes' => [
                1,
            ],
        ];

        $this->assertDatabaseEmpty('food_diaries');
        $this->assertDatabaseEmpty('food_diary_ingredient');
        $this->assertDatabaseEmpty('food_diary_recipe');

        $response = $this->postJson('/api/food-diary/create', $data);
        $response->assertStatus(201)
            ->assertJson(function (AssertableJson $response) {
                $response->has('message');
            });

        $this->assertDatabaseHas('food_diaries', [
            'user_id' => 1,
            'entry' => 'this is the food diary entry',
            'meal_type' => 'breakfast',
            'entry_date' => '2025-04-01',
            'entry_time' => '16:10:10',
        ]);

        $this->assertDatabaseHas('food_diary_ingredient', [
            'food_diary_id' => 1,
            'ingredient_id' => 1,
            'quantity' => 1,
            'unit' => null,
        ]);

        $this->assertDatabaseHas('food_diary_recipe', [
            'food_diary_id' => 1,
            'recipe_id' => 1,
        ]);
    }

    public function test_diary_entry_create_success_only_required_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Recipe::factory()->create(['id' => 1]);

        $data = [
            'user_id' => 1,
            'diary_entry' => 'this is the food diary entry',
            'diary_meal_type' => 'breakfast',
            'diary_date' => '2025-04-01',
            'diary_time' => '16:10:10',
            'diary_ingredient_name' => [],
            'diary_ingredient_quantity' => [],
            'diary_ingredient_unit' => [],
            'diary_ingredient_allergen' => [],
            'diary_recipes' => [],
        ];

        $this->assertDatabaseEmpty('food_diaries');

        $response = $this->postJson('/api/food-diary/create', $data);
        $response->assertStatus(201)
            ->assertJson(function (AssertableJson $response) {
                $response->has('message');
            });

        $this->assertDatabaseHas('food_diaries', [
            'user_id' => 1,
            'entry' => 'this is the food diary entry',
            'meal_type' => 'breakfast',
            'entry_date' => '2025-04-01',
            'entry_time' => '16:10:10',
        ]);

        $this->assertDatabaseEmpty('food_diary_ingredient');
        $this->assertDatabaseEmpty('food_diary_recipe');
    }

    public function test_create_food_diary_entry_validation_working(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'user_id' => 'not-a-number', // should be integer
            'diary_entry' => 12345, // should be string
            'diary_meal_type' => ['breakfast'], // should be string, not array
            'diary_date' => 123456, // should be string date format
            'diary_time' => false, // should be time string
            'diary_ingredient_name' => 'he', // should be array of strings
            'diary_ingredient_quantity' => 1, // should be array of numbers
            'diary_ingredient_unit' => 22, // should be array of strings/nulls
            'diary_ingredient_allergen' => 'string', // should be array of 0/1 values
            'diary_recipes' => 'eee', // should be array of recipe IDs (ints)
        ];

        $response = $this->postJson('/api/food-diary/create', $data);
        $response->assertInvalid([
            'user_id',
            'diary_entry',
            'diary_meal_type',
            'diary_date',
            'diary_time',
            'diary_ingredient_name',
            'diary_ingredient_quantity',
            'diary_ingredient_unit',
            'diary_ingredient_allergen',
            'diary_recipes',
        ]);
    }

    public function test_array_data_validation_working(): void
    {
        $user = User::factory()->create(['id' => 1]);
        $this->actingAs($user);

        $data = [
            'user_id' => 1,
            'diary_entry' => 'this is the food diary entry',
            'diary_meal_type' => 'breakfast',
            'diary_date' => '2025-04-01',
            'diary_time' => '16:10:10',
            'diary_ingredient_name' => [300], // invalid data inside array
            'diary_ingredient_quantity' => [true], // invalid data inside array
            'diary_ingredient_unit' => [false], // invalid data inside array
            'diary_ingredient_allergen' => ['string'], // invalid data inside array
            'diary_recipes' => ['ahh!'], // invalid data inside array
        ];

        $response = $this->postJson('/api/food-diary/create', $data);
        $response->assertInvalid([
            'diary_ingredient_name.0',
            'diary_ingredient_unit.0',
            'diary_ingredient_allergen.0',
            'diary_recipes.0', ]);
    }

    public function test_cannot_create_entry_for_different_user(): void
    {
        $user = User::factory()->create(['id' => 1]);
        User::factory()->create(['id' => 2]);
        $this->actingAs($user);

        $data = [
            'user_id' => 2,
            'diary_entry' => 'this is the food diary entry',
            'diary_meal_type' => 'breakfast',
            'diary_date' => '2025-04-01',
            'diary_time' => '16:10:10',
            'diary_ingredient_name' => [],
            'diary_ingredient_quantity' => [],
            'diary_ingredient_unit' => [],
            'diary_ingredient_allergen' => [],
            'diary_recipes' => [],
        ];

        $response = $this->postJson('/api/food-diary/create', $data);
        $response->assertStatus(401)
            ->assertJson(function (AssertableJson $response) {
                $response->where('message', 'Unauthorized - Cannot make an entry for another user');
            });
    }

    public function test_create_diary_entry_with_mismatched_ingredient_arrays_should_fail(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'user_id' => $user->id,
            'diary_entry' => 'Test mismatch',
            'diary_meal_type' => 'lunch',
            'diary_date' => '2025-04-01',
            'diary_time' => '12:00:00',
            'diary_ingredient_name' => ['Ingredient A', 'Ingredient B'],
            'diary_ingredient_quantity' => [1], // mismatched
            'diary_ingredient_unit' => ['g', 'ml'],
            'diary_ingredient_allergen' => [0, 1],
            'diary_recipes' => [],
        ];

        $response = $this->postJson('/api/food-diary/create', $data);

        $response->assertInvalid(['diary_ingredient_arrays']);
    }

    public function test_food_diary_entry_can_be_updated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $diary = FoodDiary::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('food_diaries', [
            'id' => $diary->id,
            'entry' => $diary->entry,
            'meal_type' => $diary->meal_type,
            'entry_date' => $diary->entry_date,
            'entry_time' => $diary->entry_time,
        ]);

        $ingredient = Ingredient::factory()->create([
            'id' => 1,
            'name' => 'test',
            'allergen' => 0,
            'food_group' => 'food_group',
        ]);

        $recipe = Recipe::factory()->create(['id' => 1]);

        $data = [
            'user_id' => $user->id,
            'diary_entry' => 'Updated diary entry',
            'diary_meal_type' => 'lunch',
            'diary_date' => date('Y-m-d'),
            'diary_time' => date('H-i-s'),
            'diary_ingredient_name' => [$ingredient->name],
            'diary_ingredient_quantity' => [1],
            'diary_ingredient_unit' => ['cup'],
            'diary_ingredient_allergen' => [$ingredient->allergen],
            'diary_recipes' => [$recipe->id],
        ];

        $response = $this->patchJson("/api/food-diary/update/$diary->id", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('food_diaries', [
            'id' => $diary->id,
            'entry' => 'Updated diary entry',
            'meal_type' => 'lunch',
            'entry_date' => date('Y-m-d'),
            'entry_time' => date('H-i-s'),
        ]);

        $this->assertDatabaseHas('food_diary_ingredient', [
            'food_diary_id' => $diary->id,
            'ingredient_id' => $ingredient->id,
        ]);

        $this->assertDatabaseHas('food_diary_recipe', [
            'food_diary_id' => $diary->id,
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_diary_partial_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $diary = FoodDiary::factory()->create([
            'user_id' => $user->id,
            'entry' => 'Original entry',
        ]);

        $data = [
            'user_id' => $user->id,
            'diary_entry' => 'Partially updated entry',
            'diary_ingredient_name' => [],
            'diary_ingredient_quantity' => [],
            'diary_ingredient_unit' => [],
            'diary_ingredient_allergen' => [],
            'diary_recipes' => [],
        ];

        $response = $this->patchJson("/api/food-diary/update/{$diary->id}", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('food_diaries', [
            'id' => $diary->id,
            'entry' => 'Partially updated entry',
        ]);
    }

    public function test_diary_update_validation_working(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $diary = FoodDiary::factory()->create(['user_id' => $user->id]);

        $invalidData = [
            'user_id' => 'not-a-number',
            'diary_entry' => 1004829474,
            'diary_meal_type' => true,
            'diary_date' => 'not-a-date',
            'diary_time' => false,
            'diary_ingredient_name' => 'not an array',
            'diary_ingredient_quantity' => 'not an array',
            'diary_ingredient_unit' => 'not an array',
            'diary_ingredient_allergen' => 'not an array',
            'diary_recipes' => 'not-an-array',
        ];

        $response = $this->patchJson("/api/food-diary/update/{$diary->id}", $invalidData);

        $response->assertInvalid([
            'user_id',
            'diary_entry',
            'diary_meal_type',
            'diary_date',
            'diary_time',
            'diary_ingredient_name',
            'diary_ingredient_quantity',
            'diary_ingredient_unit',
            'diary_ingredient_allergen',
            'diary_recipes',
        ]);
    }

    public function test_diary_update_array_lengths_validation_working(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $diary = FoodDiary::factory()->create(['user_id' => $user->id]);

        $data = [
            'user_id' => $user->id,
            'diary_ingredient_name' => ['Carrot', 'Broccoli'],
            'diary_ingredient_quantity' => [1], // mismatched length
            'diary_ingredient_unit' => ['cup', 'g'],
            'diary_ingredient_allergen' => [0, 0],
        ];

        $response = $this->patchJson("/api/food-diary/update/{$diary->id}", $data);

        $response->assertInvalid([
            'diary_ingredient_arrays',
        ]);
    }

    public function test_diary_update_ingredients_recipes_detached_properly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // create diary, recipe and ingredient
        $diary = FoodDiary::factory()->create(['user_id' => $user->id]);
        $recipe = Recipe::factory()->create();
        $ingredient = Ingredient::factory()->create();

        // attach ingredient and recipe to diary
        $diary->ingredients()->attach($ingredient, ['quantity' => 1, 'unit' => 'cup']);
        $diary->recipes()->attach($recipe);

        // check database has correct data before being detached or changed
        $this->assertDatabaseHas('food_diary_ingredient', [
            'food_diary_id' => $diary->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
            'unit' => 'cup',
        ]);
        $this->assertDatabaseHas('food_diary_recipe', [
            'food_diary_id' => $diary->id,
            'recipe_id' => $recipe->id,
        ]);

        $data = [
            'user_id' => $user->id,
            'diary_ingredient_name' => ['Carrot', 'Broccoli'],
            'diary_ingredient_quantity' => [1, null],
            'diary_ingredient_unit' => ['cup', null],
            'diary_ingredient_allergen' => [0, 0],
            'diary_recipes' => [],
        ];

        $response = $this->patchJson("/api/food-diary/update/{$diary->id}", $data);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('food_diary_ingredient', [
            'food_diary_id' => $diary->id,
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
            'unit' => 'cup',
        ]);
        $this->assertDatabaseHas('food_diary_ingredient', [
            'food_diary_id' => $diary->id,
            'ingredient_id' => 2,
            'quantity' => 1,
            'unit' => 'cup',
        ]);
        $this->assertDatabaseHas('food_diary_ingredient', [
            'food_diary_id' => $diary->id,
            'ingredient_id' => 3,
            'quantity' => null,
            'unit' => null,
        ]);
        $this->assertDatabaseMissing('food_diary_recipe', [
            'food_diary_id' => $diary->id,
            'recipe_id' => $recipe->id,
        ]);
    }

    public function test_diary_update_entry_doesnt_exists(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patchJson('/api/food-diary/update/1');

        $response->assertStatus(404);

    }

    public function test_diary_update_cannot_update_other_users_entries(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $userTwo = User::factory()->create();

        FoodDiary::factory()->create(['user_id' => $userTwo->id]);

        $data = [
            'user_id' => $user->id,
            'diary_ingredient_name' => [],
            'diary_ingredient_quantity' => [],
            'diary_ingredient_unit' => [],
            'diary_ingredient_allergen' => [],
            'diary_recipes' => [],
        ];

        $response = $this->patchJson('/api/food-diary/update/1', $data);
        $response->assertStatus(401)
            ->assertJson(function (AssertableJson $response) {
                $response->where(
                    'message',
                    'Unauthorized - Cannot update entry for another user'
                );
            });
    }

    public function test_user_can_delete_own_diary_entry(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $diary = FoodDiary::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/food-diary/delete/{$diary->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Food diary entry deleted successfully',
            ]);

        $this->assertDatabaseMissing('food_diaries', [
            'id' => $diary->id,
        ]);
    }

    public function test_user_cannot_delete_others_diary_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $diary = FoodDiary::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        $response = $this->deleteJson("/api/food-diary/delete/{$diary->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized - Cannot delete entry for another user',
            ]);

        $this->assertDatabaseHas('food_diaries', [
            'id' => $diary->id,
        ]);
    }
}
