<?php

use App\Http\Controllers\RecipeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/recipes', [RecipeApiController::class, 'all']);

Route::get('/recipes/{recipe}', [RecipeApiController::class, 'find']);
