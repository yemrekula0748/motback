<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CharacterController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\QuestController;
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
    Route::put('/characters/{id}/exp', [CharacterController::class, 'updateExp']);
    Route::put('/characters/{id}/level', [CharacterController::class, 'updateLevel']);
    Route::patch('/characters/{id}', [CharacterController::class, 'updateProgress']);
    Route::post('/characters/{id}/respec', [CharacterController::class, 'respec']);
    Route::delete('/characters/{id}', [CharacterController::class, 'destroy']);

    // Quests
    Route::get('/characters/{id}/quests', [QuestController::class, 'index']);
    Route::post('/characters/{id}/quests/{questId}/start', [QuestController::class, 'start']);
    Route::put('/characters/{id}/quests/{questId}/progress', [QuestController::class, 'progress']);
    Route::put('/characters/{id}/quests/{questId}/complete', [QuestController::class, 'complete']);

    // Friends
    Route::get('/friends', [FriendController::class, 'index']);
    Route::get('/friends/requests', [FriendController::class, 'requests']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::put('/friends/{id}/accept', [FriendController::class, 'accept']);
    Route::delete('/friends/{id}/decline', [FriendController::class, 'decline']);
    Route::delete('/friends/{id}', [FriendController::class, 'remove']);
});
