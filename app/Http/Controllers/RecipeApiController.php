<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecipeRequest;
use App\Models\Recipe;
use App\Models\User;
use App\Providers\RecipeApiServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function create(RecipeRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $user_id = $validatedData['user_id'];

        $recipe = RecipeApiServiceProvider::createRecipe($validatedData, $user_id);

        RecipeApiServiceProvider::addIngredients($validatedData, $recipe);

        RecipeApiServiceProvider::addCookingInstructions($validatedData, $recipe);

        RecipeApiServiceProvider::addDietaryRestrictions($validatedData, $recipe);

        return response()->json([
            'message' => 'Recipe created successfully',
        ], 201);
    }

    public function edit(RecipeRequest $request, Recipe $recipe): JsonResponse
    {
        $recipeToEdit = Recipe::findOrFail($recipe->id);

        $validatedData = $request->validated();

        // Update recipe details
        $recipeToEdit->name = $validatedData['recipe_name'];
        $recipeToEdit->description = $validatedData['recipe_description'];
        $recipeToEdit->cooking_time = $validatedData['recipe_cooking_time'];
        $recipeToEdit->serves = $validatedData['recipe_serves'];
        $recipeToEdit->cuisine = $validatedData['recipe_cuisine'];
        $recipeToEdit->save();

        // remove old ingredients
        $recipeToEdit->ingredients()->detach();
        RecipeApiServiceProvider::addIngredients($validatedData, $recipeToEdit);
        // remove old instructions
        $recipeToEdit->cookingInstructions()->delete();
        RecipeApiServiceProvider::addCookingInstructions($validatedData, $recipeToEdit);
        // remove dietary instructions
        $recipeToEdit->dietaryRestrictions()->delete();
        RecipeApiServiceProvider::addDietaryRestrictions($validatedData, $recipeToEdit);

        $recipeToEdit->save();

        return response()->json([
            'message' => 'Recipe edited successfully',
        ], 200);
    }

    public function delete(Recipe $recipe): JsonResponse
    {
        if ($recipe->user_id == Auth::id()) {
            if ($recipe->delete()) {
                return response()->json([
                    'message' => 'Recipe deleted successfully',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'You do not have permission to delete this recipe',
            ]);
        }
    }

    // TODO: Needs tests
    public function favouriteRecipes(User $user): JsonResponse
    {
        $favouriteRecipes = $user->favouriteRecipes()->paginate(5);
        return response()->json([
            'message' => 'Favourite recipes found successfully',
            'data' => [
                'favourite_recipes' => $favouriteRecipes->items(),
                'pagination' => RecipeApiServiceProvider::pagination($favouriteRecipes),
            ]
        ]);
    }

    // TODO: Needs tests
    public function favourite(User $user, Recipe $recipe): JsonResponse
    {
        $user->favouriteRecipes()->syncWithoutDetaching($recipe);
        return response()->json([
            'message' => 'Recipe favourite success'
        ], 200);
    }

    // TODO: needs tests.
    public function unfavourite(User $user, Recipe $recipe): JsonResponse
    {
        $exists = $user->favouriteRecipes()->where('recipe_id', $recipe->id)->exists();

        if ($exists) {
            $user->favouriteRecipes()->detach($recipe);
            return response()->json([
                'message' => 'Recipe unfavourite success'
            ], 200);
        }

        return response()->json([
            'message' => 'Recipe was not a favourite',
        ], 200);
    }
}

// TODO: Setup policy to protect certain methods like edit or delete etc.
// TODO: Setup post methods to update which recipes the user favourites etc.
