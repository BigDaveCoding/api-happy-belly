<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\FoodDiary;
use App\Models\User;
use App\Providers\PaginationServiceProvider;
use App\Providers\RecipeApiServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FoodDiaryController extends Controller
{
    public function user(User $user): JsonResponse
    {
        $entriesData = FoodDiary::where(['user_id' => $user->id])->paginate(5);
        $entriesData->getCollection()->transform(function ($entry) {
            return $entry->setHidden(['user_id','entry','created_at', 'updated_at']);
        });

        return response()->json([
            'message' => 'User food diary entries found successfully',
            'data' => [
                'entries' => $entriesData->items(),
                'pagination' =>  PaginationServiceProvider::pagination($entriesData),
            ]
        ]);
    }

    public function find(int $id): JsonResponse
    {
        $entry = FoodDiary::with('ingredients', 'recipes:id,name', 'recipes.ingredients:id,name')->findOrFail($id);

        $entry->recipes->each(function ($recipe) {
            $recipe->makeHidden(['pivot']);
        });

        return response()->json([
            'message' => 'Entry Found',
            'data' => $entry
        ]);
    }
}
