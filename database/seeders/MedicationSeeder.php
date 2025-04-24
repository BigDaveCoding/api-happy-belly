<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            DB::table('medications')->insert([
                'name' => fake()->word(),
                'strength' => fake()->word(),
                'form' => fake()->word(),
                'route' => fake()->word(),
                'notes' => fake()->sentence(5),
            ]);
        }
    }
}
