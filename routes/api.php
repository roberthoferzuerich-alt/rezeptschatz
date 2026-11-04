<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\RecipeController;
use App\Http\Controllers\Api\V1\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {
    // Öffentliche Authentifizierung
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Geschützte Endpunkte (Laravel Sanctum Token)
    Route::middleware('auth:sanctum')->group(function () {
        // Authentifizierung & Benutzerinfo
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Synchronisation (Push & Pull)
        Route::post('/sync/push', [SyncController::class, 'push']);
        Route::get('/sync/pull', [SyncController::class, 'pull']);

        // Rezepte
        Route::get('/recipes', [RecipeController::class, 'index']);
        Route::post('/recipes', [RecipeController::class, 'store']);
        Route::get('/recipes/{recipe}', [RecipeController::class, 'show']);
        Route::put('/recipes/{recipe}', [RecipeController::class, 'update']);
        Route::delete('/recipes/{recipe}', [RecipeController::class, 'destroy']);
        Route::post('/recipes/{recipe}/favorite', [RecipeController::class, 'toggleFavorite']);

        // Geschützte Bildverwaltung
        Route::post('/recipes/{recipe}/image', [RecipeController::class, 'uploadImage']);
        Route::get('/recipes/{recipe}/image', [RecipeController::class, 'getImage']);

        // Kategorien
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
    });
});
