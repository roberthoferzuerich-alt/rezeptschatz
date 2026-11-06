<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TagManager extends Component
{
    public string $name = '';
    public string $color = '#8ea92c';
    public string $icon = 'label';
    public string $search = '';

    public ?string $editingTagId = null;
    public string $editName = '';
    public string $editColor = '#8ea92c';
    public string $editIcon = 'label';

    public array $popularSuggestions = [
        'Schnell (< 30 Min.)' => '#10b981',
        'Gäste & Fest' => '#f59e0b',
        'Einfach & Alltag' => '#8ea92c',
        'Klassiker' => '#6366f1',
        'Sommer' => '#ec4899',
        'Winter & Deftig' => '#8b5cf6',
        'Low-Carb' => '#06b6d4',
        'Vegetarisch' => '#22c55e',
        'Resteverwertung' => '#f97316',
        'Apéro & Fingerfood' => '#eab308',
        'Backofen' => '#ef4444',
        'Schweizer Küche' => '#dc2626',
    ];

    public function create(TagService $tagService): void
    {
        $this->authorize('create', Tag::class);

        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['required', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $tagService->createTag(Auth::user(), [
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
        ]);

        $this->reset(['name']);
        $this->color = '#8ea92c';
        session()->flash('success', 'Schlagwort erfolgreich angelegt.');
    }

    public function addSuggestion(string $name, string $color, TagService $tagService): void
    {
        $this->authorize('create', Tag::class);

        $tagService->findOrCreateTag(Auth::user(), $name, $color);
        session()->flash('success', "Schlagwort '{$name}' wurde hinzugefügt.");
    }

    public function startEdit(string $id): void
    {
        $tag = Tag::findOrFail($id);
        $this->authorize('update', $tag);

        $this->editingTagId = $id;
        $this->editName = $tag->name;
        $this->editColor = $tag->color ?? '#8ea92c';
        $this->editIcon = $tag->icon ?? 'label';
    }

    public function cancelEdit(): void
    {
        $this->editingTagId = null;
    }

    public function update(TagService $tagService): void
    {
        $tag = Tag::findOrFail($this->editingTagId);
        $this->authorize('update', $tag);

        $this->validate([
            'editName' => ['required', 'string', 'max:100'],
            'editColor' => ['required', 'string', 'max:20'],
            'editIcon' => ['nullable', 'string', 'max:50'],
        ]);

        $tagService->updateTag($tag, [
            'name' => $this->editName,
            'color' => $this->editColor,
            'icon' => $this->editIcon,
        ]);

        $this->editingTagId = null;
        session()->flash('success', 'Schlagwort erfolgreich aktualisiert.');
    }

    public function delete(string $id, TagService $tagService): void
    {
        $tag = Tag::findOrFail($id);
        $this->authorize('delete', $tag);

        $tagService->deleteTag($tag);
        session()->flash('success', 'Schlagwort gelöscht.');
    }

    public function render()
    {
        $user = Auth::user();

        $query = Tag::withCount(['recipes' => function ($rq) use ($user) {
            $rq->where('user_id', $user->id)->orWhere('is_shared', true);
        }]);

        if (!empty(trim($this->search))) {
            $query->where('name', 'like', '%' . trim($this->search) . '%');
        }

        $tags = $query->orderBy('name')->get();

        return view('livewire.tag-manager', [
            'tags' => $tags,
            'canManage' => $user->canManageTags(),
        ]);
    }
}

