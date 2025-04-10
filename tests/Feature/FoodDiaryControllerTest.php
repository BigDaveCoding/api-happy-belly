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
}
