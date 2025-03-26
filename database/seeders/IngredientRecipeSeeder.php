<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // populate 10 ingredients for each recipe - recipe seeder creates 10 recipes
        $recipeId = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        foreach ($recipeId as $id) {
            for ($j = 0; $j < 10; $j++) {
                DB::table('ingredient_recipe')->insert([
                    'recipe_id' => $id,
                    'ingredient_id' => rand(1, 100),
                    'quantity' => rand(1, 5),
                    'unit' => fake()->word(),
                ]);
            }
        }
    }
}
