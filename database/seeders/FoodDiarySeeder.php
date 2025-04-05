<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FoodDiarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [1, 2];
        $mealTypes = ['breakfast', 'lunch', 'dinner', 'dessert'];
        foreach ($users as $user) {
            for ($i = 0; $i < 5; $i++) {
                DB::table('food_diaries')->insert([
                    'user_id' => $user,
                    'entry' => fake()->sentences(3, true),
                    'meal_type' => $mealTypes[rand(0, count($mealTypes) - 1)],
                    'entry_date' => date('Y-m-d'),
                    'entry_time' => date('H:i:s'),
                ]);
            }
        }
    }
}
