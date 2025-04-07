<?php

namespace App\Http\Controllers;

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

    public function create(Request $request): JsonResponse
    {
        // validate data sent in request
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'entry' => 'required|string|max:5000',
            'meal_type' => 'required|string',
            'diary_date' => 'required|date',
            'diary_time' => 'required|string',
            'diary_ingredient_name' => 'nullable|array',
            'diary_ingredient_name.*' => 'nullable|string',
            'diary_ingredient_quantity' => 'nullable|array',
            'diary_ingredient_quantity.*' => 'nullable|integer',
            'diary_ingredient_unit' => 'nullable|array',
            'diary_ingredient_unit.*' => 'nullable|string',
            'diary_ingredient_allergen' => 'nullable|array',
            'diary_ingredient_allergen.*' => 'nullable|boolean',
            'diary_recipes' => 'nullable|array',
            'diary_recipes.*' => 'nullable|integer|exists:recipes,id',
        ]);

        // if data is valid - create new entry

        $entry = new FoodDiary();
        $entry->user_id = $validatedData['user_id'];
        $entry->entry = $validatedData['entry'];
        $entry->meal_type = $validatedData['meal_type'];
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

        return response()->json([
            'message' => 'Food diary entry created successfully',
        ]);
    }
}
