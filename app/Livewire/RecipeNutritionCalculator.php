<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Services\AiRecipeService;
use App\Services\WebRecipeImportService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * Die ermittelten Rezeptdaten (nur skalare Werte / Arrays)
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
    public function generate(): void
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
            $aiService = app(AiRecipeService::class);
            $importService = app(WebRecipeImportService::class);

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
                ];

                $this->calories = (int) ($calc['calories'] ?? 0);
                $this->carbohydrates = (float) ($calc['carbohydrates'] ?? 0);
                $this->fat = (float) ($calc['fat'] ?? 0);
                $this->protein = (float) ($calc['protein'] ?? 0);
                $this->portions = $portions;

                // Automatische Hauptzutaten-Kategorien im Hintergrund zuweisen
                if ($this->autoAssignCategories) {
                    try {
                        Artisan::call('recipes:assign-ingredient-categories', [
                            '--user' => Auth::id(),
                            '--recipe' => $recipe->id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::warning('Kategorie-Zuweisung bei generate fehlgeschlagen: ' . $e->getMessage());
                    }
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

                // Speichere nur saubere skalare Daten (keine Objekte/DOM-Elemente)
                $this->recipeData = [
                    'type' => 'external',
                    'id' => null,
                    'title' => (string) ($parsed['title'] ?? 'Externes Rezept'),
                    'image_url' => $parsed['image_url'] ?? null,
                    'portions' => $portions,
                    'ingredients' => $ingredientStrings,
                    'source' => $sourceStr,
                    'external_title' => (string) ($parsed['title'] ?? 'Externes Rezept'),
                    'external_description' => (string) ($parsed['description'] ?? ''),
                    'external_source_url' => $target,
                    'external_ingredients' => $ingredients,
                    'external_steps' => $parsed['steps'] ?? [],
                    'external_prep_time' => $parsed['prep_time_minutes'] ?? null,
                    'external_cook_time' => $parsed['cook_time_minutes'] ?? null,
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
    public function saveNutrition()
    {
        if (!$this->recipeData) {
            $this->errorMessage = 'Es sind keine Rezeptdaten vorhanden. Bitte zuerst die Nährwerte berechnen.';
            return;
        }

        $this->isSaving = true;
        $this->errorMessage = null;

        try {
            $importService = app(WebRecipeImportService::class);

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

                // Artisan Kategoriezuweisung ausführen (mit sicherem try/catch)
                if ($this->autoAssignCategories) {
                    try {
                        Artisan::call('recipes:assign-ingredient-categories', [
                            '--user' => Auth::id(),
                            '--recipe' => $recipe->id,
                        ]);
                    } catch (\Throwable $catErr) {
                        Log::warning('Kategorie-Zuweisung bei commit fehlgeschlagen: ' . $catErr->getMessage());
                    }
                }

                session()->flash('success', "Nährwerte für „{$recipe->title}“ wurden erfolgreich gespeichert und die Zutaten-Kategorien wurden zugewiesen!");
                return redirect()->route('recipes.index');
            } elseif ($this->recipeData['type'] === 'external') {
                // Bei externen Rezepten wird das Rezept importiert und mit den Nährwerten gespeichert
                $parsed = [
                    'title' => $this->recipeData['external_title'] ?? 'Externes Rezept',
                    'description' => $this->recipeData['external_description'] ?? '',
                    'source_url' => $this->recipeData['external_source_url'] ?? '',
                    'portions' => $port,
                    'prep_time_minutes' => $this->recipeData['external_prep_time'] ?? null,
                    'cook_time_minutes' => $this->recipeData['external_cook_time'] ?? null,
                    'ingredients' => $this->recipeData['external_ingredients'] ?? [],
                    'steps' => $this->recipeData['external_steps'] ?? [],
                    'image_url' => $this->recipeData['image_url'] ?? null,
                    'nutrition' => [
                        'calories' => $cal,
                        'carbohydrates' => $carb,
                        'fat' => $f,
                        'protein' => $prot,
                        'raw' => $nutritionRaw,
                    ],
                ];

                $recipe = $importService->saveToDatabase(Auth::user(), $parsed);

                if ($this->autoAssignCategories) {
                    try {
                        Artisan::call('recipes:assign-ingredient-categories', [
                            '--user' => Auth::id(),
                            '--recipe' => $recipe->id,
                        ]);
                    } catch (\Throwable $catErr) {
                        Log::warning('Kategorie-Zuweisung bei commit fehlgeschlagen: ' . $catErr->getMessage());
                    }
                }

                session()->flash('success', "Rezept „{$recipe->title}“ wurde mit den Nährwerten erfolgreich gespeichert!");
                return redirect()->route('recipes.index');
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler beim Speichern in die Datenbank: ' . $e->getMessage();
        } finally {
            $this->isSaving = false;
        }
    }

    /**
     * Abbrechen: Leitet zurück zur Hauptseite (Rezeptübersicht)
     */
    public function cancel()
    {
        return redirect()->route('recipes.index');
    }

    public function render()
    {
        // 25 aktuellste Rezepte des Benutzers für ein schnelles Auswahlfeld laden
        $userRecipes = Recipe::where('user_id', Auth::id())
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('livewire.recipe-nutrition-calculator', [
            'userRecipes' => $userRecipes,
        ]);
    }
}
