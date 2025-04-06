<?php

namespace App\Http\Controllers;

use App\Models\FoodDiary;
use App\Models\User;
use App\Providers\RecipeApiServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodDiaryController extends Controller
{
    public function user(User $user): JsonResponse
    {
        $entriesData = FoodDiary::where(['user_id' => $user->id])->with('ingredients:name', 'recipes')->paginate(2);

        return response()->json([
            'message' => 'User food diary entries found successfully',
            'data' => [
                'entries' => $entriesData->items(),
                'pagination' =>  RecipeApiServiceProvider::pagination($entriesData),
            ]
        ]);
    }
}
