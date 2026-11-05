<?php

namespace App\Livewire;

use App\Models\ShoppingListItem;
use App\Services\ShoppingListService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ShoppingList extends Component
{
    public string $newItemName = '';
    public string $newItemAmount = '';
    public string $newItemUnit = '';

    public string $selectedCategoryFilter = '';
    public string $filter = 'open'; // 'open', 'all', 'completed'

    public function addItem(ShoppingListService $service): void
    {
        $this->validate([
            'newItemName' => ['required', 'string', 'max:255'],
            'newItemAmount' => ['nullable', 'numeric', 'min:0'],
            'newItemUnit' => ['nullable', 'string', 'max:50'],
        ], [
            'newItemName.required' => 'Bitte geben Sie einen Artikelnamen ein.',
        ]);

        $service->addIngredient(Auth::user(), [
            'name' => $this->newItemName,
            'amount_numeric' => !empty($this->newItemAmount) ? (float) $this->newItemAmount : null,
            'unit' => !empty($this->newItemUnit) ? $this->newItemUnit : null,
        ]);

        $this->newItemName = '';
        $this->newItemAmount = '';
        $this->newItemUnit = '';
    }

    public function toggleItem(string $id, ShoppingListService $service): void
    {
        $service->toggleItem(Auth::user(), $id);
    }

    public function deleteItem(string $id): void
    {
        ShoppingListItem::where('user_id', Auth::id())->where('id', $id)->delete();
    }

    public function clearCompleted(ShoppingListService $service): void
    {
        $count = $service->clearCompleted(Auth::user());
        if ($count > 0) {
            session()->flash('success', "{$count} erledigte Artikel wurden entfernt.");
        }
    }

    public function clearAll(ShoppingListService $service): void
    {
        $service->clearAll(Auth::user());
        session()->flash('success', 'Einkaufsliste wurde geleert.');
    }

    public function render()
    {
        $userId = Auth::id();

        $query = ShoppingListItem::with('recipe')
            ->where('user_id', $userId);

        if ($this->filter === 'open') {
            $query->where('is_completed', false);
        } elseif ($this->filter === 'completed') {
            $query->where('is_completed', true);
        }

        if (!empty($this->selectedCategoryFilter)) {
            $query->where('category', $this->selectedCategoryFilter);
        }

        $items = $query->orderBy('is_completed')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        // Nach Kategorien gruppieren für übersichtliches Einkaufen
        $groupedItems = $items->groupBy(function ($item) {
            return $item->category ?: 'Sonstiges';
        });

        $totalCount = ShoppingListItem::where('user_id', $userId)->count();
        $openCount = ShoppingListItem::where('user_id', $userId)->where('is_completed', false)->count();
        $completedCount = $totalCount - $openCount;

        // Alle vorhandenen Kategorien für Filter-Chips
        $availableCategories = ShoppingListItem::where('user_id', $userId)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return view('livewire.shopping-list', [
            'groupedItems' => $groupedItems,
            'totalCount' => $totalCount,
            'openCount' => $openCount,
            'completedCount' => $completedCount,
            'availableCategories' => $availableCategories,
        ]);
    }
}

