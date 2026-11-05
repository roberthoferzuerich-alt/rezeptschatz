<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\ShoppingListItem;
use App\Models\User;
use Illuminate\Support\Str;

class ShoppingListService
{
    /**
     * Fügt eine Zutat oder einen manuellen Artikel zur Einkaufsliste hinzu.
     * Erkennt gleiche, noch nicht erledigte Zutaten mit gleicher Einheit und rechnet sie zusammen.
     */
    public function addIngredient(User $user, array $data): ShoppingListItem
    {
        $name = trim($data['name'] ?? '');
        $unit = !empty($data['unit']) ? trim($data['unit']) : null;
        $amountNumeric = isset($data['amount_numeric']) && is_numeric($data['amount_numeric']) ? (float) $data['amount_numeric'] : null;
        $amountText = !empty($data['amount_text']) ? trim($data['amount_text']) : null;
        $recipeId = $data['recipe_id'] ?? null;
        $category = $data['category'] ?? $this->guessCategory($name);

        // Suche nach vorhandenem, noch nicht erledigtem Eintrag mit gleichem Namen
        $existing = ShoppingListItem::where('user_id', $user->id)
            ->where('is_completed', false)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            // Gleiche Einheit und beide numerisch -> Zusammenzählen!
            if ($existing->unit === $unit && $existing->amount_numeric !== null && $amountNumeric !== null) {
                $existing->amount_numeric += $amountNumeric;
                $existing->amount_text = null;
                $existing->save();
                return $existing;
            }

            // Wenn keine numerischen Werte vorliegen, Text anhängen
            if ($existing->amount_numeric === null && $amountNumeric === null && $amountText) {
                if ($existing->amount_text && !str_contains($existing->amount_text, $amountText)) {
                    $existing->amount_text .= ', ' . $amountText;
                    $existing->save();
                }
                return $existing;
            }
        }

        return ShoppingListItem::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'recipe_id' => $recipeId,
            'name' => $name,
            'amount_numeric' => $amountNumeric,
            'unit' => $unit,
            'amount_text' => $amountText,
            'category' => $category,
            'is_completed' => false,
            'sort_order' => 0,
        ]);
    }

    /**
     * Fügt alle Zutaten eines Rezepts (optional skaliert auf eine bestimmte Portionszahl) zur Liste hinzu.
     */
    public function addRecipeIngredients(User $user, Recipe $recipe, ?int $targetPortions = null): int
    {
        $basePortions = $recipe->portions ?: 4;
        $targetPortions = $targetPortions ?: $basePortions;
        $ratio = $targetPortions / $basePortions;

        $count = 0;
        foreach ($recipe->ingredients as $ing) {
            if (empty(trim($ing->name))) {
                continue;
            }

            $scaledAmount = null;
            if ($ing->amount_numeric !== null) {
                $scaledAmount = round($ing->amount_numeric * $ratio, 2);
            }

            $this->addIngredient($user, [
                'name' => $ing->name,
                'amount_numeric' => $scaledAmount,
                'unit' => $ing->unit,
                'amount_text' => $ing->amount_text,
                'recipe_id' => $recipe->id,
                'category' => $this->guessCategory($ing->name),
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Fügt die Zutaten aus allen Menüplänen einer Woche zur Einkaufsliste hinzu.
     */
    public function addMealPlansIngredients(User $user, iterable $mealPlans): int
    {
        $recipesAdded = 0;
        foreach ($mealPlans as $plan) {
            if ($plan->recipe) {
                $this->addRecipeIngredients($user, $plan->recipe, $plan->portions);
                $recipesAdded++;
            }
        }
        return $recipesAdded;
    }

    /**
     * Schaltet den Erledigt-Status eines Artikels um.
     */
    public function toggleItem(User $user, string $itemId): bool
    {
        $item = ShoppingListItem::where('user_id', $user->id)->findOrFail($itemId);
        $item->is_completed = !$item->is_completed;
        $item->save();
        return $item->is_completed;
    }

    /**
     * Entfernt alle erledigten Einträge.
     */
    public function clearCompleted(User $user): int
    {
        return ShoppingListItem::where('user_id', $user->id)->where('is_completed', true)->delete();
    }

    /**
     * Leert die gesamte Einkaufsliste des Benutzers.
     */
    public function clearAll(User $user): int
    {
        return ShoppingListItem::where('user_id', $user->id)->delete();
    }

    /**
     * Schätzt die Supermarkt-Kategorie anhand des Zutatennamens für eine strukturierte Einkaufsliste.
     */
    public function guessCategory(string $name): string
    {
        $lower = mb_strtolower($name);

        if (preg_match('/(kartoffel|zwiebel|knoblauch|tomate|rüebli|karotte|zucchini|peperoni|salat|gurke|pilz|champignon|apfel|zitrone|kürbis|lauch|spargel|spinat|kräuter|petersilie|basilikum|schnittlauch|avocado)/u', $lower)) {
            return 'Gemüse & Früchte';
        }

        if (preg_match('/(milch|butter|rahm|sahne|käse|gruyère|parmesan|sbrinz|mozzarella|quark|joghurt|mascarpone|creme|ei|eier)/u', $lower)) {
            return 'Milchprodukte & Eier';
        }

        if (preg_match('/(hackfleisch|rind|schwein|poulet|hähnchen|huhn|speck|schinken|wurst|bratwurst|lachs|fisch|crevette|thunfisch|fleisch)/u', $lower)) {
            return 'Fleisch & Fisch';
        }

        if (preg_match('/(mehl|zucker|reis|nudel|pasta|spaghetti|öl|olivenöl|essig|brot|hefe|backpulver|honig|brühe|bouillon|senf|ketchup|tomatenpüree|passata)/u', $lower)) {
            return 'Vorrat & Teigwaren';
        }

        if (preg_match('/(salz|pfeffer|muskat|paprika|curry|zimt|oregano|kümmel|chili|lorbeer)/u', $lower)) {
            return 'Gewürze';
        }

        return 'Sonstiges';
    }
}

