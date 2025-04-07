<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FoodDiaryController;
use App\Http\Controllers\RecipeApiController;
use App\Http\Middleware\VerifyEmailApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', VerifyEmailApi::class])->group(function () {
    Route::controller(RecipeApiController::class)->group(function () {
        Route::get('/recipes', 'all');
        Route::get('/recipes/admin', 'admin');
        Route::get('/recipes/favourite/{user}', 'favouriteRecipes');
        Route::get('/recipes/user/{user}', 'user');
        Route::get('/recipes/{recipe}', 'find');
        Route::post('/recipes/create', 'create');
        Route::post('/recipes/favourite/{user}/{recipe}', 'favourite');
        Route::put('/recipes/edit/{recipe}', 'edit');
        Route::delete('/recipes/delete/{recipe}', 'delete');
        Route::delete('/recipes/unfavourite/{user}/{recipe}', 'unfavourite');
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::post('/email/resend/verification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

// Route::get('/food-diary/{user}', [FoodDiaryController::class, 'user']);
// Route::get('/food-diary/entry/{id}', [FoodDiaryController::class, 'find']);

Route::controller(FoodDiaryController::class)->group(function () {
    Route::middleware(['auth:sanctum', VerifyEmailApi::class])->group(function () {
        Route::get('/food-diary/{user}', 'user');
        Route::get('/food-diary/entry/{id}', 'find');
    });
});

Route::post('/food-diary/create', [FoodDiaryController::class, 'create']);
