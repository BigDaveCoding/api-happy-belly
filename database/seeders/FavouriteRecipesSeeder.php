<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavouriteRecipesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [1, 2];
        foreach ($users as $user) {
            $usedIndex = [];
            for ($i = 0; $i < 5; $i++) {
                $randomIndex = rand(1, 10);
                if (!in_array($randomIndex, $usedIndex)) {
                    DB::table('favourite_recipes')->insert([
                        'user_id' => $user,
                        'recipe_id' => $randomIndex,
                    ]);
                    $usedIndex[] = $randomIndex;
                }
            }
        }
    }
}
