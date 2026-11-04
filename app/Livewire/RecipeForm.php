<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class RecipeForm extends Component
{
    use WithFileUploads;

    public ?string $recipeId = null;
    public bool $isEditing = false;

    // Rezept-Hauptfelder
    public string $title = '';
    public string $description = '';
    public int $portions = 4;
    public ?int $prep_time_minutes = null;
    public ?int $cook_time_minutes = null;
    public ?int $rest_time_minutes = null;
    public string $notes = '';
    public string $source_url = '';
    public bool $is_favorite = false;

    // Kategorien
    public array $selectedCategoryIds = [];

    // Zutaten (dynamische Zeilen)
    public array $ingredients = [];

    // Zubereitungsschritte (dynamische Zeilen)
    public array $steps = [];

    // Bild-Upload
    public $newImage;
    public ?string $existingImagePath = null;
    public bool $removeExistingImage = false;

    public function mount(?string $id = null): void
    {
        if ($id) {
            $this->recipeId = $id;
            $this->isEditing = true;
            $recipe = Recipe::with(['categories', 'ingredients', 'steps'])
                ->where('user_id', Auth::id())
                ->findOrFail($id);

            $this->title = $recipe->title;
            $this->description = $recipe->description ?? '';
            $this->portions = $recipe->portions ?: 4;
            $this->prep_time_minutes = $recipe->prep_time_minutes;
            $this->cook_time_minutes = $recipe->cook_time_minutes;
            $this->rest_time_minutes = $recipe->rest_time_minutes;
            $this->notes = $recipe->notes ?? '';
            $this->source_url = $recipe->source_url ?? '';
            $this->is_favorite = (bool) $recipe->is_favorite;
            $this->existingImagePath = $recipe->image_path;

            $this->selectedCategoryIds = $recipe->categories->pluck('id')->toArray();

            foreach ($recipe->ingredients as $item) {
                $this->ingredients[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'amount_numeric' => $item->amount_numeric !== null ? (float) $item->amount_numeric : '',
                    'unit' => $item->unit ?? '',
                    'amount_text' => $item->amount_text ?? '',
                    'group_name' => $item->group_name ?? '',
                ];
            }

            foreach ($recipe->steps as $step) {
                $this->steps[] = [
                    'id' => $step->id,
                    'instruction' => $step->instruction,
                ];
            }
        }

        // Falls neu oder leer: Standardzeilen anlegen
        if (empty($this->ingredients)) {
            $this->addIngredient();
            $this->addIngredient();
        }

        if (empty($this->steps)) {
            $this->addStep();
        }
    }

    public function addIngredient(): void
    {
        $this->ingredients[] = [
            'id' => null,
            'name' => '',
            'amount_numeric' => '',
            'unit' => '',
            'amount_text' => '',
            'group_name' => '',
        ];
    }

    public function removeIngredient(int $index): void
    {
        unset($this->ingredients[$index]);
        $this->ingredients = array_values($this->ingredients);
    }

    public function addStep(): void
    {
        $this->steps[] = [
            'id' => null,
            'instruction' => '',
        ];
    }

    public function removeStep(int $index): void
    {
        unset($this->steps[$index]);
        $this->steps = array_values($this->steps);
    }

    public function markRemoveExistingImage(): void
    {
        $this->removeExistingImage = true;
        $this->newImage = null;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'portions' => ['required', 'integer', 'min:1', 'max:100'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0'],
            'rest_time_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'is_favorite' => ['boolean'],
            'selectedCategoryIds' => ['array'],
            'ingredients.*.name' => ['nullable', 'string', 'max:255'],
            'ingredients.*.amount_numeric' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.amount_text' => ['nullable', 'string', 'max:100'],
            'ingredients.*.group_name' => ['nullable', 'string', 'max:100'],
            'steps.*.instruction' => ['nullable', 'string'],
            'newImage' => ['nullable', 'image', 'max:10240'], // max 10MB
        ];
    }

    public function save(RecipeService $recipeService)
    {
        $this->validate();

        $user = Auth::user();

        // Zutaten filtern
        $cleanIngredients = [];
        foreach ($this->ingredients as $index => $item) {
            if (!empty(trim($item['name'] ?? ''))) {
                $cleanIngredients[] = [
                    'sort_order' => $index,
                    'name' => trim($item['name']),
                    'amount_numeric' => $item['amount_numeric'] !== '' ? (float) $item['amount_numeric'] : null,
                    'unit' => trim($item['unit'] ?? ''),
                    'amount_text' => trim($item['amount_text'] ?? ''),
                    'group_name' => trim($item['group_name'] ?? ''),
                ];
            }
        }

        // Schritte filtern
        $cleanSteps = [];
        $stepCounter = 1;
        foreach ($this->steps as $step) {
            if (!empty(trim($step['instruction'] ?? ''))) {
                $cleanSteps[] = [
                    'step_number' => $stepCounter++,
                    'instruction' => trim($step['instruction']),
                ];
            }
        }

        $payload = [
            'title' => trim($this->title),
            'description' => trim($this->description),
            'portions' => (int) $this->portions,
            'prep_time_minutes' => $this->prep_time_minutes,
            'cook_time_minutes' => $this->cook_time_minutes,
            'rest_time_minutes' => $this->rest_time_minutes,
            'notes' => trim($this->notes),
            'source_url' => trim($this->source_url),
            'is_favorite' => $this->is_favorite,
            'category_ids' => $this->selectedCategoryIds,
            'ingredients' => $cleanIngredients,
            'steps' => $cleanSteps,
        ];

        if ($this->newImage) {
            $payload['image'] = $this->newImage;
        } elseif ($this->removeExistingImage) {
            $payload['remove_image'] = true;
        }

        if ($this->isEditing) {
            $recipe = Recipe::findOrFail($this->recipeId);
            $this->authorize('update', $recipe);
            $recipe = $recipeService->updateRecipe($recipe, $payload);
            session()->flash('success', "Rezept '{$recipe->title}' erfolgreich aktualisiert.");
        } else {
            $this->authorize('create', Recipe::class);
            $recipe = $recipeService->createRecipe($user, $payload);
            session()->flash('success', "Rezept '{$recipe->title}' erfolgreich angelegt.");
        }

        return $this->redirectRoute('recipes.show', $recipe->id, navigate: true);
    }

    public function render()
    {
        $categories = Category::where('user_id', Auth::id())->orderBy('name')->get();

        return view('livewire.recipe-form', [
            'categories' => $categories,
        ]);
    }
}

