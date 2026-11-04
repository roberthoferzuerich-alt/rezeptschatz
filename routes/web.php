<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RecipeImageController;
use App\Livewire\CategoryManager;
use App\Livewire\RecipeDetail;
use App\Livewire\RecipeForm;
use App\Livewire\RecipeList;
use Illuminate\Support\Facades\Route;

// Gäste-Routen (Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

// Abmeldung
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Geschützte Anwendungsrouten
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('recipes.index');
    });

    // Rezepte
    Route::get('/recipes', RecipeList::class)->name('recipes.index');
    Route::get('/recipes/create', RecipeForm::class)->name('recipes.create');
    Route::get('/recipes/{id}/edit', RecipeForm::class)->name('recipes.edit');
    Route::get('/recipes/{id}', RecipeDetail::class)->name('recipes.show');

    // Geschützte Bildauslieferung
    Route::get('/recipes/{recipe}/image', [RecipeImageController::class, 'show'])->name('recipes.image');

    // Kategorien
    Route::get('/categories', CategoryManager::class)->name('categories.index');
});
