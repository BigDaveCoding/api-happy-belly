<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;

class RecipeApiController extends Controller
{
    public function all(): JsonResponse
    {
        $recipeData = Recipe::all()->makeHidden(['description', 'user_id']);

        return response()->json([
            'message' => 'Recipes found successfully',
            'data' => $recipeData,
        ], 200);
    }

    public function find(int $recipe): JsonResponse
    {
        $recipe = Recipe::with(['ingredients', 'cookingInstructions'])->findOrFail($recipe);

        return response()->json([
            'message' => 'Recipe found',
            'data' => $recipe,
        ], 200);
    }
}
