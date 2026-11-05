<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;

class UpdateRecipeNutritionCommand extends Command
{
    protected $signature = 'recipe:set-nutrition 
                            {title? : Titel oder Teil des Titels des Rezepts}
                            {--calories= : Kalorien in kcal pro Portion}
                            {--carbs= : Kohlenhydrate in g pro Portion}
                            {--fat= : Fett in g pro Portion}
                            {--protein= : Eiweiss in g pro Portion}';

    protected $description = 'Trägt Nährwerte für ein bestimmtes Rezept oder für die KI-Hackfleischpfanne nach';

    public function handle(): int
    {
        $search = $this->argument('title');

        if (empty($search)) {
            // Standardfall: Das neu generierte Kühlschrank-Rezept finden
            $recipe = Recipe::where(function ($q) {
                $q->where('title', 'like', '%Hackfleisch%')
                  ->where('title', 'like', '%Karotte%');
            })->orWhere('title', 'like', '%Herzhafte Hackfleisch%')
              ->latest()
              ->first();
        } else {
            $recipe = Recipe::where('title', 'like', "%{$search}%")->latest()->first();
        }

        if (!$recipe) {
            $this->error("Kein passendes Rezept gefunden" . ($search ? " für Suchbegriff '{$search}'" : "") . ".");
            return self::FAILURE;
        }

        // Standardwerte für die herzhafte Hackfleisch-Pfanne (falls keine Optionen übergeben wurden)
        $calories = $this->option('calories') !== null ? (int) $this->option('calories') : 545;
        $carbs = $this->option('carbs') !== null ? (float) $this->option('carbs') : 12.0;
        $fat = $this->option('fat') !== null ? (float) $this->option('fat') : 36.0;
        $protein = $this->option('protein') !== null ? (float) $this->option('protein') : 41.5;

        $recipe->calories = $calories;
        $recipe->carbohydrates = $carbs;
        $recipe->fat = $fat;
        $recipe->protein = $protein;
        $recipe->nutrition_raw = "{$calories} kcal | {$carbs} g KH | {$fat} g Fett | {$protein} g Eiweiss pro Portion";
        $recipe->server_version = $recipe->server_version + 1;
        $recipe->save();

        $this->info("✓ Nährwerte für '{$recipe->title}' erfolgreich gespeichert!");
        $this->table(
            ['Nährwert', 'Wert pro Portion'],
            [
                ['Kalorien', "{$calories} kcal"],
                ['Kohlenhydrate', "{$carbs} g"],
                ['Fett', "{$fat} g"],
                ['Eiweiss / Protein', "{$protein} g"],
            ]
        );

        return self::SUCCESS;
    }
}
