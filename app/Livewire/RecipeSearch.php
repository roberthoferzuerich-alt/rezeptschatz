<?php

namespace App\Livewire;

use App\Models\Recipe;
use App\Services\RecipeSearchService;
use App\Services\WebRecipeImportService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RecipeSearch extends Component
{
    public string $query = '';
    public string $language = 'de';
    public string $site = 'all';

    public bool $hasSearched = false;
    public bool $isSearching = false;
    public bool $isImporting = false;
    public ?string $activeImportUrl = null;

    /** @var array<int, array> */
    public array $webResults = [];

    /** @var \Illuminate\Database\Eloquent\Collection<int, Recipe> */
    public $localResults = null;

    public ?array $parsedRecipe = null;
    public ?string $errorMessage = null;

    protected $queryString = [
        'query' => ['except' => '', 'as' => 'q'],
        'site' => ['except' => 'all'],
        'language' => ['except' => 'de', 'as' => 'lang'],
    ];

    public function mount(RecipeSearchService $searchService, WebRecipeImportService $importService): void
    {
        $this->site = request()->query('site', $this->site);
        $this->language = request()->query('lang', $this->language);
        $initialQuery = request()->query('q', request()->query('url', $this->query));

        if (!empty($initialQuery)) {
            $this->query = $initialQuery;
            $this->search($searchService, $importService);
        }
    }

    public function search(RecipeSearchService $searchService, WebRecipeImportService $importService): void
    {
        $this->errorMessage = null;
        $this->parsedRecipe = null;
        $cleanQuery = trim($this->query);

        if ($cleanQuery === '') {
            $this->hasSearched = false;
            $this->webResults = [];
            $this->localResults = collect();
            return;
        }

        // Prüfen, ob eine direkte Web-Adresse (URL) eingegeben wurde
        if (filter_var($cleanQuery, FILTER_VALIDATE_URL) || str_starts_with($cleanQuery, 'http://') || str_starts_with($cleanQuery, 'https://')) {
            $this->isSearching = true;
            $result = $importService->fetchAndParse($cleanQuery);
            $this->isSearching = false;

            if (!$result['success']) {
                $this->errorMessage = $result['error'];
                return;
            }

            $this->parsedRecipe = $result;
            $this->hasSearched = true;
            return;
        }

        // Es handelt sich um eine Stichwortsuche
        $this->isSearching = true;
        $this->hasSearched = true;

        // 1. Lokale Rezepte durchsuchen (wenn nicht rein extern)
        if ($this->site === 'rezeptschatz' || $this->site === 'all') {
            $this->localResults = $searchService->searchLocal(Auth::user(), $cleanQuery);
        } else {
            $this->localResults = collect();
        }

        // 2. Web-Rezepte durchsuchen
        if ($this->site !== 'rezeptschatz') {
            $this->webResults = $searchService->searchWeb($cleanQuery, $this->site);
        } else {
            $this->webResults = [];
        }

        $this->isSearching = false;
    }

    /**
     * Startet den Import eines Web-Suchergebnisses und öffnet die Detail-Vorschau.
     */
    public function importFromUrl(string $url, WebRecipeImportService $importService): void
    {
        $this->isImporting = true;
        $this->activeImportUrl = $url;
        $this->errorMessage = null;

        $result = $importService->fetchAndParse($url);

        $this->isImporting = false;
        $this->activeImportUrl = null;

        if (!$result['success']) {
            $this->errorMessage = 'Fehler beim Laden des Rezepts: ' . $result['error'];
            return;
        }

        $this->parsedRecipe = $result;
    }

    /**
     * Speichert das in der Vorschau geöffnete Rezept direkt in die Datenbank.
     */
    public function saveParsedRecipe(WebRecipeImportService $importService)
    {
        if (!$this->parsedRecipe) {
            return;
        }

        try {
            $recipe = $importService->saveToDatabase(Auth::user(), $this->parsedRecipe);

            session()->flash('success', "Rezept „{$recipe->title}“ wurde erfolgreich im Rezeptschatz gespeichert!");
            return redirect()->route('recipes.show', $recipe->id);
        } catch (\Throwable $e) {
            $this->errorMessage = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }

    /**
     * Öffnet das Rezept vor dem Speichern im vollwertigen Formular.
     */
    public function editBeforeSave()
    {
        if (!$this->parsedRecipe) {
            return;
        }

        session()->flash('imported_web_recipe', $this->parsedRecipe);
        return redirect()->route('recipes.create');
    }

    /**
     * Schließt die Rezept-Vorschau.
     */
    public function closePreview(): void
    {
        $this->parsedRecipe = null;
    }

    /**
     * Setzt die Suche vollständig zurück.
     */
    public function resetSearch(): void
    {
        $this->query = '';
        $this->hasSearched = false;
        $this->webResults = [];
        $this->localResults = collect();
        $this->parsedRecipe = null;
        $this->errorMessage = null;
    }

    public function render(RecipeSearchService $searchService)
    {
        $externalUrl = null;
        if (!empty($this->query) && !in_array($this->site, ['all', 'rezeptschatz'])) {
            $externalUrl = $searchService->getExternalSearchUrl($this->site, $this->query);
        }

        return view('livewire.recipe-search', [
            'sites' => RecipeSearchService::SITES,
            'languages' => RecipeSearchService::LANGUAGES,
            'externalUrl' => $externalUrl,
        ]);
    }
}

