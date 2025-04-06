<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodDiaryRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 1; $j <= 2; $j++) {
                if (rand(1, 2) == 1) {
                    DB::table('food_diary_recipe')->insert([
                        'food_diary_id' => $i,
                        'recipe_id' => rand(1, 10),
                    ]);
                }
            }
        }
    }
}
