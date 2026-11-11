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
    public string $detailTab = 'formatted'; // 'formatted' oder 'raw'

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

    public function setTab(string $tab): void
    {
        $this->detailTab = in_array($tab, ['formatted', 'raw'], true) ? $tab : 'formatted';
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

    public function toggleShared(): void
    {
        $user = Auth::user();
        $recipe = Recipe::where('user_id', $user->id)->findOrFail($this->recipeId);
        $recipe->is_shared = !$recipe->is_shared;
        $recipe->server_version = ((int) $recipe->server_version) + 1;
        $recipe->save();

        $status = $recipe->is_shared ? 'freigegeben (in Gesamtansicht sichtbar)' : 'privat (nur für Sie sichtbar)';
        session()->flash('success', "Freigabestatus geändert: Das Rezept ist jetzt {$status}.");
    }

    public function copyRecipe(RecipeService $recipeService)
    {
        $user = Auth::user();
        $sourceRecipe = Recipe::with(['categories', 'tags', 'ingredients', 'steps'])
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

    public function addToShoppingList(\App\Services\ShoppingListService $shoppingListService): void
    {
        $user = Auth::user();
        $recipe = Recipe::with('ingredients')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_shared', true);
            })
            ->findOrFail($this->recipeId);

        $count = $shoppingListService->addRecipeIngredients($user, $recipe, $this->currentPortions);

        session()->flash('success', "{$count} Zutaten für {$this->currentPortions} Portionen wurden zur Einkaufsliste hinzugefügt!");
    }

    public function getMarkdownContentProperty(): string
    {
        $user = Auth::user();
        $recipe = Recipe::with(['categories', 'tags', 'ingredients', 'steps'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_shared', true);
            })
            ->findOrFail($this->recipeId);

        $scaleFactor = ($recipe->portions > 0) ? ($this->currentPortions / $recipe->portions) : 1;

        $md = "# {$recipe->title}\n\n";

        if (!empty($recipe->description)) {
            $md .= "{$recipe->description}\n\n";
        }

        $md .= "### Details\n";
        $md .= "- **Portionen:** {$this->currentPortions}\n";
        if ($recipe->prep_time_minutes) {
            $md .= "- **Vorbereitungszeit:** {$recipe->prep_time_minutes} Min.\n";
        }
        if ($recipe->cook_time_minutes) {
            $md .= "- **Koch- / Backzeit:** {$recipe->cook_time_minutes} Min.\n";
        }
        if ($recipe->rest_time_minutes) {
            $md .= "- **Ruhezeit:** {$recipe->rest_time_minutes} Min.\n";
        }
        if ($recipe->categories->isNotEmpty()) {
            $categoriesList = $recipe->categories->pluck('name')->join(', ');
            $md .= "- **Kategorien:** {$categoriesList}\n";
        }
        if ($recipe->tags->isNotEmpty()) {
            $tagsList = $recipe->tags->map(fn($t) => '#' . $t->name)->join(', ');
            $md .= "- **Schlagwörter:** {$tagsList}\n";
        }
        if ($recipe->calories || $recipe->protein || $recipe->fat || $recipe->carbohydrates) {
            $md .= "- **Nährwerte (pro Portion):** " . implode(', ', array_filter([
                $recipe->calories ? "{$recipe->calories} kcal" : null,
                $recipe->protein ? "{$recipe->protein} g Eiweiß" : null,
                $recipe->fat ? "{$recipe->fat} g Fett" : null,
                $recipe->carbohydrates ? "{$recipe->carbohydrates} g Kohlenhydrate" : null,
            ])) . "\n";
        }
        $md .= "\n";

        // Zutaten
        $md .= "## Zutaten\n\n";
        $grouped = $recipe->ingredients->groupBy(fn($i) => $i->group_name ?: 'Hauptzutaten');

        foreach ($grouped as $groupName => $items) {
            if ($grouped->count() > 1 || $groupName !== 'Hauptzutaten') {
                $md .= "### {$groupName}\n";
            }
            foreach ($items as $item) {
                $amount = '';
                if ($item->amount_numeric !== null) {
                    $scaled = round($item->amount_numeric * $scaleFactor, 2);
                    $formatted = ($scaled == (int)$scaled) ? (int)$scaled : number_format($scaled, 1, ',', '');
                    $amount = $formatted . ($item->unit ? ' ' . $item->unit : '');
                } elseif ($item->unit) {
                    $amount = $item->unit;
                }

                if ($item->amount_text) {
                    $amount .= ($amount ? ' ' : '') . '(' . $item->amount_text . ')';
                }

                $amount = trim($amount);
                $md .= $amount ? "- {$amount} {$item->name}\n" : "- {$item->name}\n";
            }
            $md .= "\n";
        }

        // Zubereitungsschritte
        $md .= "## Zubereitung\n\n";
        foreach ($recipe->steps->sortBy('step_number') as $step) {
            $md .= "{$step->step_number}. {$step->instruction}\n";
        }

        if (!empty($recipe->notes)) {
            $md .= "\n## Notizen\n{$recipe->notes}\n";
        }

        if (!empty($recipe->source_url)) {
            $md .= "\n---\n**Quelle:** [{$recipe->source_url}]({$recipe->source_url})\n";
        }

        return trim($md);
    }

    public function render()
    {
        $user = Auth::user();

        $recipe = Recipe::with(['categories', 'tags', 'ingredients', 'steps', 'user', 'favoritedBy'])
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
            'markdownContent' => $this->markdownContent,
            'canEdit' => $user->can('update', $recipe),
            'canDelete' => $user->can('delete', $recipe),
            'isOwner' => ($recipe->user_id === $user->id),
            'isFavorited' => $recipe->isFavoritedBy($user),
        ]);
    }
}
