<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CookingInstructionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $recipeId = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        foreach ($recipeId as $id) {
            for ($j = 0; $j < 5; $j++) {
                DB::table('cooking_instructions')->insert([
                    'recipe_id' => $id,
                    'step' => $j + 1,
                    'instruction' => fake()->sentences(3, true),
                ]);
            }
        }
    }
}
