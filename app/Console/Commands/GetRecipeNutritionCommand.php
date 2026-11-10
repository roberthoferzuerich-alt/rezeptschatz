<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use App\Services\AiRecipeService;
use App\Services\WebRecipeImportService;
use Illuminate\Console\Command;

class GetRecipeNutritionCommand extends Command
{
    /**
     * Der Name und die Signatur des Konsolenbefehls.
     */
    protected $signature = 'recipe:nutritional-val 
                            {target : Link (URL), UUID oder Titel des Rezepts}
                            {--save : Speichert die berechneten Nährwerte direkt in der Datenbank}
                            {--recalculate : Erzwingt eine Neuberechnung, selbst wenn bereits Nährwerte in der Datenbank gespeichert sind}';

    /**
     * Aliase für den Befehl (z. B. get:nutritional-val oder recipe:get-nutrition).
     */
    protected $aliases = [
        'get:nutritional-val',
        'recipe:get-nutrition',
        'recipe:nutrition',
    ];

    /**
     * Die Beschreibung des Konsolenbefehls.
     */
    protected $description = 'Ermittelt oder berechnet die Nährwerte eines Rezepts anhand des Links, der ID oder des Titels';

    public function handle(AiRecipeService $aiService): int
    {
        $target = trim((string) $this->argument('target'));

        if (empty($target)) {
            $this->error('Bitte gib einen Rezept-Link, eine UUID oder einen Rezepttitel an.');
            return self::FAILURE;
        }

        $this->line("<fg=cyan>Suche Rezept für:</> <fg=yellow>{$target}</>");

        // 1. UUID aus dem Ziel extrahieren (unterstützt URLs wie https://rhz.internet-box.ch:8449/recipes/{uuid})
        $uuid = null;
        if (preg_match('/([0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12})/', $target, $matches)) {
            $uuid = $matches[1];
        }

        $recipe = null;

        // A: Suche nach UUID
        if ($uuid) {
            $recipe = Recipe::with('ingredients')->find($uuid);
        }

        // B: Suche nach source_url (falls target eine externe oder interne Quelle ist)
        if (!$recipe) {
            $recipe = Recipe::with('ingredients')->where('source_url', $target)->first();
        }

        // C: Suche nach Rezepttitel (exakt oder LIKE)
        if (!$recipe) {
            $recipe = Recipe::with('ingredients')
                ->where('title', 'like', "%{$target}%")
                ->latest()
                ->first();
        }

        // D: Fallback für externe URLs (z.B. Chefkoch, Leckerschmecker etc.), die noch nicht importiert wurden
        if (!$recipe && filter_var($target, FILTER_VALIDATE_URL)) {
            return $this->handleExternalUrl($target, $aiService);
        }

        if (!$recipe) {
            $this->error("Kein Rezept in der Datenbank gefunden für: '{$target}'");
            return self::FAILURE;
        }

        $portions = max(1, (int) ($recipe->portions ?? 4));
        $recalculate = (bool) $this->option('recalculate');
        $hasStoredNutrition = ($recipe->calories !== null && $recipe->calories > 0);

        $calories = 0;
        $carbs = 0.0;
        $fat = 0.0;
        $protein = 0.0;
        $sourceInfo = '';
        $wasCalculated = false;

        // Wenn bereits Nährwerte vorliegen und keine Neuberechnung erzwungen wird
        if ($hasStoredNutrition && !$recalculate) {
            $calories = (int) $recipe->calories;
            $carbs = (float) $recipe->carbohydrates;
            $fat = (float) $recipe->fat;
            $protein = (float) $recipe->protein;
            $sourceInfo = 'Gespeichert in Datenbank';
        } else {
            // Nährwerte neu berechnen anhand der Zutaten
            $this->comment("Berechne Nährwerte anhand von {$recipe->ingredients->count()} Zutaten...");
            $calc = $aiService->estimateNutrition($recipe->title, $portions, $recipe->ingredients->all());

            $calories = (int) ($calc['calories'] ?? 0);
            $carbs = (float) ($calc['carbohydrates'] ?? 0);
            $fat = (float) ($calc['fat'] ?? 0);
            $protein = (float) ($calc['protein'] ?? 0);
            $sourceInfo = $calc['source'] ?? 'Berechnet';
            $wasCalculated = true;

            if ($this->option('save')) {
                $recipe->calories = $calories;
                $recipe->carbohydrates = $carbs;
                $recipe->fat = $fat;
                $recipe->protein = $protein;
                $recipe->nutrition_raw = "{$calories} kcal | {$carbs}g KH | {$fat}g Fett | {$protein}g Eiweiss pro Portion";
                $recipe->server_version = ((int) $recipe->server_version) + 1;
                $recipe->save();
            }
        }

        // Ausgabe formatieren
        $this->displayNutritionCard(
            title: $recipe->title,
            id: $recipe->id,
            portions: $portions,
            source: $sourceInfo,
            calories: $calories,
            carbs: $carbs,
            fat: $fat,
            protein: $protein,
            ingredients: $recipe->ingredients->map(function ($ing) {
                $amount = $ing->amount_text ?: trim(($ing->amount_numeric ? rtrim(rtrim((string)$ing->amount_numeric, '0'), '.') : '') . ' ' . $ing->unit);
                return trim("{$amount} {$ing->name}");
            })->all()
        );

        if ($wasCalculated) {
            if ($this->option('save')) {
                $this->info("✓ Nährwerte wurden erfolgreich in der Datenbank für '{$recipe->title}' gespeichert!");
            } else {
                $this->newLine();
                $this->comment("Tipp: Mit --save kannst du diese berechneten Werte direkt im Rezept abspeichern:");
                $this->line("      <fg=cyan>php artisan recipe:nutritional-val \"{$target}\" --save</>");
            }
        } elseif ($hasStoredNutrition) {
            $this->newLine();
            $this->line("<fg=gray>Hinweis: Werte stammen aus der Datenbank. Verwende --recalculate für eine Neuberechnung.</>");
        }

        return self::SUCCESS;
    }

