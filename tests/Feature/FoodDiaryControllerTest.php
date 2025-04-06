<?php

namespace Tests\Feature;

use App\Models\FoodDiary;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

        // check databases has all expected entries
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
                            'entry_date',
                            'entry_time',
                            'ingredients',
                            'recipes',
                        );
                    });
            });
    }
}
