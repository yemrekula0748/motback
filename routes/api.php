<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CharacterController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/characters', [CharacterController::class, 'index']);
    Route::post('/characters', [CharacterController::class, 'store']);
    Route::get('/characters/{id}', [CharacterController::class, 'show']);
    Route::put('/characters/{id}/save', [CharacterController::class, 'save']);
    Route::delete('/characters/{id}', [CharacterController::class, 'destroy']);
});