    /**
     * Behandelt externe URLs, die noch nicht in der Datenbank gespeichert sind.
     */
    protected function handleExternalUrl(string $url, AiRecipeService $aiService): int
    {
        $this->comment("Rezept nicht in Datenbank gefunden. Versuche Web-Import von externem Link...");

        try {
            $importService = app(WebRecipeImportService::class);
            $parsed = $importService->fetchAndParse($url);

            $title = $parsed['title'] ?? 'Externes Rezept';
            $portions = max(1, (int) ($parsed['portions'] ?? 4));
            $ingredients = $parsed['ingredients'] ?? [];

            // Prüfen, ob die Seite bereits Schema.org nutrition enthielt
            $nutrition = $parsed['nutrition'] ?? [];
            if (!empty($nutrition['calories'])) {
                $calories = (int) $nutrition['calories'];
                $carbs = (float) ($nutrition['carbohydrates'] ?? 0);
                $fat = (float) ($nutrition['fat'] ?? 0);
                $protein = (float) ($nutrition['protein'] ?? 0);
                $sourceInfo = 'Aus Web-Rezept extrahiert (Schema.org)';
            } else {
                $calc = $aiService->estimateNutrition($title, $portions, $ingredients);
                $calories = (int) $calc['calories'];
                $carbs = (float) $calc['carbohydrates'];
                $fat = (float) $calc['fat'];
                $protein = (float) $calc['protein'];
                $sourceInfo = $calc['source'] ?? 'Berechnet anhand Zutaten';
            }

            $ingredientStrings = array_map(function ($ing) {
                if (is_string($ing)) return $ing;
                $amount = $ing['amount_text'] ?? trim(($ing['amount_numeric'] ?? '') . ' ' . ($ing['unit'] ?? ''));
                return trim("{$amount} " . ($ing['name'] ?? ''));
            }, $ingredients);

            $this->displayNutritionCard(
                title: $title,
                id: 'Extern (nicht gespeichert)',
                portions: $portions,
                source: $sourceInfo,
                calories: $calories,
                carbs: $carbs,
                fat: $fat,
                protein: $protein,
                ingredients: $ingredientStrings
            );

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Konnte externes Rezept nicht analysieren: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Gibt die formatierte Nährwerttabelle auf der Konsole aus.
     */
    protected function displayNutritionCard(
        string $title,
        string $id,
        int $portions,
        string $source,
        int $calories,
        float $carbs,
        float $fat,
        float $protein,
        array $ingredients = []
    ): void {
        $totalCalories = $calories * $portions;
        $totalCarbs = round($carbs * $portions, 1);
        $totalFat = round($fat * $portions, 1);
        $totalProtein = round($protein * $portions, 1);

        $this->newLine();
        $this->line("<options=bold;fg=cyan>========================================================================</>");
        $this->line("<options=bold;fg=green> REZEPT-NÄHRWERTE: {$title}</>");
        $this->line("<fg=gray> ID:       {$id}</>");
        $this->line("<fg=gray> Portionen: {$portions}</>");
        $this->line("<fg=gray> Herkunft: {$source}</>");
        $this->line("<options=bold;fg=cyan>========================================================================</>");
        $this->newLine();

        $this->table(
            ['Nährwert', 'Pro Portion', "Gesamt ({$portions} Portionen)"],
            [
                ['Kalorien (Energie)', "{$calories} kcal", number_format($totalCalories, 0, '.', "'") . " kcal"],
                ['Kohlenhydrate', number_format($carbs, 1, '.', '') . " g", number_format($totalCarbs, 1, '.', '') . " g"],
                ['Fett', number_format($fat, 1, '.', '') . " g", number_format($totalFat, 1, '.', '') . " g"],
                ['Eiweiss / Protein', number_format($protein, 1, '.', '') . " g", number_format($totalProtein, 1, '.', '') . " g"],
            ]
        );

        if (!empty($ingredients)) {
            $this->newLine();
            $this->line("<fg=yellow;options=bold>Berücksichtigte Zutaten (" . count($ingredients) . "):</>");
            foreach (array_slice($ingredients, 0, 15) as $ing) {
                $this->line("  <fg=white>•</> {$ing}");
            }
            if (count($ingredients) > 15) {
                $this->line("  <fg=gray>... und " . (count($ingredients) - 15) . " weitere</>");
            }
        }
    }
}
