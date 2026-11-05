<?php

namespace App\Livewire;

use App\Services\WebRecipeImportService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WebRecipeImport extends Component
{
    public string $url = '';
    public bool $isAnalyzing = false;
    public ?array $parsedData = null;
    public ?string $errorMessage = null;

    protected $rules = [
        'url' => ['required', 'url', 'max:2000'],
    ];

    protected $messages = [
        'url.required' => 'Bitte geben Sie die Internet-Adresse (URL) des Rezepts ein.',
        'url.url' => 'Bitte geben Sie eine gültige Web-Adresse ein (z. B. https://...).',
    ];

    public function analyzeUrl(WebRecipeImportService $importService): void
    {
        $this->validate();

        $this->isAnalyzing = true;
        $this->errorMessage = null;
        $this->parsedData = null;

        $result = $importService->fetchAndParse($this->url);

        $this->isAnalyzing = false;

        if (!$result['success']) {
            $this->errorMessage = $result['error'];
            return;
        }

        $this->parsedData = $result;
    }

    public function cancel(): void
    {
        $this->parsedData = null;
        $this->errorMessage = null;
        $this->url = '';
    }

    public function saveDirectly(WebRecipeImportService $importService)
    {
        if (!$this->parsedData) {
            return;
        }

        try {
            $recipe = $importService->saveToDatabase(Auth::user(), $this->parsedData);

            session()->flash('success', "Rezept „{$recipe->title}“ wurde erfolgreich importiert und gespeichert!");
            return redirect()->route('recipes.show', $recipe->id);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }

    public function editBeforeSave()
    {
        if (!$this->parsedData) {
            return;
        }

        session()->flash('imported_web_recipe', $this->parsedData);
        return redirect()->route('recipes.create');
    }

    public function render()
    {
        return view('livewire.web-recipe-import');
    }
}

