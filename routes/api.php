<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecipeApiController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(RecipeApiController::class)->group(function () {
        Route::get('/recipes', 'all');
        Route::get('/recipes/admin', 'admin');
        Route::get('/recipes/user/{user}', 'user');
        Route::get('/recipes/{recipe}', 'find');
        Route::post('/recipes/create', 'create');
        Route::put('/recipes/edit/{recipe}', 'edit');
        Route::delete('/recipes/delete/{recipe}', 'delete');
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);


Route::get('/email/verify', function () {
    return response()->json([
        'message' => 'verification link has been sent to your email address.',
    ]);
})->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {

    $request->fulfill(); // Marks email as verified
    return response()->json(['message' => 'Email verified successfully']);
})->middleware([ 'signed'])->name('verification.verify');
