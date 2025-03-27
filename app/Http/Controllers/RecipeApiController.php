<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\User;
use App\Providers\RecipeApiServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeApiController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $recipeData = Recipe::with('dietaryRestrictions')->paginate(5);
        RecipeApiServiceProvider::paginationCollection($recipeData);

        return response()->json([
            'message' => 'Recipes found successfully',
            'data' => [
                'recipes' => $recipeData->items(),
                'pagination' => RecipeApiServiceProvider::pagination($recipeData),
            ],
        ], 200);
    }

    public function find(int $recipe): JsonResponse
    {
        $recipe = Recipe::with(['ingredients', 'cookingInstructions', 'dietaryRestrictions'])->findOrFail($recipe);

        return response()->json([
            'message' => 'Recipe found',
            'data' => $recipe,
        ], 200);
    }

    public function admin(): JsonResponse
    {
        $recipeData = Recipe::with('dietaryRestrictions')->where(['user_id' => 1])->paginate(5);
        RecipeApiServiceProvider::paginationCollection($recipeData);

        return response()->json([
            'message' => 'Admin recipes found successfully',
            'data' => [
                'admin_recipes' => $recipeData->items(),
                'pagination' => RecipeApiServiceProvider::pagination($recipeData),
            ],
        ], 200);
    }

    public function user(User $user): JsonResponse
    {
        $recipeData = Recipe::with('dietaryRestrictions')->where(['user_id' => $user->id])->paginate(5);
        RecipeApiServiceProvider::paginationCollection($recipeData);

        return response()->json([
            'message' => 'User recipes found successfully',
            'data' => [
                'user_recipes' => $recipeData->items(),
                'pagination' => RecipeApiServiceProvider::pagination($recipeData),
            ],
        ]);
    }
}
