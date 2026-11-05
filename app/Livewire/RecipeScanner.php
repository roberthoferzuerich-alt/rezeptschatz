<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeScanService;
use App\Services\RecipeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class RecipeScanner extends Component
{
    use WithFileUploads;

    public $imageFile;
    public ?string $imagePreviewUrl = null;
    public string $rawText = '';
    public bool $isParsed = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    // Strukturierte Rezeptfelder
    public string $title = '';
    public string $description = '';
    public int $portions = 4;
    public ?int $prep_time_minutes = null;
    public ?int $cook_time_minutes = null;
    public array $ingredients = [];
    public array $steps = [];

    public function updatedImageFile(): void
    {
        $this->validate([
            'imageFile' => 'image|max:15360', // max 15MB
        ]);

        $this->errorMessage = null;
        try {
            $this->imagePreviewUrl = $this->imageFile->temporaryUrl();
        } catch (\Exception $e) {
            $this->imagePreviewUrl = null;
        }

        // Event für Browser auslösen, um OCR vorzubereiten
        $this->dispatch('image-ready-for-ocr');
    }

    /**
     * Wird vom Browser/Tesseract.js aufgerufen, sobald der Text erkannt wurde.
     */
    public function setExtractedText(string $text, RecipeScanService $scanService): void
    {
        $this->rawText = trim($text);
        if (! empty($this->rawText)) {
            $this->parseText($scanService);
        }
    }

    /**
     * Parst den aktuellen Text in strukturierte Felder.
     */
    public function parseText(RecipeScanService $scanService): void
    {
        $text = trim($this->rawText);
        if (empty($text)) {
            $this->errorMessage = 'Bitte erfassen oder scannen Sie zuerst einen Rezepttext.';
            return;
        }

        $this->errorMessage = null;
        $parsed = $scanService->parseScannedText($text);

        $this->title = $parsed['title'];
        $this->description = $parsed['description'] ?? '';
        $this->portions = $parsed['portions'];
        $this->prep_time_minutes = $parsed['prep_time_minutes'];
        $this->cook_time_minutes = $parsed['cook_time_minutes'];
        $this->ingredients = $parsed['ingredients'];
        $this->steps = $parsed['steps'];

        // Falls keine Zutaten erkannt wurden, mindestens eine leere Zeile anbieten
        if (empty($this->ingredients)) {
            $this->ingredients[] = [
                'name' => '',
                'amount_numeric' => null,
                'unit' => '',
                'amount_text' => '',
                'sort_order' => 1,
            ];
        }

        // Falls keine Schritte erkannt wurden, mindestens eine leere Zeile anbieten
        if (empty($this->steps)) {
            $this->steps[] = [
                'instruction' => '',
                'step_number' => 1,
            ];
        }

        $this->isParsed = true;
    }

    public function addIngredient(): void
    {
        $this->ingredients[] = [
            'name' => '',
            'amount_numeric' => null,
            'unit' => '',
            'amount_text' => '',
            'sort_order' => count($this->ingredients) + 1,
        ];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
        foreach ($this->ingredients as $i => &$ing) {
            $ing['sort_order'] = $i + 1;
        }
    }

    public function addStep(): void
    {
        $this->steps[] = [
            'instruction' => '',
            'step_number' => count($this->steps) + 1,
        ];
    }

    public function removeStep(int $index): void
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
        foreach ($this->steps as $i => &$st) {
            $st['step_number'] = $i + 1;
        }
    }

    public function resetScan(): void
    {
        $this->imageFile = null;
        $this->imagePreviewUrl = null;
        $this->rawText = '';
        $this->isParsed = false;
        $this->errorMessage = null;
        $this->successMessage = null;
        $this->title = '';
        $this->description = '';
        $this->portions = 4;
        $this->prep_time_minutes = null;
        $this->cook_time_minutes = null;
        $this->ingredients = [];
        $this->steps = [];
    }

    public function saveRecipe(RecipeService $recipeService)
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'portions' => 'required|integer|min:1|max:99',
        ]);

        $user = Auth::user();
        if (! $user) {
            $this->errorMessage = 'Sie müssen angemeldet sein.';
            return;
        }

        try {
            $recipeId = (string) Str::uuid();
            $imagePath = null;
            $imageHash = null;

            // Foto speichern, falls aufgenommen oder hochgeladen
            if ($this->imageFile) {
                $targetFilename = "recipes/{$recipeId}.jpg";
                $imageContents = file_get_contents($this->imageFile->getRealPath());
                Storage::disk('local')->put($targetFilename, $imageContents);
                $imagePath = $targetFilename;
                $imageHash = hash('sha256', $imageContents);
            }

            // Zutaten filtern
            $cleanIngredients = [];
            foreach ($this->ingredients as $ing) {
                if (! empty(trim($ing['name'] ?? ''))) {
                    $cleanIngredients[] = [
                        'name' => trim($ing['name']),
                        'amount_numeric' => ! empty($ing['amount_numeric']) ? (float) $ing['amount_numeric'] : null,
                        'unit' => ! empty($ing['unit']) ? trim($ing['unit']) : null,
                        'amount_text' => ! empty($ing['amount_text']) ? trim($ing['amount_text']) : null,
                        'sort_order' => (int) ($ing['sort_order'] ?? 1),
                    ];
                }
            }

            // Schritte filtern
            $cleanSteps = [];
            foreach ($this->steps as $st) {
                if (! empty(trim($st['instruction'] ?? ''))) {
                    $cleanSteps[] = [
                        'instruction' => trim($st['instruction']),
                        'step_number' => (int) ($st['step_number'] ?? 1),
                    ];
                }
            }

            // Rezept über RecipeService anlegen
            $recipe = $recipeService->createRecipe($user, [
                'id' => $recipeId,
                'title' => trim($this->title),
                'description' => trim($this->description) ?: null,
                'portions' => $this->portions,
                'prep_time_minutes' => $this->prep_time_minutes,
                'cook_time_minutes' => $this->cook_time_minutes,
                'ingredients' => $cleanIngredients,
                'steps' => $cleanSteps,
                'is_shared' => true,
            ]);

            // Bildpfad aktualisieren falls vorhanden
            if ($imagePath) {
                $recipe->update([
                    'image_path' => $imagePath,
                    'image_hash' => $imageHash,
                ]);
            }

            // Automatische Kategorien-Zuordnung anhand der neuen Zutaten
            $this->assignCategoriesAutomatically($recipe, $user);

            session()->flash('success', "Rezept '{$recipe->title}' wurde erfolgreich aus dem Scan erstellt!");

            return redirect()->route('recipes.show', ['id' => $recipe->id]);
        } catch (\Exception $e) {
            $this->errorMessage = 'Fehler beim Speichern des Rezepts: ' . $e->getMessage();
        }
    }

    /**
     * Weist dem neu erstellten Rezept automatisch passende Kategorien zu.
     */
    protected function assignCategoriesAutomatically(Recipe $recipe, User $user): void
    {
        $allText = mb_strtolower($recipe->title . ' ' . implode(' ', array_column($this->ingredients, 'name')));

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

    public function render()
    {
        return view('livewire.recipe-scanner');
    }
}
