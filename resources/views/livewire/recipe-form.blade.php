<div class="max-w-4xl mx-auto space-y-6">
    <!-- Kopfzeile -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900">
                {{ $isEditing ? 'Rezept bearbeiten' : 'Neues Rezept erstellen' }}
            </h1>
            <p class="text-sm text-stone-500">
                Erfassen Sie alle Zutaten, Zubereitungsschritte und Details.
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if (!$isEditing)
                <a href="{{ route('recipes.scan') }}" 
                   class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-white bg-[#8ea92c] hover:bg-[#7e9626] rounded-xl shadow-sm transition">
                    <span class="material-symbols-outlined text-base mr-1.5">document_scanner</span>
                    <span>Rezept scannen</span>
                </a>
            @endif
            <a href="{{ $isEditing ? route('recipes.show', $recipeId) : route('recipes.index') }}" 
               class="px-4 py-2 text-sm text-stone-600 hover:text-stone-900 border border-stone-300 rounded-xl hover:bg-stone-50 transition">
                Abbrechen
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <strong class="font-semibold block mb-1">Bitte korrigieren Sie die markierten Felder:</strong>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <!-- 1. Grunddaten & Bild -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2 flex items-center space-x-2">
                <span class="material-symbols-outlined text-brand-600">info</span>
                <span>Grunddaten</span>
            </h2>

            <!-- Titel, Freigabe & Favorit -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
                <div class="sm:col-span-6">
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Rezepttitel *</label>
                    <input wire:model="title" type="text" placeholder="z. B. Spinat-Nudelpfanne" required
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div class="sm:col-span-3 pt-4 sm:pt-0">
                    <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-stone-50 border border-stone-200" title="Wenn aktiviert, erscheint das Rezept in der Gesamtansicht und bei Familie/Geteilt">
                        <input wire:model="is_shared" type="checkbox" class="w-4 h-4 text-[#8ea92c] rounded border-stone-300 focus:ring-[#8ea92c]">
                        <span class="text-xs font-semibold text-stone-700 flex items-center space-x-1">
                            <span class="material-symbols-outlined text-[#8ea92c] text-base">share</span>
                            <span>Freigegeben</span>
                        </span>
                    </label>
                </div>
                <div class="sm:col-span-3 pt-4 sm:pt-0">
                    <label class="flex items-center space-x-2 cursor-pointer p-2 rounded-lg hover:bg-stone-50 border border-stone-200">
                        <input wire:model="is_favorite" type="checkbox" class="w-4 h-4 text-brand-600 rounded border-stone-300 focus:ring-brand-500">
                        <span class="text-xs font-semibold text-stone-700 flex items-center space-x-1">
                            <span class="material-symbols-outlined text-amber-500 text-base">star</span>
                            <span>Favorit</span>
                        </span>
                    </label>
                </div>
            </div>

            <!-- Kurzbeschreibung -->
            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Kurzbeschreibung</label>
                <textarea wire:model="description" rows="2" placeholder="Kurze Beschreibung des Gerichts..."
                          class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>

            <!-- Zeiten & Portionen -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Portionen *</label>
                    <input wire:model="portions" type="number" min="1" max="100" required
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Vorbereitung (Min.)</label>
                    <input wire:model="prep_time_minutes" type="number" min="0" placeholder="15"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Kochen / Backen (Min.)</label>
                    <input wire:model="cook_time_minutes" type="number" min="0" placeholder="20"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Ruhezeit (Min.)</label>
                    <input wire:model="rest_time_minutes" type="number" min="0" placeholder="0"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                </div>
            </div>

            <!-- Bild-Upload -->
            <div class="pt-2">
                <label class="block text-xs font-semibold text-stone-700 mb-1">Hauptbild</label>
                <div class="flex items-center space-x-4">
                    @if ($newImage)
                        <div class="relative w-28 h-20 rounded-lg overflow-hidden border border-stone-300 shadow-sm">
                            <img src="{{ $newImage->temporaryUrl() }}" class="w-full h-full object-cover">
                            <button type="button" wire:click="$set('newImage', null)" 
                                    class="absolute top-1 right-1 bg-black/70 hover:bg-black text-white p-0.5 rounded-full">
                                <span class="material-symbols-outlined text-xs">close</span>
                            </button>
                        </div>
                    @elseif ($existingImagePath && !$removeExistingImage)
                        <div class="relative w-28 h-20 rounded-lg overflow-hidden border border-stone-300 shadow-sm">
                            <img src="{{ route('recipes.image', $recipeId) }}" class="w-full h-full object-cover">
                            <button type="button" wire:click="markRemoveExistingImage" 
                                    class="absolute top-1 right-1 bg-rose-600 hover:bg-rose-700 text-white p-0.5 rounded-full" title="Bild entfernen">
                                <span class="material-symbols-outlined text-xs">delete</span>
                            </button>
                        </div>
                    @endif

                    <div class="flex-1">
                        <input wire:model="newImage" type="file" accept="image/jpeg,image/png,image/webp" 
                               class="text-xs text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
                        <p class="text-[11px] text-stone-400 mt-1">JPEG, PNG oder WebP, max. 10 MB.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Kategorien -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-stone-100 pb-3 gap-2">
                <div class="flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#8ea92c]">label</span>
                    <h2 class="text-base font-bold text-stone-900">Kategorien</h2>
                    <span class="text-xs text-stone-500 font-medium">({{ count($selectedCategoryIds) }} ausgewählt)</span>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Suchfilter für Kategorien -->
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-2 top-1.5 text-stone-400 text-sm pointer-events-none">search</span>
                        <input wire:model.live.debounce.150ms="categorySearch" type="text" placeholder="Kategorien filtern..." 
                               class="pl-7 pr-2 py-1 text-xs border border-stone-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#8ea92c]">
                    </div>

                    @if (count($selectedCategoryIds) > 0)
                        <button type="button" wire:click="deselectAllCategories" class="text-xs text-stone-400 hover:text-stone-700">
                            Zurücksetzen
                        </button>
                    @endif

                    <!-- Direkter Link zur Kategorientabelle -->
                    <a href="{{ route('categories.index') }}" target="_blank" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 text-xs font-semibold text-[#8ea92c] bg-[#8ea92c]/10 hover:bg-[#8ea92c]/20 rounded-md transition" 
                       title="Öffnet die Kategorientabelle in einem neuen Tab zum Anpassen">
                        <span class="material-symbols-outlined text-sm">tune</span>
                        <span>Kategorientabelle verwalten</span>
                    </a>
                </div>
            </div>

            <!-- Klickbare Kategorie-Chips mit direktem Umschalten -->
            <div class="flex flex-wrap gap-2 pt-1 max-h-56 overflow-y-auto p-1 scrollbar-thin">
                @forelse ($categories as $cat)
                    @php
                        $isSelected = in_array($cat->id, $selectedCategoryIds, true);
                    @endphp
                    <button type="button" 
                            wire:click="toggleCategory('{{ $cat->id }}')" 
                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition cursor-pointer select-none {{ $isSelected ? 'bg-[#8ea92c] border-[#8ea92c] text-white shadow-xs font-semibold' : 'bg-stone-50 border-stone-200 text-stone-700 hover:bg-stone-100 hover:border-stone-300' }}">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $isSelected ? '#ffffff' : ($cat->color ?? '#8ea92c') }};"></span>
                        <span>{{ $cat->name }}</span>
                        @if ($isSelected)
                            <span class="material-symbols-outlined text-xs leading-none">check</span>
                        @endif
                    </button>
                @empty
                    <p class="text-xs text-stone-400 italic py-2">
                        Keine Kategorien für "{{ $categorySearch }}" gefunden.
                    </p>
                @endforelse
            </div>
        </div>

        <!-- 3. Schlagwörter (Tags) -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-stone-100 pb-3 gap-2">
                <div class="flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#8ea92c]">style</span>
                    <h2 class="text-base font-bold text-stone-900">Schlagwörter (Tags)</h2>
                    <span class="text-xs text-stone-500 font-medium">({{ count($selectedTagIds) }} ausgewählt)</span>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Schnelles Anlegen neuer Schlagwörter direkt im Formular -->
                    <div class="flex items-center space-x-1">
                        <input wire:model="newTagName" 
                               wire:keydown.enter.prevent="createAndAttachTag"
                               type="text" 
                               placeholder="+ Neues Schlagwort..." 
                               class="px-2.5 py-1 text-xs border border-stone-300 rounded-md focus:outline-none focus:ring-1 focus:ring-[#8ea92c]">
                        <button type="button" 
                                wire:click="createAndAttachTag" 
                                class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c] hover:text-white text-stone-700 text-xs font-semibold rounded-md transition"
                                title="Schlagwort hinzufügen">
                            +
                        </button>
                    </div>

                    <a href="{{ route('tags.index') }}" target="_blank" 
                       class="inline-flex items-center space-x-1 px-2.5 py-1 text-xs font-semibold text-[#8ea92c] bg-[#8ea92c]/10 hover:bg-[#8ea92c]/20 rounded-md transition" 
                       title="Öffnet die Schlagwörter-Verwaltung in einem neuen Tab">
                        <span class="material-symbols-outlined text-sm">tune</span>
                        <span>Verwalten</span>
                    </a>
                </div>
            </div>

            <!-- Klickbare Schlagwort-Chips -->
            <div class="flex flex-wrap gap-2 pt-1 max-h-48 overflow-y-auto p-1 scrollbar-thin">
                @forelse ($tags as $tag)
                    @php
                        $isSelected = in_array($tag->id, $selectedTagIds, true);
                    @endphp
                    <button type="button" 
                            wire:click="toggleTag('{{ $tag->id }}')" 
                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-medium border transition cursor-pointer select-none {{ $isSelected ? 'bg-[#8ea92c] border-[#8ea92c] text-white shadow-xs font-semibold' : 'bg-stone-50 border-stone-200 text-stone-700 hover:bg-stone-100 hover:border-stone-300' }}">
                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $isSelected ? '#ffffff' : ($tag->color ?? '#8ea92c') }};"></span>
                        <span>#{{ $tag->name }}</span>
                        @if ($isSelected)
                            <span class="material-symbols-outlined text-xs leading-none">check</span>
                        @endif
                    </button>
                @empty
                    <p class="text-xs text-stone-400 italic py-2">
                        Noch keine Schlagwörter vorhanden. Tippen Sie oben z. B. "Schnell" ein und drücken Sie Enter.
                    </p>
                @endforelse
            </div>
        </div>

        <!-- 3. Zutatenliste -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                <h2 class="text-base font-bold text-stone-900 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-brand-600">kitchen</span>
                    <span>Zutaten</span>
                </h2>
                <button type="button" wire:click="addIngredient" class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>Zeile hinzufügen</span>
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($ingredients as $index => $item)
                    <div class="grid grid-cols-12 gap-2 items-center bg-stone-50 p-2.5 rounded-lg border border-stone-200" wire:key="ingredient-{{ $index }}">
                        <!-- Menge numerisch -->
                        <div class="col-span-3 sm:col-span-2">
                            <input wire:model="ingredients.{{ $index }}.amount_numeric" type="number" step="any" placeholder="Menge"
                                   class="w-full px-2 py-1.5 border border-stone-300 rounded text-xs bg-white focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <!-- Einheit -->
                        <div class="col-span-3 sm:col-span-2">
                            <input wire:model="ingredients.{{ $index }}.unit" type="text" placeholder="Einheit (g, EL)"
                                   class="w-full px-2 py-1.5 border border-stone-300 rounded text-xs bg-white focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <!-- Zutat Name -->
                        <div class="col-span-5 sm:col-span-4">
                            <input wire:model="ingredients.{{ $index }}.name" type="text" placeholder="Zutat *"
                                   class="w-full px-2 py-1.5 border border-stone-300 rounded text-xs bg-white font-medium focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <!-- Textzusatz (z. B. "nach Geschmack") -->
                        <div class="col-span-11 sm:col-span-3">
                            <input wire:model="ingredients.{{ $index }}.amount_text" type="text" placeholder="Notiz (z. B. frisch gehackt)"
                                   class="w-full px-2 py-1.5 border border-stone-300 rounded text-xs bg-white focus:ring-1 focus:ring-brand-500 focus:outline-none">
                        </div>

                        <!-- Zeile löschen -->
                        <div class="col-span-1 flex justify-end">
                            <button type="button" wire:click="removeIngredient({{ $index }})" 
                                    class="text-stone-400 hover:text-rose-600 p-1 rounded transition" title="Zutat entfernen">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="addIngredient" class="w-full py-2 border border-dashed border-stone-300 hover:border-brand-500 text-stone-600 hover:text-brand-600 text-xs font-semibold rounded-lg flex items-center justify-center space-x-1 transition">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Weitere Zutat hinzufügen</span>
            </button>
        </div>

        <!-- 4. Zubereitungsschritte -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                <h2 class="text-base font-bold text-stone-900 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-brand-600">format_list_numbered</span>
                    <span>Zubereitungsschritte</span>
                </h2>
                <button type="button" wire:click="addStep" class="text-xs font-semibold text-brand-600 hover:text-brand-700 flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>Schritt hinzufügen</span>
                </button>
            </div>

            <div class="space-y-3">
                @foreach ($steps as $index => $step)
                    <div class="flex items-start space-x-3 bg-stone-50 p-3 rounded-lg border border-stone-200" wire:key="step-{{ $index }}">
                        <span class="w-7 h-7 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0 mt-1 shadow-sm">
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1">
                            <textarea wire:model="steps.{{ $index }}.instruction" rows="2" placeholder="Beschreiben Sie diesen Zubereitungsschritt..."
                                      class="w-full px-3 py-1.5 border border-stone-300 rounded text-xs bg-white focus:ring-1 focus:ring-brand-500 focus:outline-none"></textarea>
                        </div>
                        <button type="button" wire:click="removeStep({{ $index }})" 
                                class="text-stone-400 hover:text-rose-600 p-1 rounded transition mt-1" title="Schritt entfernen">
                            <span class="material-symbols-outlined text-lg">delete</span>
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" wire:click="addStep" class="w-full py-2 border border-dashed border-stone-300 hover:border-brand-500 text-stone-600 hover:text-brand-600 text-xs font-semibold rounded-lg flex items-center justify-center space-x-1 transition">
                <span class="material-symbols-outlined text-base">add</span>
                <span>Weiteren Schritt hinzufügen</span>
            </button>
        </div>

        <!-- 5. Nährwerte (pro Portion) -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                <h2 class="text-base font-bold text-stone-900 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#8ea92c]">nutrition</span>
                    <span>Nährwerte (pro Portion)</span>
                </h2>
                <span class="text-xs text-stone-400 font-normal">Optional</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Energie (kcal)</label>
                    <input wire:model="calories" type="number" min="0" placeholder="z. B. 580"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Eiweiß / Protein (g)</label>
                    <input wire:model="protein" type="number" step="0.1" min="0" placeholder="z. B. 33"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Fett (g)</label>
                    <input wire:model="fat" type="number" step="0.1" min="0" placeholder="z. B. 24"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Kohlenhydrate (g)</label>
                    <input wire:model="carbohydrates" type="number" step="0.1" min="0" placeholder="z. B. 40"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Zusätzliche Nährwert-Angaben</label>
                <textarea wire:model="nutrition_raw" rows="2" placeholder="z. B. Ballaststoffe: 5g, Zucker: 3g..."
                          class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none"></textarea>
            </div>
        </div>

        <!-- 6. Notizen & Quelle -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
            <h2 class="text-base font-bold text-stone-900 border-b border-stone-100 pb-2 flex items-center space-x-2">
                <span class="material-symbols-outlined text-brand-600">sticky_note_2</span>
                <span>Zusatzinformationen</span>
            </h2>

            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Persönliche Notizen</label>
                <textarea wire:model="notes" rows="3" placeholder="Tipps, Abwandlungen, passende Beilagen..."
                          class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-stone-700 mb-1">Ursprüngliche Quellen-URL</label>
                <input wire:model="source_url" type="url" placeholder="https://..."
                       class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
            </div>
        </div>

        <!-- Aktionsleiste Speichern -->
        <div class="flex items-center justify-end space-x-4 pt-2">
            <a href="{{ $isEditing ? route('recipes.show', $recipeId) : route('recipes.index') }}" 
               class="px-5 py-2.5 text-sm text-stone-700 hover:text-stone-900 border border-stone-300 rounded-lg hover:bg-stone-50 transition">
                Abbrechen
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-lg shadow-sm transition flex items-center space-x-2">
                <span class="material-symbols-outlined text-base">save</span>
                <span>{{ $isEditing ? 'Änderungen speichern' : 'Rezept erstellen' }}</span>
            </button>
        </div>
    </form>
</div>

