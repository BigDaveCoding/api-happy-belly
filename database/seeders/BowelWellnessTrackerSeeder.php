<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BowelWellnessTrackerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fake_users = [1, 2];
        foreach ($fake_users as $user) {
            for ($i = 0; $i < 5; $i++) {
                DB::table('bowel_wellness_trackers')->insert([
                    'user_id' => $user,
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
                ]);
            }
        }
    }
}
