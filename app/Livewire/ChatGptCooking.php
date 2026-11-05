<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Services\CookmateImportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ChatGptCooking extends Component
{
    // Wunschzutaten & Kriterien
    public string $fridgeIngredients = '';
    public string $timeLimit = '30';
    public string $diet = 'normal'; // 'normal', 'vegetarian', 'vegan', 'lowcarb', 'quick'
    public int $portions = 4;

    // ChatGPT-Antwort zum Einfügen
    public string $pastedResponse = '';
    public ?string $errorMessage = null;

    public function getGeneratedPromptProperty(): string
    {
        $ingredients = trim($this->fridgeIngredients);
        $dietText = match ($this->diet) {
            'vegetarian' => 'vegetarisch',
            'vegan' => 'vegan',
            'lowcarb' => 'kohlenhydratarm (Low-Carb)',
            'quick' => 'besonders schnell und unkompliziert',
            default => 'ausgewogen',
        };

        $prompt = "Erstelle mir bitte ein köstliches Rezept auf Deutsch.\n";
        if (!empty($ingredients)) {
            $prompt .= "Ich habe folgende Zutaten vorrätig: {$ingredients}\n";
        }
        $prompt .= "Zubereitungszeit: maximal {$this->timeLimit} Minuten.\n";
        $prompt .= "Portionen: {$this->portions} Personen.\n";
        $prompt .= "Ernährungsweise: {$dietText}.\n\n";

        $prompt .= "Bitte antworte exakt in folgendem Format, damit ich es direkt in mein Rezeptbuch importieren kann:\n\n";
        $prompt .= "Titel: [Rezepttitel]\n";
        $prompt .= "Portionen: {$this->portions}\n";
        $prompt .= "Vorbereitungszeit: [Minuten]\n";
        $prompt .= "Kochzeit: [Minuten]\n";
        $prompt .= "Beschreibung: [1-2 Sätze zum Gericht]\n\n";
        $prompt .= "Zutaten:\n";
        $prompt .= "- [Menge] [Einheit] [Zutat]\n";
        $prompt .= "- [Menge] [Einheit] [Zutat]\n\n";
        $prompt .= "Zubereitung:\n";
        $prompt .= "1. [Schritt 1]\n";
        $prompt .= "2. [Schritt 2]\n";

        return $prompt;
    }

    public function importPastedRecipe(CookmateImportService $importService)
    {
        $text = trim($this->pastedResponse);
        if (empty($text)) {
            $this->errorMessage = 'Bitte fügen Sie den Rezepttext von ChatGPT in das Textfeld ein.';
            return;
        }

        $user = Auth::user();
        $this->errorMessage = null;

        try {
            $lines = preg_split('/\r\n|\r|\n/', $text);
            $title = '';
            $portions = $this->portions;
            $prepTime = null;
            $cookTime = null;
            $description = '';
            $ingredients = [];
            $steps = [];

            $section = 'meta';

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Sektionen erkennen
                if (preg_match('/^(?:zutaten|ingredients):?/i', $line)) {
                    $section = 'ingredients';
                    continue;
                }
                if (preg_match('/^(?:zubereitung|anleitung|schritte|directions|instructions):?/i', $line)) {
                    $section = 'steps';
                    continue;
                }

                if ($section === 'meta') {
                    if (preg_match('/^titel\s*:\s*(.*)$/i', $line, $m)) {
                        $title = trim($m[1]);
                    } elseif (preg_match('/^#\s*(.*)$/', $line, $m) && empty($title)) {
                        $title = trim($m[1]);
                    } elseif (preg_match('/^portionen\s*:\s*(\d+)/i', $line, $m)) {
                        $portions = (int)$m[1];
                    } elseif (preg_match('/^vorbereitungszeit\s*:\s*(\d+)/i', $line, $m)) {
                        $prepTime = (int)$m[1];
                    } elseif (preg_match('/^kochzeit\s*:\s*(\d+)/i', $line, $m)) {
                        $cookTime = (int)$m[1];
                    } elseif (preg_match('/^beschreibung\s*:\s*(.*)$/i', $line, $m)) {
                        $description = trim($m[1]);
                    }
                } elseif ($section === 'ingredients') {
                    // Zutat (z. B. "- 400 g Kartoffeln")
                    $clean = preg_replace('/^[\-\*\•]\s*/', '', $line);
                    if (!empty($clean)) {
                        $ingredients[] = $clean;
                    }
                } elseif ($section === 'steps') {
                    // Schritt (z. B. "1. Kartoffeln schälen")
                    $clean = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                    if (!empty($clean)) {
                        $steps[] = $clean;
                    }
                }
            }

            // Fallback für Titel
            if (empty($title)) {
                $title = 'ChatGPT-Rezept vom ' . date('d.m.Y');
            }

            // Rezept anlegen
            $recipe = Recipe::create([
                'id' => (string)Str::uuid(),
                'user_id' => $user->id,
                'title' => mb_substr($title, 0, 255),
                'description' => $description ?: null,
                'portions' => $portions ?: 4,
                'prep_time_minutes' => $prepTime,
                'cook_time_minutes' => $cookTime,
                'notes' => "Erstellt mit ChatGPT (" . date('d.m.Y') . ")",
                'is_shared' => true,
                'server_version' => 1,
            ]);

            // Zutaten speichern
            $order = 1;
            foreach ($ingredients as $rawIng) {
                $parsed = $importService->parseIngredientLine($rawIng);
                RecipeIngredient::create([
                    'id' => (string)Str::uuid(),
                    'recipe_id' => $recipe->id,
                    'name' => mb_substr($parsed['name'], 0, 255),
                    'amount_numeric' => $parsed['amount_numeric'],
                    'unit' => $parsed['unit'],
                    'amount_text' => $parsed['amount_text'],
                    'sort_order' => $order++,
                ]);
            }

            // Schritte speichern
            $stepOrder = 1;
            foreach ($steps as $rawStep) {
                RecipeStep::create([
                    'id' => (string)Str::uuid(),
                    'recipe_id' => $recipe->id,
                    'step_number' => $stepOrder++,
                    'instruction' => $rawStep,
                ]);
            }

            session()->flash('success', "Rezept '{$recipe->title}' wurde erfolgreich von ChatGPT übernommen und angelegt!");
            return $this->redirectRoute('recipes.show', $recipe->id, navigate: true);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler beim Einlesen: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.chat-gpt-cooking', [
            'generatedPrompt' => $this->generatedPrompt,
        ]);
    }
}
