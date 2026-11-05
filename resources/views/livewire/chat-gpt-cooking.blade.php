<div class="max-w-5xl mx-auto py-4 space-y-6">
    <!-- Kopfbereich -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shadow-xs">
                    <span class="material-symbols-outlined text-xl">smart_toy</span>
                </span>
                <span>Mit ChatGPT kochen</span>
            </h1>
            <p class="text-sm text-stone-500 mt-1">
                Lassen Sie sich von ChatGPT aus Ihren Resten ein neues Rezept kreieren und übernehmen Sie es mit einem Klick!
            </p>
        </div>
        <a href="{{ route('recipes.index') }}" 
           class="inline-flex items-center space-x-1 px-3 py-1.5 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg shadow-xs hover:bg-stone-50 transition">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zurück</span>
        </a>
    </div>

    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start space-x-2">
            <span class="material-symbols-outlined text-lg flex-shrink-0 text-rose-600">error</span>
            <span>{{ $errorMessage }}</span>
        </div>
    @endif

    <!-- 2-Schritte-Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- SCHRITT 1: Zutaten & Kriterien angeben -->
        <div class="lg:col-span-6 bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-5">
            <div class="flex items-center space-x-2 border-b border-stone-100 pb-3">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">1</span>
                <h2 class="text-base font-bold text-stone-900">Rezept-Wunsch zusammenstellen</h2>
            </div>

            <!-- Vorhandene Zutaten -->
            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">
                    Welche Zutaten haben Sie im Kühlschrank?
                </label>
                <textarea wire:model.live.debounce.300ms="fridgeIngredients" rows="3" 
                          placeholder="z. B. 400g Hähnchenbrust, Zucchini, Reis, etwas Rahm..."
                          class="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
                <p class="text-[11px] text-stone-400 mt-1">Geben Sie einfach ein, was verwertet werden soll.</p>
            </div>

            <!-- Optionen: Zeit, Portionen, Ernährung -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Max. Zeit</label>
                    <select wire:model.live="timeLimit" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="15">15 Min.</option>
                        <option value="30">30 Min.</option>
                        <option value="45">45 Min.</option>
                        <option value="60">60 Min.</option>
                        <option value="90">90 Min.</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Portionen</label>
                    <input wire:model.live="portions" type="number" min="1" max="20"
                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Ernährung</label>
                    <select wire:model.live="diet" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="normal">Normal</option>
                        <option value="vegetarian">Vegetarisch</option>
                        <option value="vegan">Vegan</option>
                        <option value="lowcarb">Low-Carb</option>
                        <option value="quick">Schnell & einfach</option>
                    </select>
                </div>
            </div>

            <!-- Voransicht Prompt & Kopieren -->
            <div class="pt-2 space-y-2" x-data="{ copied: false }">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-stone-600">Generierter ChatGPT-Befehl:</span>
                    <button type="button" 
                            @click="navigator.clipboard.writeText($refs.promptBox.value); copied = true; setTimeout(() => copied = false, 2500)"
                            class="text-xs text-emerald-700 hover:text-emerald-900 font-semibold flex items-center space-x-1">
                        <span class="material-symbols-outlined text-sm" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
                        <span x-text="copied ? 'Kopiert!' : 'Befehl kopieren'">Befehl kopieren</span>
                    </button>
                </div>
                <textarea x-ref="promptBox" readonly rows="7" 
                          class="w-full font-mono text-[11px] bg-stone-50 text-stone-700 p-3 rounded-lg border border-stone-200 select-all leading-relaxed">{{ $generatedPrompt }}</textarea>

                <div class="pt-2">
                    <a href="https://chatgpt.com" target="_blank" rel="noopener noreferrer"
                       @click="navigator.clipboard.writeText($refs.promptBox.value)"
                       class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-sm shadow-xs transition">
                        <span>Befehl kopieren & ChatGPT öffnen</span>
                        <span class="material-symbols-outlined text-base">open_in_new</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- SCHRITT 2: Antwort von ChatGPT einfügen -->
        <div class="lg:col-span-6 bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-5">
            <div class="flex items-center space-x-2 border-b border-stone-100 pb-3">
                <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center">2</span>
                <h2 class="text-base font-bold text-stone-900">Rezept von ChatGPT übernehmen</h2>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">
                    ChatGPT-Antwort hier einfügen:
                </label>
                <textarea wire:model="pastedResponse" rows="14" 
                          placeholder="Fügen Sie hier einfach den Text ein, den ChatGPT generiert hat (mit Strg + V)..."
                          class="w-full font-mono text-xs p-3 border border-stone-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:outline-none leading-relaxed"></textarea>
                <p class="text-[11px] text-stone-400 mt-1">
                    Das System erkennt Titel, Zeiten, Mengenangaben und Zubereitungsschritte vollautomatisch.
                </p>
            </div>

            <div class="pt-2">
                <button type="button" wire:click="importPastedRecipe" 
                        class="w-full flex items-center justify-center space-x-2 px-5 py-3 bg-[#8ea92c] hover:bg-[#7e9626] text-white font-bold rounded-lg text-sm shadow-sm transition">
                    <span class="material-symbols-outlined text-lg">restaurant</span>
                    <span>Rezept automatisch in Rezeptschatz anlegen</span>
                </button>
            </div>
        </div>

    </div>
</div>
