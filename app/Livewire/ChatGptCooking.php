<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\User;
use App\Services\AiRecipeService;
use App\Services\CookmateImportService;
use App\Services\RecipeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ChatGptCooking extends Component
{
    use WithFileUploads;

    // Modus: 'photo' (Kühlschrank fotografieren), 'text' (Zutaten als Text), 'manual' (ChatGPT-Prompt)
    public string $mode = 'photo';

    // Foto vom Kühlschrank
    public $fridgePhoto;
    public ?string $photoPreviewUrl = null;

    // Kriterien
    public string $fridgeIngredients = '';
    public string $timeLimit = '30';
    public string $diet = 'normal'; // 'normal', 'vegetarian', 'vegan', 'lowcarb', 'quick'
    public int $portions = 4;
    public string $extraWishes = '';

    // Status & Feedback
    public bool $isGenerating = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    // Generiertes KI-Rezept
    public ?array $generatedRecipe = null;

    // ChatGPT-Antwort für manuellen Modus
    public string $pastedResponse = '';

    // API-Schlüssel Verwaltung
    public string $apiProviderInput = 'gemini';
    public string $apiKeyInput = '';
    public bool $showApiKeyModal = false;

    public function updatedFridgePhoto(): void
    {
        $this->validate([
            'fridgePhoto' => 'image|max:15360', // max 15MB
        ]);

        $this->errorMessage = null;
        try {
            $this->photoPreviewUrl = $this->fridgePhoto->temporaryUrl();
        } catch (\Exception $e) {
            $this->photoPreviewUrl = null;
        }
    }

    /**
     * Startet die Bildanalyse und Rezepterstellung über die KI (Gemini oder OpenAI).
     */
    public function generateFromPhoto(AiRecipeService $aiService): void
    {
        if (! $this->fridgePhoto) {
            $this->errorMessage = 'Bitte nehmen Sie zuerst ein Foto Ihres Kühlschranks oder Vorrats auf.';
            return;
        }

        if (! $aiService->isConfigured()) {
            $this->errorMessage = 'Bitte hinterlegen Sie zuerst einen KI-API-Schlüssel (Google Gemini oder OpenAI).';
            $this->showApiKeyModal = true;
            return;
        }

        $this->isGenerating = true;
        $this->errorMessage = null;
        $this->generatedRecipe = null;

        try {
            $imageBytes = file_get_contents($this->fridgePhoto->getRealPath());
            $mimeType = $this->fridgePhoto->getMimeType() ?: 'image/jpeg';

            $result = $aiService->generateFromFridgeImage($imageBytes, $mimeType, [
                'portions' => $this->portions,
                'time_limit' => $this->timeLimit,
                'diet' => $this->diet,
                'extra_wishes' => $this->extraWishes,
            ]);

            $this->generatedRecipe = $result;
            $this->successMessage = 'Rezept erfolgreich aus Ihrem Kühlschrankinhalt generiert!';
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Startet die Rezepterstellung direkt aus der Zutaten-Textliste mit der KI.
     */
    public function generateFromTextAi(AiRecipeService $aiService): void
    {
        $ingredients = trim($this->fridgeIngredients);
        if (empty($ingredients)) {
            $this->errorMessage = 'Bitte geben Sie ein paar Zutaten ein, die Sie vorrätig haben.';
            return;
        }

        if (! $aiService->isConfigured()) {
            $this->errorMessage = 'Bitte hinterlegen Sie zuerst einen KI-API-Schlüssel.';
            $this->showApiKeyModal = true;
            return;
        }

        $this->isGenerating = true;
        $this->errorMessage = null;
        $this->generatedRecipe = null;

        try {
            $result = $aiService->generateFromText($ingredients, [
                'portions' => $this->portions,
                'time_limit' => $this->timeLimit,
                'diet' => $this->diet,
                'extra_wishes' => $this->extraWishes,
            ]);

            $this->generatedRecipe = $result;
            $this->successMessage = 'Rezept erfolgreich generiert!';
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    /**
     * Speichert das generierte KI-Rezept direkt in der Datenbank.
     */
    public function saveGeneratedRecipe(RecipeService $recipeService)
    {
        if (! $this->generatedRecipe) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            $this->errorMessage = 'Sie müssen angemeldet sein.';
            return;
        }

        try {
            $recipeId = (string) Str::uuid();
            $imagePath = null;
            $imageHash = null;

            // Foto des Kühlschranks als Rezeptbild sichern falls vorhanden
            if ($this->fridgePhoto) {
                $targetFilename = "recipes/{$recipeId}.jpg";
                $imageContents = file_get_contents($this->fridgePhoto->getRealPath());
                Storage::disk('local')->put($targetFilename, $imageContents);
                $imagePath = $targetFilename;
                $imageHash = hash('sha256', $imageContents);
            }

            $cleanIngredients = [];
            foreach ($this->generatedRecipe['ingredients'] as $i => $ing) {
                if (! empty(trim($ing['name'] ?? ''))) {
                    $cleanIngredients[] = [
                        'name' => trim($ing['name']),
                        'amount_numeric' => isset($ing['amount_numeric']) && is_numeric($ing['amount_numeric']) ? (float) $ing['amount_numeric'] : null,
                        'unit' => ! empty($ing['unit']) ? trim($ing['unit']) : null,
                        'amount_text' => ! empty($ing['amount_text']) ? trim($ing['amount_text']) : null,
                        'sort_order' => $i + 1,
                    ];
                }
            }

            $cleanSteps = [];
            foreach ($this->generatedRecipe['steps'] as $i => $st) {
                if (! empty(trim($st['instruction'] ?? ''))) {
                    $cleanSteps[] = [
                        'instruction' => trim($st['instruction']),
                        'step_number' => $i + 1,
                    ];
                }
            }

            $recipe = $recipeService->createRecipe($user, [
                'id' => $recipeId,
                'title' => $this->generatedRecipe['title'],
                'description' => $this->generatedRecipe['description'] ?: null,
                'portions' => (int) ($this->generatedRecipe['portions'] ?? $this->portions),
                'prep_time_minutes' => $this->generatedRecipe['prep_time_minutes'] ?? null,
                'cook_time_minutes' => $this->generatedRecipe['cook_time_minutes'] ?? null,
                'calories' => $this->generatedRecipe['calories'] ?? null,
                'carbohydrates' => $this->generatedRecipe['carbohydrates'] ?? null,
                'fat' => $this->generatedRecipe['fat'] ?? null,
                'protein' => $this->generatedRecipe['protein'] ?? null,
                'notes' => 'Kreiert mit Kühlschrank-KI am ' . date('d.m.Y'),
                'ingredients' => $cleanIngredients,
                'steps' => $cleanSteps,
                'is_shared' => true,
            ]);

            if ($imagePath) {
                $recipe->update([
                    'image_path' => $imagePath,
                    'image_hash' => $imageHash,
                ]);
            }

            // Automatische Kategorien-Zuordnung
            $this->assignCategoriesAutomatically($recipe, $user);

            session()->flash('success', "KI-Rezept '{$recipe->title}' wurde erfolgreich in Ihrem Rezeptschatz gespeichert!");

            return redirect()->route('recipes.show', ['id' => $recipe->id]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }

    /**
     * Übergibt das generierte KI-Rezept in den vollständigen RecipeForm-Editor.
     */
    public function editInFullEditor()
    {
        if (! $this->generatedRecipe) {
            return;
        }

        $imagePath = null;
        if ($this->fridgePhoto) {
            $tempId = (string) Str::uuid();
            $targetFilename = "recipes/{$tempId}.jpg";
            $imageContents = file_get_contents($this->fridgePhoto->getRealPath());
            Storage::disk('local')->put($targetFilename, $imageContents);
            $imagePath = $targetFilename;
        }

        session()->flash('imported_scanned_recipe', [
            'title' => $this->generatedRecipe['title'],
            'description' => $this->generatedRecipe['description'] ?? '',
            'portions' => $this->generatedRecipe['portions'] ?? $this->portions,
            'prep_time_minutes' => $this->generatedRecipe['prep_time_minutes'] ?? null,
            'cook_time_minutes' => $this->generatedRecipe['cook_time_minutes'] ?? null,
            'calories' => $this->generatedRecipe['calories'] ?? null,
            'carbohydrates' => $this->generatedRecipe['carbohydrates'] ?? null,
            'fat' => $this->generatedRecipe['fat'] ?? null,
            'protein' => $this->generatedRecipe['protein'] ?? null,
            'image_path' => $imagePath,
            'ingredients' => $this->generatedRecipe['ingredients'] ?? [],
            'steps' => $this->generatedRecipe['steps'] ?? [],
        ]);

        return redirect()->route('recipes.create');
    }

    /**
     * Speichert einen neuen API-Schlüssel persistent in Datenbank & .env.
     */
    public function saveApiKey(AiRecipeService $aiService): void
    {
        $key = trim($this->apiKeyInput);
        if (empty($key)) {
            $this->errorMessage = 'Bitte geben Sie einen gültigen API-Schlüssel ein.';
            return;
        }

        $aiService->saveApiKey($this->apiProviderInput, $key);

        $this->showApiKeyModal = false;
        $this->apiKeyInput = '';
        $this->successMessage = 'API-Schlüssel wurde erfolgreich gespeichert und aktiviert!';
        $this->errorMessage = null;
    }

    /**
     * Entfernt den gespeicherten API-Schlüssel.
     */
    public function removeApiKey(AiRecipeService $aiService): void
    {
        $aiService->removeApiKey();
        $this->showApiKeyModal = false;
        $this->apiKeyInput = '';
        $this->successMessage = 'API-Schlüssel wurde entfernt.';
        $this->errorMessage = null;
    }

    /**
     * Weist dem neu erstellten Rezept automatisch passende Kategorien zu.
     */
    protected function assignCategoriesAutomatically(Recipe $recipe, User $user): void
    {
        $ingredientsNames = array_column($this->generatedRecipe['ingredients'] ?? [], 'name');
        $allText = mb_strtolower($recipe->title . ' ' . implode(' ', $ingredientsNames));

        $rules = [
            'Fleisch' => ['rind', 'schwein', 'kalb', 'hackfleisch', 'speck', 'schinken', 'bratwurst', 'geschnetzelt', 'fleisch'],
            'Geflügel' => ['poulet', 'hähnchen', 'huhn', 'trute', 'truthahn', 'geflügel', 'ente'],
            'Fisch' => ['fisch', 'lachs', 'thunfisch', 'forelle', 'dorsch', 'crevette', 'garnele', 'meeresfrüchte'],
            'Teigwaren' => ['teigwaren', 'nudel', 'pasta', 'spaghetti', 'penne', 'spätzli', 'lasagne'],
            'Reis' => ['reis', 'risotto', 'basmati'],
            'Kartoffel' => ['kartoffel', 'gschwellti', 'härdöpfel'],
            'Gemüse' => ['gemüse', 'rüebli', 'karotte', 'zucchetti', 'peperoni', 'spinat', 'broccoli', 'blumenkohl', 'lauch'],
            'Pilz' => ['pilz', 'champignon', 'steinpilz', 'morchel', 'pfifferling'],
            'Käse' => ['käse', 'gruyère', 'parmesan', 'mozzarella', 'raclette'],
        ];

        $categoryIds = [];
        foreach ($rules as $catName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($allText, $kw)) {
                    $category = Category::firstOrCreate(
                        ['user_id' => $user->id, 'name' => $catName],
                        ['color' => '#8ea92c']
                    );
                    $categoryIds[] = $category->id;
                    break;
                }
            }
        }

        if (! empty($categoryIds)) {
            $recipe->categories()->syncWithoutDetaching($categoryIds);
        }
    }

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
                    $clean = preg_replace('/^[\-\*\•]\s*/', '', $line);
                    if (!empty($clean)) {
                        $ingredients[] = $clean;
                    }
                } elseif ($section === 'steps') {
                    $clean = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                    if (!empty($clean)) {
                        $steps[] = $clean;
                    }
                }
            }

            if (empty($title)) {
                $title = 'ChatGPT-Rezept vom ' . date('d.m.Y');
            }

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

            $order = 1;
            foreach ($ingredients as $rawIng) {
                $parsed = $importService->parseIngredientLine($rawIng);
                RecipeIngredient::create([
                    'id' => (string)Str::uuid(),
                    'recipe_id' => $recipe->id,
                    'sort_order' => $order++,
                    'name' => $parsed['name'] ?: $rawIng,
                    'amount_numeric' => $parsed['amount_numeric'],
                    'unit' => $parsed['unit'],
                    'amount_text' => $parsed['amount_text'],
                ]);
            }

            $stepOrder = 1;
            foreach ($steps as $rawStep) {
                RecipeStep::create([
                    'id' => (string)Str::uuid(),
                    'recipe_id' => $recipe->id,
                    'step_number' => $stepOrder++,
                    'instruction' => $rawStep,
                ]);
            }

            session()->flash('success', "Rezept '{$recipe->title}' wurde erfolgreich aus der ChatGPT-Antwort importiert!");

            return redirect()->route('recipes.show', ['id' => $recipe->id]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Fehler beim Import: ' . $e->getMessage();
        }
    }

    public function render(AiRecipeService $aiService)
    {
        return view('livewire.chat-gpt-cooking', [
            'isAiConfigured' => $aiService->isConfigured(),
            'activeAiProvider' => $aiService->getActiveProvider(),
        ]);
    }
}
