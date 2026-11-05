<?php

namespace App\Livewire;

use App\Models\MealPlan;
use App\Models\Recipe;
use App\Services\ShoppingListService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MealPlanner extends Component
{
    public string $currentWeekStart = '';

    // Modal für Rezeptauswahl / Eigenes Gericht
    public bool $showRecipeModal = false;
    public string $modalDate = '';
    public string $modalMealType = 'dinner'; // 'lunch' oder 'dinner'
    public string $recipeSearch = '';
    public string $customTitle = '';
    public int $modalPortions = 4;

    public function mount(): void
    {
        $this->currentWeekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
    }

    public function previousWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)->subWeek()->startOfWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->currentWeekStart = Carbon::parse($this->currentWeekStart)->addWeek()->startOfWeek()->format('Y-m-d');
    }

    public function currentWeek(): void
    {
        $this->currentWeekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
    }

    public function openAddModal(string $date, string $mealType): void
    {
        $this->modalDate = $date;
        $this->modalMealType = $mealType;
        $this->recipeSearch = '';
        $this->customTitle = '';
        $this->modalPortions = 4;
        $this->showRecipeModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showRecipeModal = false;
    }

    public function assignRecipe(string $recipeId): void
    {
        $recipe = Recipe::where('user_id', Auth::id())
            ->orWhere('is_shared', true)
            ->findOrFail($recipeId);

        MealPlan::create([
            'id' => (string) Str::uuid(),
            'user_id' => Auth::id(),
            'recipe_id' => $recipe->id,
            'custom_title' => null,
            'date' => $this->modalDate,
            'meal_type' => $this->modalMealType,
            'portions' => $this->modalPortions ?: ($recipe->portions ?: 4),
        ]);

        $this->showRecipeModal = false;
        session()->flash('success', "„{$recipe->title}“ für den " . Carbon::parse($this->modalDate)->isoFormat('dddd, D. MMMM') . " eingeplant!");
    }

    public function assignCustomMeal(): void
    {
        $title = trim($this->customTitle);
        if ($title === '') {
            return;
        }

        MealPlan::create([
            'id' => (string) Str::uuid(),
            'user_id' => Auth::id(),
            'recipe_id' => null,
            'custom_title' => $title,
            'date' => $this->modalDate,
            'meal_type' => $this->modalMealType,
            'portions' => $this->modalPortions ?: 4,
        ]);

        $this->showRecipeModal = false;
        session()->flash('success', "„{$title}“ für den " . Carbon::parse($this->modalDate)->isoFormat('dddd, D. MMMM') . " eingeplant!");
    }

    public function removeMeal(string $mealPlanId): void
    {
        MealPlan::where('user_id', Auth::id())->where('id', $mealPlanId)->delete();
    }

    public function sendWeekToShoppingList(ShoppingListService $shoppingListService): void
    {
        $start = Carbon::parse($this->currentWeekStart)->startOfWeek();
        $end = Carbon::parse($this->currentWeekStart)->endOfWeek();

        $mealPlans = MealPlan::with('recipe.ingredients')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        $recipesAdded = $shoppingListService->addMealPlansIngredients(Auth::user(), $mealPlans);

        if ($recipesAdded > 0) {
            session()->flash('success', "Zutaten aus {$recipesAdded} geplanten Gerichten wurden auf die Einkaufsliste gesetzt!");
        } else {
            session()->flash('info', 'Keine Rezepte mit Zutaten in dieser Woche gefunden.');
        }
    }

    public function render()
    {
        $userId = Auth::id();
        $start = Carbon::parse($this->currentWeekStart)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        // Alle Wochentage der Woche vorbereiten (Montag bis Sonntag)
        $days = [];
        $current = $start->copy();
        while ($current->lte($end)) {
            $days[] = [
                'date_string' => $current->format('Y-m-d'),
                'day_name' => $current->isoFormat('dddd'),
                'short_date' => $current->format('d.m.'),
                'is_today' => $current->isToday(),
            ];
            $current->addDay();
        }

        // Alle geplanten Menüs dieser Woche laden
        $mealPlans = MealPlan::with(['recipe.ingredients'])
            ->where('user_id', $userId)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        // Gruppieren nach Datum und meal_type
        $groupedPlans = [];
        foreach ($mealPlans as $plan) {
            $dateKey = $plan->date->format('Y-m-d');
            $typeKey = $plan->meal_type; // 'lunch' oder 'dinner'
            $groupedPlans[$dateKey][$typeKey][] = $plan;
        }

        // Rezepte für Modal-Suche
        $searchRecipes = [];
        if ($this->showRecipeModal) {
            $recipeQuery = Recipe::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)->orWhere('is_shared', true);
            });

            if (!empty(trim($this->recipeSearch))) {
                $term = trim($this->recipeSearch);
                $recipeQuery->where('title', 'like', "%{$term}%");
            }

            $searchRecipes = $recipeQuery->orderBy('title')->take(8)->get();
        }

        return view('livewire.meal-planner', [
            'days' => $days,
            'groupedPlans' => $groupedPlans,
            'searchRecipes' => $searchRecipes,
            'weekLabel' => $start->format('d.m.') . ' – ' . $end->format('d.m.Y'),
            'totalPlannedMeals' => $mealPlans->count(),
        ]);
    }
}
