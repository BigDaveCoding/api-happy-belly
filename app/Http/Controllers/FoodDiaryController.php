<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodDiaryRequest;
use App\Models\FoodDiary;
use App\Models\Ingredient;
use App\Models\User;
use App\Providers\FoodDiaryServiceProvider;
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
        $validatedData = $request->validated();

        if (Auth::id() !== $validatedData['user_id']) {
            return response()->json([
                'message' => 'Unauthorized - Cannot make an entry for another user',
            ], 401);
        }

        $entry = FoodDiaryServiceProvider::createFoodDiaryEntry($validatedData);
        FoodDiaryServiceProvider::createIngredientsAddPivot($validatedData, $entry);
        FoodDiaryServiceProvider::addRecipePivot($validatedData, $entry);

        return response()->json([
            'message' => 'Food diary entry created successfully',
        ], 200);
    }

    public function update(Request $request, FoodDiary $entry): JsonResponse
    {
        // find entry to edit
        $diaryToUpdate = FoodDiary::findOrFail($entry->id);

        // validate the data
        $validatedData = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'diary_entry' => 'nullable|string|max:5000',
            'diary_meal_type' => 'nullable|string',
            'diary_date' => 'nullable|date',
            'diary_time' => 'nullable|string',
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

        // update food diary fields
        if ($validatedData['diary_entry']){
            $diaryToUpdate->entry = $validatedData['diary_entry'];
        }
        if($validatedData['diary_meal_type']) {
            $diaryToUpdate->meal_type = $validatedData['diary_meal_type'];
        }
        if ($validatedData['diary_date']) {
            $diaryToUpdate->date = $validatedData['diary_date'];
        }
        if ($validatedData['diary_time']) {
            $diaryToUpdate->time = $validatedData['diary_time'];
        }

        return response()->json([
            'message' => 'Food diary entry updated successfully',
        ]);
    }
}
