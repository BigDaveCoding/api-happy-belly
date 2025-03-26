<?php

use App\Http\Controllers\RecipeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/recipes', [RecipeApiController::class, 'all']);

Route::get('/recipes/admin', [RecipeApiController::class, 'admin']);

Route::get('/recipes/user/{user}', [RecipeApiController::class, 'user']);

Route::get('/recipes/{recipe}', [RecipeApiController::class, 'find']);
