<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DietaryRestrictionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            DB::table('dietary_restrictions')->insert([
                'recipe_id' => $i + 1,
                'is_vegetarian' => fake()->boolean(50),
                'is_vegan' => fake()->boolean(50),
                'is_gluten_free' => fake()->boolean(50),
                'is_dairy_free' => fake()->boolean(50),
                'is_low_fodmap' => fake()->boolean(50),
                'is_ostomy_friendly' => fake()->boolean(50),
            ]);
        }
    }
}
