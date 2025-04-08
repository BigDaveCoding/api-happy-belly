<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodDiaryRequest;
use App\Models\FoodDiary;
use App\Models\Ingredient;
use App\Models\User;
use App\Providers\PaginationServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FoodDiaryController extends Controller
{
    public function user(User $user): JsonResponse
    {
        if (Auth::id() !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized - These are not your diary entries',
            ], 401);
        }

        $entriesData = FoodDiary::where(['user_id' => $user->id])->paginate(5);
        $entriesData->getCollection()->transform(function ($entry) {
            return $entry->setHidden(['user_id', 'entry', 'created_at', 'updated_at']);
        });

        return response()->json([
            'message' => 'User food diary entries found successfully',
            'data' => [
                'entries' => $entriesData->items(),
                'pagination' => PaginationServiceProvider::pagination($entriesData),
            ],
        ]);
    }

    public function find(int $id): JsonResponse
    {
        $entry = FoodDiary::with('ingredients:id,name', 'recipes:id,name', 'recipes.ingredients:id,name')->findOrFail($id);

        if (Auth::id() !== $entry->user_id) {
            return response()->json([
                'message' => 'Unauthorized - These are not your diary entries',
            ], 401);
        }

        $entry->recipes->each(function ($recipe) {
            $recipe->makeHidden(['pivot']);
        });

        return response()->json([
            'message' => 'Entry Found',
            'data' => $entry,
        ]);
    }

    public function create(FoodDiaryRequest $request): JsonResponse
    {
        // validate data sent in request
        $validatedData = $request->validated();

        // if data is valid - create new entry

        $entry = new FoodDiary();
        $entry->user_id = $validatedData['user_id'];
        $entry->entry = $validatedData['diary_entry'];
        $entry->meal_type = $validatedData['diary_meal_type'];
        $entry->entry_date = $validatedData['diary_date'];
        $entry->entry_time = $validatedData['diary_time'];
        $entry->save();

        foreach ($validatedData['diary_ingredient_name'] as $index => $ingredient) {
            $ingredient = Ingredient::firstOrCreate([
                'name' => $ingredient,
                'food_group' => 'food_group', // static value
                'allergen' => $validatedData['diary_ingredient_allergen'][$index],
            ]);
            $entry->ingredients()->attach($ingredient, [
                'quantity' => $validatedData['diary_ingredient_quantity'][$index],
                'unit' => $validatedData['diary_ingredient_unit'][$index],
            ]);
        }

        // attach recipes to pivot table
        foreach ($validatedData['diary_recipes'] as $recipe) {
            $entry->recipes()->attach($recipe);
        }

        return response()->json([
            'message' => 'Food diary entry created successfully',
        ]);
    }
}
