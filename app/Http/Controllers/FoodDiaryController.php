<?php

namespace App\Http\Controllers;

use App\Http\Requests\FoodDiaryRequest;
use App\Http\Requests\FoodDiaryUpdateRequest;
use App\Models\FoodDiary;
use App\Models\User;
use App\Providers\FoodDiaryServiceProvider;
use App\Providers\PaginationServiceProvider;
use Illuminate\Http\JsonResponse;
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
        ], 201);
    }

    public function update(FoodDiaryUpdateRequest $request, FoodDiary $entry): JsonResponse
    {
        // find entry to edit
        $diaryToUpdate = FoodDiary::findOrFail($entry->id);

        if (Auth::id() !== $diaryToUpdate->user_id) {
            return response()->json([
                'message' => 'Unauthorized - Cannot update entry for another user',
            ], 401);
        }

        // validate the data
        $validatedData = $request->validated();

        // update food diary fields
        if (isset($validatedData['diary_entry'])) {
            $diaryToUpdate->entry = $validatedData['diary_entry'];
        }
        if (isset($validatedData['diary_meal_type'])) {
            $diaryToUpdate->meal_type = $validatedData['diary_meal_type'];
        }
        if (isset($validatedData['diary_date'])) {
            $diaryToUpdate->entry_date = $validatedData['diary_date'];
        }
        if (isset($validatedData['diary_time'])) {
            $diaryToUpdate->entry_time = $validatedData['diary_time'];
        }

        // detach ingredients
        $diaryToUpdate->ingredients()->detach();
        // add ingredients
        FoodDiaryServiceProvider::createIngredientsAddPivot($validatedData, $diaryToUpdate);

        // detach recipes
        $diaryToUpdate->recipes()->detach();
        // add recipes
        FoodDiaryServiceProvider::addRecipePivot($validatedData, $diaryToUpdate);

        $diaryToUpdate->save();

        return response()->json([
            'message' => 'Food diary entry updated successfully',
        ], 200);
    }

    public function delete(FoodDiary $entry): JsonResponse
    {
        if (Auth::id() != $entry->user_id) {
            return response()->json([
                'message' => 'Unauthorized - Cannot delete entry for another user',
            ], 401);
        }

        $entry->delete();

        return response()->json([
            'message' => 'Food diary entry deleted successfully',
        ], 200);
    }
}
