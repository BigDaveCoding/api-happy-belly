<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BowelWellnessTrackerMedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            DB::table('bowel_wellness_tracker_medication')->insert([
                'bowel_wellness_tracker_id' => $i,
                'medication_id' => rand(1, 50),
                'prescribed' => fake()->boolean,
                'taken_at' => date('H:i:s'),
            ]);
        }
    }
}
