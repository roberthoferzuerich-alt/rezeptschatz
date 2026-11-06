<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">Schlagwörter verwalten</h1>
            <p class="text-sm text-stone-500">Versehen Sie Rezepte mit flexiblen Tags wie Anlass, Jahreszeit, Zubereitungsart oder Ernährungsweise.</p>
        </div>
        <a href="{{ route('recipes.index') }}" class="text-sm text-stone-600 hover:text-[#8ea92c] flex items-center space-x-1 font-medium transition">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zur Rezeptübersicht</span>
        </a>
    </div>

    @if (session()->has('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl flex items-center space-x-2">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Neues Schlagwort anlegen (nur für Admins) -->
    @if ($canManage)
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#8ea92c]">label</span>
                <span>Neues Schlagwort hinzufügen</span>
            </h2>

            <form wire:submit="create" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Name des Schlagworts *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-stone-400 font-bold">#</span>
                        <input wire:model="name" type="text" placeholder="z. B. Schnell, Gäste, Sommer..." required
                               class="w-full pl-7 pr-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                    </div>
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
                    <button type="submit" class="w-full py-2 px-4 bg-[#8ea92c] hover:bg-[#7d9626] text-white font-semibold text-sm rounded-lg shadow-sm transition flex items-center justify-center space-x-1">
                        <span class="material-symbols-outlined text-base">add</span>
                        <span>Anlegen</span>
                    </button>
                </div>
            </form>

            <!-- Schnellauswahl / Vorschläge -->
            <div class="pt-2 border-t border-stone-100">
                <div class="text-xs font-semibold text-stone-500 mb-2">Häufige Schlagwörter mit 1 Klick hinzufügen:</div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($popularSuggestions as $suggName => $suggColor)
                        @php
                            $alreadyExists = $tags->contains('name', $suggName);
                        @endphp
                        @if (!$alreadyExists)
                            <button type="button" 
                                    wire:click="addSuggestion('{{ $suggName }}', '{{ $suggColor }}')"
                                    class="inline-flex items-center space-x-1 text-xs px-2.5 py-1 rounded-full border border-stone-200 bg-stone-50 hover:bg-white hover:border-[#8ea92c] text-stone-700 transition">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $suggColor }}"></span>
                                <span>+ #{{ $suggName }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Vorhandene Schlagwörter -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <div class="p-6 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-stone-900 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#8ea92c]">style</span>
                    <span>Vorhandene Schlagwörter ({{ $tags->count() }})</span>
                </h2>
                <p class="text-xs text-stone-500 mt-0.5">Tippen Sie auf ein Schlagwort, um alle verknüpften Rezepte anzuzeigen.</p>
            </div>

            <!-- Suchfeld -->
            <div class="w-full sm:w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Schlagwort filtern..."
                       class="w-full px-3 py-1.5 border border-stone-300 rounded-lg text-xs focus:ring-1 focus:ring-[#8ea92c] focus:outline-none">
            </div>
        </div>

        @if ($tags->isEmpty())
            <div class="p-12 text-center text-stone-500 space-y-2">
                <span class="material-symbols-outlined text-4xl text-stone-300">sell</span>
                <p class="text-sm">Noch keine Schlagwörter vorhanden.</p>
                @if ($canManage)
                    <p class="text-xs text-stone-400">Nutzen Sie das Formular oben oder wählen Sie aus den beliebten Vorschlägen.</p>
                @endif
            </div>
        @else
            <ul class="divide-y divide-stone-100">
                @foreach ($tags as $tag)
                    <li class="p-4 sm:px-6 flex items-center justify-between hover:bg-stone-50 transition" wire:key="tag-{{ $tag->id }}">
                        @if ($editingTagId === $tag->id)
                            <!-- Bearbeitungs-Modus -->
                            <form wire:submit="update" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                                <div class="sm:col-span-6">
                                    <input wire:model="editName" type="text" required
                                           class="w-full px-2 py-1 border border-stone-300 rounded text-sm focus:ring-1 focus:ring-[#8ea92c] focus:outline-none">
                                    @error('editName') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>
                                <div class="sm:col-span-3 flex items-center space-x-2">
                                    <input wire:model="editColor" type="color" class="h-7 w-10 border border-stone-300 rounded cursor-pointer">
                                    <span class="text-xs font-mono text-stone-500">{{ $editColor }}</span>
                                </div>
                                <div class="sm:col-span-3 flex items-center space-x-2 justify-end">
                                    <button type="submit" class="px-2.5 py-1 bg-[#8ea92c] hover:bg-[#7d9626] text-white rounded text-xs font-semibold">
                                        Speichern
                                    </button>
                                    <button type="button" wire:click="cancelEdit" class="px-2 py-1 text-stone-500 hover:text-stone-700 text-xs">
                                        Abbrechen
                                    </button>
                                </div>
                            </form>
                        @else
                            <!-- Anzeige-Modus -->
                            <div class="flex items-center space-x-3">
                                <span class="w-3.5 h-3.5 rounded-full shrink-0 shadow-sm" style="background-color: {{ $tag->color ?? '#8ea92c' }}"></span>
                                <a href="{{ route('recipes.index', ['tag' => $tag->id]) }}" 
                                   class="font-bold text-sm text-stone-900 hover:text-[#8ea92c] flex items-center space-x-1.5 transition">
                                    <span>#{{ $tag->name }}</span>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 hover:bg-[#8ea92c]/10 hover:text-[#8ea92c]">
                                        {{ $tag->recipes_count }} {{ $tag->recipes_count === 1 ? 'Rezept' : 'Rezepte' }}
                                    </span>
                                </a>
                            </div>

                            @if ($canManage)
                                <div class="flex items-center space-x-1">
                                    <button type="button" wire:click="startEdit('{{ $tag->id }}')" 
                                            class="p-1.5 text-stone-400 hover:text-[#8ea92c] rounded-lg transition" 
                                            title="Bearbeiten">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </button>
                                    <button type="button" 
                                            wire:click="delete('{{ $tag->id }}')" 
                                            wire:confirm="Möchten Sie das Schlagwort '#{{ $tag->name }}' wirklich löschen? Die Zuordnung zu Rezepten wird dabei entfernt."
                                            class="p-1.5 text-stone-400 hover:text-rose-600 rounded-lg transition" 
                                            title="Löschen">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </button>
                                </div>
                            @endif
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
