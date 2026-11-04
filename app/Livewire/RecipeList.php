<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class RecipeList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'kat')]
    public string $selectedCategory = '';

    #[Url(as: 'fav')]
    public bool $onlyFavorites = false;

    #[Url(as: 'author')]
    public string $authorFilter = 'all'; // 'all', 'mine', 'shared'

    #[Url(as: 'sort')]
    public string $sortBy = 'updated_at';

    #[Url(as: 'view')]
    public string $viewMode = 'grid'; // 'grid' oder 'list'

    public function toggleViewMode(): void
    {
        $this->viewMode = ($this->viewMode === 'grid') ? 'list' : 'grid';
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['grid', 'list'], true) ? $mode : 'grid';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function updatingOnlyFavorites(): void
    {
        $this->resetPage();
    }

    public function updatingAuthorFilter(): void
    {
        $this->resetPage();
    }

    public function toggleFavorite(string $recipeId, RecipeService $recipeService): void
    {
        $user = Auth::user();
        $recipe = Recipe::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('is_shared', true);
        })->findOrFail($recipeId);

        $recipeService->toggleFavorite($user, $recipe);
    }

    public function selectCategory(string $categoryId): void
    {
        $this->selectedCategory = ($this->selectedCategory === $categoryId) ? '' : $categoryId;
        $this->resetPage();
    }

    public function toggleFavoriteFilter(): void
    {
        $this->onlyFavorites = !$this->onlyFavorites;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->onlyFavorites = false;
        $this->authorFilter = 'all';
        $this->sortBy = 'updated_at';
        $this->resetPage();
    }

    public function render()
    {
        $userId = Auth::id();

        $query = Recipe::with(['categories', 'user', 'favoritedBy', 'ingredients'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('is_shared', true);
            });

        // Autorenfilter
        if ($this->authorFilter === 'mine') {
            $query->where('user_id', $userId);
        } elseif ($this->authorFilter === 'shared') {
            $query->where('user_id', '!=', $userId)->where('is_shared', true);
        }

        // Suche
        if (!empty(trim($this->search))) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('ingredients', function ($iq) use ($term) {
                      $iq->where('name', 'like', "%{$term}%");
                  });
            });
        }

        // Kategorie-Filter
        if (!empty($this->selectedCategory)) {
            $query->whereHas('categories', function ($cq) {
                $cq->where('categories.id', $this->selectedCategory);
            });
        }

        // Favoriten-Filter
        if ($this->onlyFavorites) {
            $query->whereHas('favoritedBy', function ($fq) use ($userId) {
                $fq->where('user_id', $userId);
            });
        }

        // Sortierung
        switch ($this->sortBy) {
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'last_viewed':
                $query->orderByDesc('updated_at');
                break;
            case 'created_at':
                $query->orderByDesc('created_at');
                break;
            case 'rating':
                $query->orderByDesc('is_favorite')->orderByDesc('updated_at');
                break;
            case 'total_time':
                $query->orderByRaw('COALESCE(prep_time_minutes, 0) + COALESCE(cook_time_minutes, 0) asc');
                break;
            case 'updated_at':
            default:
                $query->orderByDesc('updated_at');
                break;
        }

        $recipes = $query->paginate(12);

        $categories = Category::withCount(['recipes' => function ($rq) use ($userId) {
            $rq->where('user_id', $userId)->orWhere('is_shared', true);
        }])->orderBy('name')->get();

        $totalRecipeCount = Recipe::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('is_shared', true);
        })->count();

        return view('livewire.recipe-list', [
            'recipes' => $recipes,
            'categories' => $categories,
            'totalRecipeCount' => $totalRecipeCount,
            'currentUserId' => $userId,
        ]);
    }
}
