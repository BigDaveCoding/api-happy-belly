<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodDiaryIngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 3; $j++) {
                DB::table('food_diary_ingredient')->insert([
                    'food_diary_id' => $i + 1,
                    'ingredient_id' => rand(1, 100),
                    'quantity' => rand(1, 5),
                    'unit' => fake()->word(),
                ]);
            }
        }
    }
}
