<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RecipeDetail extends Component
{
    public string $recipeId;
    public int $currentPortions = 4;

    public function mount(string $id): void
    {
        $this->recipeId = $id;
        $user = Auth::user();

        $recipe = Recipe::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('is_shared', true);
        })->findOrFail($id);

        $this->currentPortions = $recipe->portions ?: 4;
    }

    public function toggleFavorite(RecipeService $recipeService): void
    {
        $user = Auth::user();
        $recipe = Recipe::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('is_shared', true);
        })->findOrFail($this->recipeId);

        $recipeService->toggleFavorite($user, $recipe);
    }

    public function copyRecipe(RecipeService $recipeService)
    {
        $user = Auth::user();
        $sourceRecipe = Recipe::with(['categories', 'ingredients', 'steps'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_shared', true);
            })->findOrFail($this->recipeId);

        $newRecipe = $recipeService->copyRecipeForUser($user, $sourceRecipe);

        session()->flash('success', "Kopie '{$newRecipe->title}' erfolgreich angelegt. Sie können sie nun frei anpassen.");
        return $this->redirectRoute('recipes.edit', $newRecipe->id, navigate: true);
    }

    public function delete(RecipeService $recipeService)
    {
        $user = Auth::user();
        $recipe = Recipe::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('is_shared', true);
        })->findOrFail($this->recipeId);

        // Berechtigungsprüfung über Policy
        $this->authorize('delete', $recipe);

        $recipeService->deleteRecipe($recipe);

        session()->flash('success', 'Rezept erfolgreich gelöscht.');
        return $this->redirectRoute('recipes.index', navigate: true);
    }

    public function incrementPortions(): void
    {
        if ($this->currentPortions < 100) {
            $this->currentPortions++;
        }
    }

    public function decrementPortions(): void
    {
        if ($this->currentPortions > 1) {
            $this->currentPortions--;
        }
    }

    public function render()
    {
        $user = Auth::user();

        $recipe = Recipe::with(['categories', 'ingredients', 'steps', 'user', 'favoritedBy'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_shared', true);
            })
            ->findOrFail($this->recipeId);

        $groupedIngredients = $recipe->ingredients->groupBy(function ($item) {
            return $item->group_name ?: 'Hauptzutaten';
        });

        return view('livewire.recipe-detail', [
            'recipe' => $recipe,
            'groupedIngredients' => $groupedIngredients,
            'canEdit' => $user->can('update', $recipe),
            'canDelete' => $user->can('delete', $recipe),
            'isOwner' => ($recipe->user_id === $user->id),
            'isFavorited' => $recipe->isFavoritedBy($user),
        ]);
    }
}
