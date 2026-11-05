<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;

class RecipePrintController extends Controller
{
    /**
     * Zeigt eine druckoptimierte A4-Ansicht des Rezepts an.
     */
    public function show(string $id)
    {
        $userId = Auth::id();
        $recipe = Recipe::with(['categories', 'ingredients', 'steps', 'user'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_shared', true);
            })
            ->findOrFail($id);

        return view('recipes.print', [
            'recipe' => $recipe,
        ]);
    }
}

