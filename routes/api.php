<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CharacterController;
use App\Http\Controllers\Api\V1\GameSessionController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\RealmController;
use App\Http\Controllers\Api\V1\ServerCharacterController;
use App\Http\Controllers\Api\V1\ServerSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    });

    Route::get('public/realms', [RealmController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [MeController::class, 'show']);
        Route::patch('me/faction', [MeController::class, 'updateFaction']);

        Route::get('characters', [CharacterController::class, 'index']);
        Route::post('characters', [CharacterController::class, 'store']);
        Route::get('characters/{character}', [CharacterController::class, 'show']);
        Route::delete('characters/{character}', [CharacterController::class, 'destroy']);

        Route::post('game/session', [GameSessionController::class, 'store'])->middleware('throttle:game-session');
    });

    Route::prefix('server')->group(function (): void {
        Route::post('session/consume', [ServerSessionController::class, 'consume'])->middleware('throttle:server-consume');
        Route::patch('characters/{character}/progress', [ServerCharacterController::class, 'updateProgress'])->middleware('throttle:server-progress');
    });
});
