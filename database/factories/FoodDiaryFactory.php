<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FoodDiary>
 */
class FoodDiaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'dessert'];

        return [
            'user_id' => User::factory(),
            'entry' => fake()->sentences(3, true),
            'meal_type' => $mealTypes[rand(0, count($mealTypes) - 1)],
            'entry_date' => date('Y-m-d'),
            'entry_time' => date('H:i:s'),
        ];
    }
}
