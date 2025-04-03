<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipeApiController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
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

// this route is for using the verified middleware.
// if the user has not verified their email address
// they will be directed to or sent this response

// I think this doesn't work with api routes??
Route::get('/email/verify', function () {
    return response()->json([
        'message' => 'you have not verified your email',
    ]);
})->name('verification.notice');

// TODO: Move routes into a controller
// TODO: Email verification routes need testing

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

