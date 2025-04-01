<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavouriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // two users from seeders - admin and test
        $users = [1, 2];
        foreach ($users as $user) {
            $used_index = [];
            for ($i = 0; $i < 5; $i++) {
                // 10 recipes created in seeder
                $random_recipe = rand(1,10);
                if(!in_array($random_recipe, $used_index)) {
                    DB::table('favourites')->insert([
                        'user_id' => $user,
                        'recipe_id' => $random_recipe,
                    ]);
                    $used_index[] = $random_recipe;
                }
            }
        }

    }
}
