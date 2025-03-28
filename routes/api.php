<?php

use App\Http\Controllers\RecipeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

});
Route::controller(RecipeApiController::class)->group(function () {
    Route::get('/recipes', 'all');
    Route::get('/recipes/admin', 'admin');
    Route::get('/recipes/user/{user}', 'user');
    Route::get('/recipes/{recipe}', 'find');
    Route::post('/recipes/create', 'create');
    Route::put('/recipes/edit/{recipe}', 'edit');
    Route::delete('/recipes/delete/{recipe}', 'delete');
});
