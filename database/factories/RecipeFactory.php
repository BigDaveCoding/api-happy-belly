<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->words(20, true),
            'image' => 'https://placehold.co/600x400',
            'cooking_time' => rand(10, 60),
            'serves' => rand(1, 4),
            'user_id' => User::factory(),
        ];
    }
}
