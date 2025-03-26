<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeApiController extends Controller
{

    // TODO: Need validation!
    // TODO: Pagination for admin and user recipes

    public function all(Request $request): JsonResponse
    {
        $recipeData = Recipe::with('dietaryRestrictions')->paginate(5);
        $recipeData->getCollection()->transform(function ($recipe) {
            return $recipe->setHidden(['description', 'user_id', 'created_at', 'updated_at',]);
        });

        return response()->json([
            'message' => 'Recipes found successfully',
            'data' => [
                'recipes' => $recipeData->items(),
                'pagination' => [
                    'current_page' => $recipeData->currentPage(),
                    'total_recipes' => $recipeData->total(),
                    'next_page_url' => $recipeData->nextPageUrl(),
                    'previous_page_url' => $recipeData->previousPageUrl(),
                    'all_page_urls' => $recipeData->getUrlRange(1, $recipeData->lastPage())
                ]
            ]
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
        $recipe = Recipe::with('dietaryRestrictions')->where(['user_id' => 1])->get()->makeHidden(['description', 'user_id']);

        return response()->json([
            'message' => 'Admin recipes found successfully',
            'data' => $recipe,
        ], 200);
    }

    public function user(int $user_id): JsonResponse
    {
        $recipe = Recipe::with('dietaryRestrictions')->where(['user_id' => $user_id])->get()->makeHidden(['description', 'user_id']);

        return response()->json([
            'message' => 'User recipes found successfully',
            'data' => $recipe,
        ]);
    }
}
