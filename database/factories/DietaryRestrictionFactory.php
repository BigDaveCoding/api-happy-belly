<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DietaryRestriction>
 */
class DietaryRestrictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'recipe_id' => Recipe::factory(),
            'is_vegetarian' => fake()->boolean(50),
            'is_vegan' => fake()->boolean(50),
            'is_gluten_free' => fake()->boolean(50),
            'is_dairy_free' => fake()->boolean(50),
            'is_low_fodmap' => fake()->boolean(50),
            'is_ostomy_friendly' => fake()->boolean(50),
        ];
    }
}
