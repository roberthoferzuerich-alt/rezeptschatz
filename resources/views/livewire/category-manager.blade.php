<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Kategorien verwalten</h1>
            <p class="text-sm text-stone-500">Organisieren Sie Ihre Rezepte in individuellen Themen und Rezeptarten.</p>
        </div>
        <a href="{{ route('recipes.index') }}" class="text-sm text-stone-600 hover:text-brand-600 flex items-center space-x-1">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zur Rezeptübersicht</span>
        </a>
    </div>

    <!-- Neue Kategorie anlegen (nur für Admins) -->
    @if ($canManage)
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6">
            <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2 mb-4 flex items-center space-x-2">
                <span class="material-symbols-outlined text-brand-600">add_circle</span>
                <span>Neue Kategorie hinzufügen</span>
            </h2>

            <form wire:submit="create" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Name der Kategorie *</label>
                    <input wire:model="name" type="text" placeholder="z. B. Desserts & Süsses" required
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                    @error('name') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Farbe</label>
                    <div class="flex items-center space-x-2">
                        <input wire:model="color" type="color" class="h-9 w-12 border border-stone-300 rounded cursor-pointer p-0.5">
                        <span class="text-xs font-mono text-stone-500">{{ $color }}</span>
                    </div>
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" class="w-full py-2 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg shadow-sm transition flex items-center justify-center space-x-1">
                        <span class="material-symbols-outlined text-base">add</span>
                        <span>Anlegen</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Vorhandene Kategorien -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="p-6 border-b border-stone-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-brand-600">label</span>
                <span>Verfügbare Kategorien ({{ $categories->count() }})</span>
            </h2>
            @if (!$canManage)
                <span class="text-xs text-stone-400 italic">Verwaltung durch Administrator</span>
            @endif
        </div>

        <ul class="divide-y divide-stone-100">
            @forelse ($categories as $cat)
                <li class="p-4 sm:px-6 flex items-center justify-between hover:bg-stone-50 transition" wire:key="category-{{ $cat->id }}">
                    @if ($editingCategoryId === $cat->id)
                        <!-- Bearbeitungs-Modus -->
                        <form wire:submit="update" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <div class="sm:col-span-6">
                                <input wire:model="editName" type="text" required
                                       class="w-full px-2 py-1 border border-stone-300 rounded text-sm focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            </div>
                            <div class="sm:col-span-3 flex items-center space-x-2">
                                <input wire:model="editColor" type="color" class="h-7 w-10 border border-stone-300 rounded cursor-pointer">
                            </div>
                            <div class="sm:col-span-3 flex items-center space-x-2 justify-end">
                                <button type="submit" class="px-2.5 py-1 bg-brand-600 hover:bg-brand-700 text-white rounded text-xs font-semibold">
                                    Speichern
                                </button>
                                <button type="button" wire:click="cancelEdit" class="px-2.5 py-1 border border-stone-300 text-stone-600 hover:bg-stone-100 rounded text-xs">
                                    Abbrechen
                                </button>
                            </div>
                        </form>
                    @else
                        <!-- Ansichts-Modus -->
                        <div class="flex items-center space-x-3">
                            <span class="w-3.5 h-3.5 rounded-full shadow-sm" style="background-color: {{ $cat->color ?? '#7a9c33' }};"></span>
                            <span class="font-medium text-stone-900 text-sm">{{ $cat->name }}</span>
                            <span class="text-xs text-stone-400 bg-stone-100 px-2 py-0.5 rounded-full">
                                {{ $cat->recipes_count }} {{ $cat->recipes_count === 1 ? 'Rezept' : 'Rezepte' }}
                            </span>
                        </div>

                        @if ($canManage)
                            <div class="flex items-center space-x-2">
                                <button wire:click="startEdit('{{ $cat->id }}')" class="p-1 text-stone-400 hover:text-stone-700 rounded transition" title="Bearbeiten">
                                    <span class="material-symbols-outlined text-lg">edit</span>
                                </button>
                                <button wire:click="delete('{{ $cat->id }}')" wire:confirm="Möchten Sie diese Kategorie wirklich löschen?" 
                                        class="p-1 text-stone-400 hover:text-rose-600 rounded transition" title="Löschen">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        @endif
                    @endif
                </li>
            @empty
                <li class="p-8 text-center text-sm text-stone-500">
                    Noch keine Kategorien vorhanden.
                </li>
            @endforelse
        </ul>
    </div>
</div>
