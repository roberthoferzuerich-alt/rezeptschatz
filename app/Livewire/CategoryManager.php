<?php

namespace App\Livewire;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CategoryManager extends Component
{
    public string $name = '';
    public string $color = '#7a9c33';
    public string $icon = 'restaurant';

    public ?string $editingCategoryId = null;
    public string $editName = '';
    public string $editColor = '#7a9c33';
    public string $editIcon = 'restaurant';

    public function create(CategoryService $categoryService): void
    {
        $this->authorize('create', Category::class);

        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $categoryService->createCategory(Auth::user(), [
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
        ]);

        $this->reset(['name', 'color', 'icon']);
        $this->color = '#7a9c33';
        session()->flash('success', 'Kategorie erfolgreich angelegt.');
    }

    public function startEdit(string $id): void
    {
        $cat = Category::findOrFail($id);
        $this->authorize('update', $cat);

        $this->editingCategoryId = $id;
        $this->editName = $cat->name;
        $this->editColor = $cat->color ?? '#7a9c33';
        $this->editIcon = $cat->icon ?? 'restaurant';
    }

    public function cancelEdit(): void
    {
        $this->editingCategoryId = null;
    }

    public function update(CategoryService $categoryService): void
    {
        $cat = Category::findOrFail($this->editingCategoryId);
        $this->authorize('update', $cat);

        $this->validate([
            'editName' => ['required', 'string', 'max:100'],
            'editColor' => ['required', 'string', 'max:7'],
            'editIcon' => ['nullable', 'string', 'max:50'],
        ]);

        $categoryService->updateCategory($cat, [
            'name' => $this->editName,
            'color' => $this->editColor,
            'icon' => $this->editIcon,
        ]);

        $this->editingCategoryId = null;
        session()->flash('success', 'Kategorie erfolgreich aktualisiert.');
    }

    public function delete(string $id, CategoryService $categoryService): void
    {
        $cat = Category::findOrFail($id);
        $this->authorize('delete', $cat);

        $categoryService->deleteCategory($cat);
        session()->flash('success', 'Kategorie gelöscht.');
    }

    public function render()
    {
        $user = Auth::user();

        $categories = Category::withCount(['recipes' => function ($rq) use ($user) {
            $rq->where('user_id', $user->id)->orWhere('is_shared', true);
        }])->orderBy('name')->get();

        return view('livewire.category-manager', [
            'categories' => $categories,
            'canManage' => $user->canManageCategories(),
        ]);
    }
}
