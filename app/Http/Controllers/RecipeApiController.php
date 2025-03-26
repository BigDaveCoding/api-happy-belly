<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipeApiController extends Controller
{
    public function all(): JsonResponse
    {
        $recipeData = Recipe::all()->makeHidden(['description', 'user_id']);

        return response()->json([
           'message' => 'Recipes found successfully',
           'data' => $recipeData
        ]);
    }
}
