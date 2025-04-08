<?php

namespace Tests\Feature;

use App\Models\FoodDiary;
use App\Models\Recipe;
use App\Providers\FoodDiaryServiceProvider;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FoodDiaryServiceProviderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_create_food_diary_entry_success(): void
    {
        $data = [
            'user_id' => 1,
            'diary_entry' => 'entry',
            'diary_meal_type' => 'breakfast',
            'diary_date' => date('Y-m-d'),
            'diary_time' => date('H:i:s'),
        ];

        FoodDiaryServiceProvider::createFoodDiaryEntry($data);

        $this->assertDatabaseHas('food_diaries', [
            'user_id' => 1,
            'entry' => 'entry',
            'meal_type' => 'breakfast',
            'entry_date' => date('Y-m-d'),
            'entry_time' => date('H:i:s'),
        ]);
    }

    public function test_create_ingredients_adds_pivot(): void
    {
        $entry = FoodDiary::factory()->create();
        $data = [
            'diary_ingredient_name' => ['one', 'two'],
            'diary_ingredient_allergen' => [0, 1],
            'diary_ingredient_quantity' => [2, 240],
            'diary_ingredient_unit' => ['cups', 'grams'],
        ];

        FoodDiaryServiceProvider::createIngredientsAddPivot($data, $entry);

        foreach ($data['diary_ingredient_name'] as $index => $name){
            $this->assertDatabaseHas('food_diary_ingredient', [
                'food_diary_id' => $entry->id,
                'ingredient_id' => $index + 1,
                'quantity' => $data['diary_ingredient_quantity'][$index],
                'unit' => $data['diary_ingredient_unit'][$index],
            ]);
        }

        foreach ($data['diary_ingredient_name'] as $index => $name){
            $this->assertDatabaseHas('ingredients', [
                'name' =>  $name,
                'food_group' => 'food_group',
                'allergen' => $data['diary_ingredient_allergen'][$index],
            ]);
        }
    }

    public function test_create_recipes_adds_pivot(): void
    {
        $entry = FoodDiary::factory()->create();
        $data = ['diary_recipes' => [1, 2, 3]];

        Recipe::factory()->count(3)->create();

        FoodDiaryServiceProvider::addRecipePivot($data, $entry);

        foreach ($data['diary_recipes'] as $recipe){
            $this->assertDatabaseHas('food_diary_recipe', [
                'food_diary_id' => $entry->id,
                'recipe_id' => $recipe,
            ]);
        }
    }
}
