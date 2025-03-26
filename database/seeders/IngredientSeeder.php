<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 100; $i++) {
            DB::table('ingredients')->insert([
                'name' => fake()->word(),
                'food_group' => fake()->word(),
                'allergen' => fake()->numberBetween(0, 1),
            ]);
        }
    }
}
