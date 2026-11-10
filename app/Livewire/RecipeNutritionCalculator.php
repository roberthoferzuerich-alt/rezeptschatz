<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Services\AiRecipeService;
use App\Services\WebRecipeImportService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RecipeNutritionCalculator extends Component
{
    /**
     * URL, ID oder Suchbegriff des Rezepts
     */
    public string $url = '';

    /**
     * Optionale Auswahl aus den bestehenden Rezepten des Benutzers
     */
    public string $selectedRecipeId = '';

    /**
     * Ob automatisch Hauptzutaten-Kategorien via Artisan zugewiesen werden sollen
     */
    public bool $autoAssignCategories = true;

    /**
     * Status während der Generierung
     */
    public bool $isGenerating = false;

    /**
     * Status während des Speicherns (Commit)
     */
    public bool $isSaving = false;

    /**
     * Fehlermeldung
     */
    public ?string $errorMessage = null;

    /**
     * Erfolgsmeldung
     */
    public ?string $successMessage = null;

    /**
     * Die ermittelten Rezeptdaten
     */
    public ?array $recipeData = null;

    /**
     * Bearbeitbare Nährwertfelder (Formular zum Überschreiben)
     */
    public ?int $calories = null;
    public ?float $carbohydrates = null;
    public ?float $fat = null;
    public ?float $protein = null;
    public ?int $portions = 4;

    /**
     * URL-Parameter auslesen (z.B. wenn aus der Detailansicht aufgerufen)
     */
    public function mount(?string $url = null, ?string $recipe = null): void
    {
        if (!empty($recipe)) {
            $this->selectedRecipeId = $recipe;
            $this->url = route('recipes.show', $recipe);
        } elseif (!empty($url)) {
            $this->url = $url;
        }
    }

    /**
     * Wenn der Benutzer ein Rezept aus dem Dropdown wählt
     */
    public function updatedSelectedRecipeId(string $value): void
    {
        if (!empty($value)) {
            $this->url = route('recipes.show', $value);
            $this->errorMessage = null;
            $this->successMessage = null;
        }
    }

    /**
     * Generiert die Nährwerte anhand der URL oder ID
     */
    public function generate(AiRecipeService $aiService, WebRecipeImportService $importService): void
    {
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->isGenerating = true;

        $target = trim($this->url);
        if (empty($target) && !empty($this->selectedRecipeId)) {
            $target = $this->selectedRecipeId;
        }

        if (empty($target)) {
            $this->errorMessage = 'Bitte gib eine Rezept-URL, eine ID oder wähle ein Rezept aus.';
            $this->isGenerating = false;
            return;
        }

        try {
            // 1. UUID ermitteln
            $uuid = null;
            if (preg_match('/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})/', $target, $m)) {
                $uuid = $m[1];
            }

            $recipe = null;
            if ($uuid) {
                $recipe = Recipe::with('ingredients')->find($uuid);
            }

            if (!$recipe) {
                $recipe = Recipe::with('ingredients')->where('source_url', $target)->first();
            }

            if (!$recipe && !filter_var($target, FILTER_VALIDATE_URL)) {
                $recipe = Recipe::with('ingredients')
                    ->where('title', 'like', "%{$target}%")
                    ->latest()
                    ->first();
            }

            // Fall A: Rezept in interner Datenbank gefunden
            if ($recipe) {
                $portions = max(1, (int) ($recipe->portions ?? 4));
                $ingredientsList = $recipe->ingredients->map(function ($ing) {
                    $amount = $ing->amount_text ?: trim(($ing->amount_numeric ? rtrim(rtrim((string)$ing->amount_numeric, '0'), '.') : '') . ' ' . $ing->unit);
                    return trim("{$amount} {$ing->name}");
                })->all();

                // Nährwerte berechnen
                $calc = $aiService->estimateNutrition($recipe->title, $portions, $recipe->ingredients->all());

                $this->recipeData = [
                    'type' => 'internal',
                    'id' => $recipe->id,
                    'title' => $recipe->title,
                    'image_url' => $recipe->image_path ? route('recipes.image', $recipe->id) : null,
                    'portions' => $portions,
                    'ingredients' => $ingredientsList,
                    'source' => $calc['source'] ?? 'Berechnet via KI',
                    'original_recipe' => $recipe,
                ];

                $this->calories = (int) ($calc['calories'] ?? 0);
                $this->carbohydrates = (float) ($calc['carbohydrates'] ?? 0);
                $this->fat = (float) ($calc['fat'] ?? 0);
                $this->protein = (float) ($calc['protein'] ?? 0);
                $this->portions = $portions;

                // Automatische Hauptzutaten-Kategorien direkt im Hintergrund zuweisen falls gewünscht
                if ($this->autoAssignCategories) {
                    Artisan::call('recipes:assign-ingredient-categories', [
                        '--user' => Auth::id(),
                        '--recipe' => $recipe->id,
                    ]);
                }
            } 
            // Fall B: Externe Rezept-URL (Webseite)
            elseif (filter_var($target, FILTER_VALIDATE_URL)) {
                $parsed = $importService->fetchAndParse($target);

                if (!$parsed['success']) {
                    throw new \Exception($parsed['error'] ?? 'Die externe Rezept-Webseite konnte nicht geladen werden.');
                }

                $portions = max(1, (int) ($parsed['portions'] ?? 4));
                $ingredients = $parsed['ingredients'] ?? [];

                // Prüfen ob Schema.org Nährwerte vorhanden sind
                $nutrition = $parsed['nutrition'] ?? [];
                if (!empty($nutrition['calories'])) {
                    $calVal = (int) $nutrition['calories'];
                    $carbVal = (float) ($nutrition['carbohydrates'] ?? 0);
                    $fatVal = (float) ($nutrition['fat'] ?? 0);
                    $protVal = (float) ($nutrition['protein'] ?? 0);
                    $sourceStr = 'Aus Web-Rezept extrahiert (Schema.org)';
                } else {
                    $calc = $aiService->estimateNutrition($parsed['title'], $portions, $ingredients);
                    $calVal = (int) ($calc['calories'] ?? 0);
                    $carbVal = (float) ($calc['carbohydrates'] ?? 0);
                    $fatVal = (float) ($calc['fat'] ?? 0);
                    $protVal = (float) ($calc['protein'] ?? 0);
                    $sourceStr = $calc['source'] ?? 'Berechnet via KI';
                }

                $ingredientStrings = array_map(function ($ing) {
                    if (is_string($ing)) return $ing;
                    $amount = $ing['amount_text'] ?? trim(($ing['amount_numeric'] ?? '') . ' ' . ($ing['unit'] ?? ''));
                    return trim("{$amount} " . ($ing['name'] ?? ''));
                }, $ingredients);

                $this->recipeData = [
                    'type' => 'external',
                    'id' => null,
                    'title' => $parsed['title'] ?? 'Externes Rezept',
                    'image_url' => $parsed['image_url'] ?? null,
                    'portions' => $portions,
                    'ingredients' => $ingredientStrings,
                    'source' => $sourceStr,
                    'parsed_external' => $parsed,
                ];

                $this->calories = $calVal;
                $this->carbohydrates = $carbVal;
                $this->fat = $fatVal;
                $this->protein = $protVal;
                $this->portions = $portions;
            } else {
                $this->errorMessage = "Kein Rezept mit dieser URL oder diesem Namen gefunden: '{$target}'";
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler bei der Nährwertermittlung: ' . $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Speichern (Commit): Schreibt die Nährwerte direkt in die Datenbank
     */
    public function commit(WebRecipeImportService $importService): void
    {
        if (!$this->recipeData) {
            return;
        }

        $this->isSaving = true;
        $this->errorMessage = null;

        try {
            $cal = max(0, (int) $this->calories);
            $carb = max(0.0, (float) $this->carbohydrates);
            $f = max(0.0, (float) $this->fat);
            $prot = max(0.0, (float) $this->protein);
            $port = max(1, (int) $this->portions);

            $nutritionRaw = "{$cal} kcal | {$carb}g KH | {$f}g Fett | {$prot}g Eiweiss pro Portion";

            if ($this->recipeData['type'] === 'internal' && !empty($this->recipeData['id'])) {
                $recipe = Recipe::find($this->recipeData['id']);
                if (!$recipe) {
                    throw new \Exception('Das Rezept konnte nicht in der Datenbank gefunden werden.');
                }

                $recipe->calories = $cal;
                $recipe->carbohydrates = $carb;
                $recipe->fat = $f;
                $recipe->protein = $prot;
                $recipe->portions = $port;
                $recipe->nutrition_raw = $nutritionRaw;
                $recipe->server_version = ((int) $recipe->server_version) + 1;
                $recipe->save();

                // Artisan Kategoriezuweisung ausführen
                if ($this->autoAssignCategories) {
                    Artisan::call('recipes:assign-ingredient-categories', [
                        '--user' => Auth::id(),
                        '--recipe' => $recipe->id,
                    ]);
                }

                $this->successMessage = "Nährwerte für „{$recipe->title}“ wurden erfolgreich in der Datenbank gespeichert!";
                $this->recipeData['saved_recipe_id'] = $recipe->id;
            } elseif ($this->recipeData['type'] === 'external') {
                // Bei externen Rezepten wird das Rezept importiert und mit den Nährwerten gespeichert
                $parsed = $this->recipeData['parsed_external'] ?? [];
                $parsed['portions'] = $port;
                $parsed['nutrition'] = [
                    'calories' => $cal,
                    'carbohydrates' => $carb,
                    'fat' => $f,
                    'protein' => $prot,
                    'raw' => $nutritionRaw,
                ];

                $recipe = $importService->saveToDatabase(Auth::user(), $parsed);

                if ($this->autoAssignCategories) {
                    Artisan::call('recipes:assign-ingredient-categories', [
                        '--user' => Auth::id(),
                        '--recipe' => $recipe->id,
                    ]);
                }

                $this->successMessage = "Rezept „{$recipe->title}“ wurde mit den Nährwerten erfolgreich in deiner Rezeptsammlung gespeichert!";
                $this->recipeData['type'] = 'internal';
                $this->recipeData['id'] = $recipe->id;
                $this->recipeData['saved_recipe_id'] = $recipe->id;
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler beim Speichern in die Datenbank: ' . $e->getMessage();
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * Abbrechen: Setzt das Formular zurück
     */
    public function cancel(): void
    {
        $this->recipeData = null;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->calories = null;
        $this->carbohydrates = null;
        $this->fat = null;
        $this->protein = null;
    }

    public function render()
    {
        // 20 aktuellste Rezepte des Benutzers für ein schnelles Auswahlfeld laden
        $userRecipes = Recipe::where('user_id', Auth::id())
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('livewire.recipe-nutrition-calculator', [
            'userRecipes' => $userRecipes,
        ]);
    }
}
