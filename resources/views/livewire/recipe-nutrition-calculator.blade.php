<div class="max-w-4xl mx-auto space-y-6">
    <!-- Kopfzeile -->
    <div class="flex items-center justify-between border-b border-stone-200 pb-3">
        <div class="flex items-center space-x-2">
            <a href="{{ route('recipes.index') }}" class="text-stone-500 hover:text-stone-800 transition p-1 rounded-md" title="Zurück">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#8ea92c]">nutrition</span>
                <span>Nährwerte ermitteln & Kategorien zuweisen</span>
            </h1>
        </div>
    </div>

    <!-- Fehlermeldung -->
    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start space-x-3">
            <span class="material-symbols-outlined text-red-600 text-xl mt-0.5">error</span>
            <div class="space-y-1">
                <p class="font-bold">Aktion fehlgeschlagen</p>
                <p class="text-xs">{{ $errorMessage }}</p>
            </div>
        </div>
    @endif

    <!-- Erfolgsmeldung -->
    @if ($successMessage)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-start justify-between space-x-3">
            <div class="flex items-start space-x-3">
                <span class="material-symbols-outlined text-emerald-600 text-xl mt-0.5">check_circle</span>
                <div class="space-y-1">
                    <p class="font-bold">Erfolgreich gespeichert!</p>
                    <p class="text-xs text-emerald-800">{{ $successMessage }}</p>
                </div>
            </div>
            @if (!empty($recipeData['saved_recipe_id']))
                <a href="{{ route('recipes.show', $recipeData['saved_recipe_id']) }}" 
                   class="inline-flex items-center space-x-1 px-3 py-1.5 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs font-semibold rounded-lg shadow-xs transition shrink-0">
                    <span>Rezept ansehen</span>
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            @endif
        </div>
    @endif

    <!-- Eingabeformular -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-5">
        <div>
            <label for="url" class="block text-sm font-semibold text-stone-800 mb-1">
                Rezept-Link oder ID eingeben
            </label>
            <p class="text-xs text-stone-500 mb-3">
                Gib die Web-URL eines eigenen Rezepts (z. B. <code>https://rhz.internet-box.ch:8449/recipes/...</code>), eine Rezept-ID oder einen externen Rezeptlink (Chefkoch, Betty Bossi etc.) ein.
            </p>
            
            <form wire:submit.prevent="generate" class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-lg pointer-events-none">link</span>
                    <input wire:model="url" type="text" id="url" 
                           placeholder="https://rhz.internet-box.ch:8449/recipes/... oder ID" 
                           class="w-full pl-10 pr-4 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c] focus:border-[#8ea92c]">
                </div>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center space-x-2 px-5 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-semibold rounded-lg shadow-xs transition disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove wire:target="generate" class="material-symbols-outlined text-base">auto_awesome</span>
                    <span wire:loading wire:target="generate" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                    <span wire:loading.remove wire:target="generate">Nährwerte berechnen</span>
                    <span wire:loading wire:target="generate">Berechne mit KI...</span>
                </button>
            </form>
        </div>

        <!-- Alternative: Aus bestehenden Rezepten auswählen -->
        <div class="flex items-center space-x-2 pt-2 border-t border-stone-100">
            <span class="text-xs text-stone-500 shrink-0">Oder Rezept aus Sammlung wählen:</span>
            <select wire:model.live="selectedRecipeId" class="text-xs border border-stone-300 rounded-lg px-2.5 py-1.5 bg-stone-50 text-stone-700 focus:outline-none focus:ring-1 focus:ring-[#8ea92c] max-w-sm">
                <option value="">-- Eigenes Rezept auswählen --</option>
                @foreach ($userRecipes as $r)
                    <option value="{{ $r->id }}">{{ $r->title }}</option>
                @endforeach
            </select>
        </div>

        <!-- Option: Automatische Kategorie-Zuweisung -->
        <div class="pt-2">
            <label class="inline-flex items-center space-x-2 cursor-pointer select-none">
                <input type="checkbox" wire:model="autoAssignCategories" class="rounded text-[#8ea92c] focus:ring-[#8ea92c] border-stone-300">
                <span class="text-xs text-stone-700">
                    Hauptzutaten-Kategorien (Fleisch, Geflügel, Gemüse etc.) automatisch via 
                    <span class="font-mono bg-stone-100 px-1 py-0.5 rounded text-[11px] text-stone-600">recipes:assign-ingredient-categories</span> zuweisen
                </span>
            </label>
        </div>
    </div>

    <!-- ERGEBNIS & FORMULAR ZUM ÜBERSCHREIBEN / ABBRECHEN -->
    @if ($recipeData)
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-6">
            <!-- Header des Rezepts -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-stone-200 gap-3">
                <div class="flex items-center space-x-3">
                    @if (!empty($recipeData['image_url']))
                        <img src="{{ $recipeData['image_url'] }}" alt="{{ $recipeData['title'] }}" class="w-14 h-14 object-cover rounded-xl shadow-xs">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-[#8ea92c]/10 text-[#8ea92c] flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl">restaurant</span>
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-lg font-bold text-stone-900">{{ $recipeData['title'] }}</h2>
                            <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-200">
                                {{ $recipeData['source'] }}
                            </span>
                        </div>
                        <p class="text-xs text-stone-500 mt-0.5">
                            Basis: {{ count($recipeData['ingredients'] ?? []) }} Zutaten • Berechnet pro Portion
                        </p>
                    </div>
                </div>
            </div>

            <!-- Interaktives Formular: Nährwerte pro Portion überschreiben -->
            <div>
                <h3 class="text-sm font-bold text-stone-800 mb-2 flex items-center space-x-1.5">
                    <span class="material-symbols-outlined text-[#8ea92c] text-base">edit_note</span>
                    <span>Nährwerte pro Portion anpassen / überschreiben:</span>
                </h3>
                <p class="text-xs text-stone-500 mb-4">
                    Du kannst jeden berechneten Wert hier manuell korrigieren, bevor du ihn in die Datenbank schreibst.
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <!-- Kalorien -->
                    <div class="bg-amber-50/50 border border-amber-200/80 rounded-xl p-3">
                        <label for="calories" class="block text-xs font-semibold text-amber-900 mb-1">
                            Kalorien
                        </label>
                        <div class="flex items-center space-x-1">
                            <input wire:model.live="calories" type="number" min="0" id="calories" 
                                   class="w-full bg-white border border-amber-300 rounded-lg px-2.5 py-1.5 text-sm font-bold text-stone-800 focus:outline-none focus:ring-1 focus:ring-amber-500">
                            <span class="text-xs text-amber-700 font-medium">kcal</span>
                        </div>
                    </div>

                    <!-- Kohlenhydrate -->
                    <div class="bg-blue-50/50 border border-blue-200/80 rounded-xl p-3">
                        <label for="carbohydrates" class="block text-xs font-semibold text-blue-900 mb-1">
                            Kohlenhydrate
                        </label>
                        <div class="flex items-center space-x-1">
                            <input wire:model.live="carbohydrates" type="number" step="0.1" min="0" id="carbohydrates" 
                                   class="w-full bg-white border border-blue-300 rounded-lg px-2.5 py-1.5 text-sm font-bold text-stone-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <span class="text-xs text-blue-700 font-medium">g</span>
                        </div>
                    </div>

                    <!-- Fett -->
                    <div class="bg-red-50/50 border border-red-200/80 rounded-xl p-3">
                        <label for="fat" class="block text-xs font-semibold text-red-900 mb-1">
                            Fett
                        </label>
                        <div class="flex items-center space-x-1">
                            <input wire:model.live="fat" type="number" step="0.1" min="0" id="fat" 
                                   class="w-full bg-white border border-red-300 rounded-lg px-2.5 py-1.5 text-sm font-bold text-stone-800 focus:outline-none focus:ring-1 focus:ring-red-500">
                            <span class="text-xs text-red-700 font-medium">g</span>
                        </div>
                    </div>

                    <!-- Eiweiss -->
                    <div class="bg-emerald-50/50 border border-emerald-200/80 rounded-xl p-3">
                        <label for="protein" class="block text-xs font-semibold text-emerald-900 mb-1">
                            Eiweiss / Protein
                        </label>
                        <div class="flex items-center space-x-1">
                            <input wire:model.live="protein" type="number" step="0.1" min="0" id="protein" 
                                   class="w-full bg-white border border-emerald-300 rounded-lg px-2.5 py-1.5 text-sm font-bold text-stone-800 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <span class="text-xs text-emerald-700 font-medium">g</span>
                        </div>
                    </div>

                    <!-- Portionen -->
                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 col-span-2 sm:col-span-1">
                        <label for="portions" class="block text-xs font-semibold text-stone-700 mb-1">
                            Portionen
                        </label>
                        <div class="flex items-center space-x-1">
                            <input wire:model.live="portions" type="number" min="1" id="portions" 
                                   class="w-full bg-white border border-stone-300 rounded-lg px-2.5 py-1.5 text-sm font-bold text-stone-800 focus:outline-none focus:ring-1 focus:ring-[#8ea92c]">
                            <span class="text-xs text-stone-500 font-medium">Port.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamische Gesamttabelle -->
            @php
                $portCount = max(1, (int) ($portions ?? 1));
                $calNum = (int) ($calories ?? 0);
                $carbNum = (float) ($carbohydrates ?? 0);
                $fatNum = (float) ($fat ?? 0);
                $protNum = (float) ($protein ?? 0);
            @endphp
            <div class="border border-stone-200 rounded-xl overflow-hidden text-xs">
                <table class="w-full text-left">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-700 font-semibold">
                        <tr>
                            <th class="py-2.5 px-4">Nährwert</th>
                            <th class="py-2.5 px-4">Wert pro Portion</th>
                            <th class="py-2.5 px-4">Gesamt ({{ $portCount }} Portionen)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-800">
                        <tr>
                            <td class="py-2 px-4 font-medium flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                                <span>Kalorien (Energie)</span>
                            </td>
                            <td class="py-2 px-4 font-bold">{{ $calNum }} kcal</td>
                            <td class="py-2 px-4 font-bold text-stone-600">{{ number_format($calNum * $portCount, 0, '.', "'") }} kcal</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 font-medium flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>
                                <span>Kohlenhydrate</span>
                            </td>
                            <td class="py-2 px-4 font-bold">{{ number_format($carbNum, 1, '.', '') }} g</td>
                            <td class="py-2 px-4 font-bold text-stone-600">{{ number_format($carbNum * $portCount, 1, '.', '') }} g</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 font-medium flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                                <span>Fett</span>
                            </td>
                            <td class="py-2 px-4 font-bold">{{ number_format($fatNum, 1, '.', '') }} g</td>
                            <td class="py-2 px-4 font-bold text-stone-600">{{ number_format($fatNum * $portCount, 1, '.', '') }} g</td>
                        </tr>
                        <tr>
                            <td class="py-2 px-4 font-medium flex items-center space-x-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                                <span>Eiweiss / Protein</span>
                            </td>
                            <td class="py-2 px-4 font-bold">{{ number_format($protNum, 1, '.', '') }} g</td>
                            <td class="py-2 px-4 font-bold text-stone-600">{{ number_format($protNum * $portCount, 1, '.', '') }} g</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Liste der berücksichtigten Zutaten -->
            @if (!empty($recipeData['ingredients']))
                <div class="border-t border-stone-100 pt-3" x-data="{ open: false }">
                    <button type="button" @click="open = !open" 
                            class="text-xs text-stone-500 hover:text-stone-800 transition flex items-center space-x-1.5 focus:outline-none">
                        <span class="material-symbols-outlined text-sm text-[#8ea92c]">format_list_bulleted</span>
                        <span>Berücksichtigte Zutaten anzeigen ({{ count($recipeData['ingredients']) }})</span>
                        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>

                    <div x-show="open" class="mt-2 text-xs text-stone-700 bg-stone-50 p-3 rounded-xl border border-stone-200 grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach ($recipeData['ingredients'] as $ing)
                            <div class="flex items-center space-x-1.5">
                                <span class="text-[#8ea92c]">•</span>
                                <span>{{ $ing }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Fehlermeldung direkt beim Speichern anzeigen -->
            @if ($errorMessage)
                <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center space-x-2">
                    <span class="material-symbols-outlined text-red-600 text-base">error</span>
                    <span>{{ $errorMessage }}</span>
                </div>
            @endif

            <!-- AKTIONEN: COMMIT & ABBRECHEN -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-between pt-4 border-t border-stone-200 gap-3">
                <button type="button" 
                        wire:click="cancel" 
                        class="w-full sm:w-auto px-4 py-2 text-sm font-semibold text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition cursor-pointer">
                    Abbrechen
                </button>

                <div class="flex items-center space-x-2 w-full sm:w-auto">
                    <button type="button" 
                            wire:click="commit" 
                            wire:loading.attr="disabled"
                            wire:target="commit"
                            class="w-full sm:w-auto inline-flex items-center justify-center space-x-2 px-6 py-2.5 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-bold rounded-lg shadow-sm transition disabled:opacity-50 cursor-pointer">
                        <span wire:loading.remove wire:target="commit" class="material-symbols-outlined text-base">save</span>
                        <span wire:loading wire:target="commit" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                        <span wire:loading.remove wire:target="commit">In Datenbank speichern (Commit)</span>
                        <span wire:loading wire:target="commit">Speichere in Datenbank...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
