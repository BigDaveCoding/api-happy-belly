<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BowelWellnessTracker>
 */
class BowelWellnessTrackerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => date('Y-m-d'),
            'time' => date('H:i:s'),
            'stool_type' => rand(1, 7),
            'urgency' => rand(1, 10),
            'pain' => rand(1, 10),
            'blood' => fake()->boolean,
            'blood_amount' => rand(10, 50),
            'stress_level' => rand(1, 10),
            'hydration_level' => rand(1, 10),
            'recent_meal' => fake()->boolean,
            'color' => fake()->colorName(),
            'additional_notes' => fake()->paragraph(3),
        ];
    }
}
