<div class="max-w-5xl mx-auto py-4 space-y-6">
    <!-- Kopfbereich -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="w-8 h-8 rounded-lg bg-[#8ea92c] text-white flex items-center justify-center shadow-xs">
                    <span class="material-symbols-outlined text-xl">smart_toy</span>
                </span>
                <span>Mit KI & Kühlschrank kochen</span>
            </h1>
            <p class="text-sm text-stone-500 mt-1">
                Fotografieren Sie Ihren Kühlschrank oder wählen Sie Ihre Reste – die KI zaubert sofort ein neues Rezept!
            </p>
        </div>

        <div class="flex items-center space-x-2">
            @if ($isAiConfigured)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                    <span>KI aktiv ({{ ucfirst($activeAiProvider) }})</span>
                </span>
                <button type="button" wire:click="$toggle('showApiKeyModal')" class="text-xs text-stone-500 hover:text-stone-800 p-1" title="API-Schlüssel ändern">
                    <span class="material-symbols-outlined text-base">settings</span>
                </button>
            @else
                <button type="button" wire:click="$toggle('showApiKeyModal')" 
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-300 rounded-lg shadow-xs transition">
                    <span class="material-symbols-outlined text-base text-amber-600">key</span>
                    <span>API-Schlüssel einrichten</span>
                </button>
            @endif

            <a href="{{ route('recipes.index') }}" 
               class="inline-flex items-center space-x-1 px-3 py-1.5 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg shadow-xs hover:bg-stone-50 transition">
                <span class="material-symbols-outlined text-base">arrow_back</span>
                <span>Zurück</span>
            </a>
        </div>
    </div>

    <!-- Fehlermeldungen -->
    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start justify-between">
            <div class="flex items-start space-x-2">
                <span class="material-symbols-outlined text-lg flex-shrink-0 text-rose-600">error</span>
                <span>{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-400 hover:text-rose-600">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    @if ($successMessage)
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start justify-between">
            <div class="flex items-start space-x-2">
                <span class="material-symbols-outlined text-lg flex-shrink-0 text-emerald-600">check_circle</span>
                <span>{{ $successMessage }}</span>
            </div>
            <button type="button" wire:click="$set('successMessage', null)" class="text-emerald-400 hover:text-emerald-600">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    <!-- MODAL / KARTE ZUR API-SCHLÜSSEL KONFIGURATION -->
    @if ($showApiKeyModal || (! $isAiConfigured && ! $generatedRecipe))
        <div class="bg-gradient-to-r from-amber-50 via-stone-50 to-emerald-50 rounded-2xl border border-amber-200 p-6 shadow-sm space-y-4">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-2 text-stone-900 font-bold text-base">
                    <span class="material-symbols-outlined text-[#8ea92c]">smart_toy</span>
                    <span>KI-API-Schlüssel einrichten</span>
                </div>
                @if ($isAiConfigured)
                    <button type="button" wire:click="$set('showApiKeyModal', false)" class="text-stone-400 hover:text-stone-600">
                        <span class="material-symbols-outlined text-base">close</span>
                    </button>
                @endif
            </div>

            <p class="text-xs text-stone-600">
                Damit die KI Ihren Kühlschrank fotografisch analysieren und neue Rezepte kreieren kann, hinterlegen Sie hier einmalig Ihren API-Schlüssel:
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                <div class="sm:col-span-3">
                    <label class="block text-[11px] font-bold text-stone-700 uppercase mb-1">Anbieter</label>
                    <select wire:model="apiProviderInput" class="w-full text-xs font-semibold rounded-lg border border-stone-300 px-3 py-2 bg-white">
                        <option value="gemini">Google Gemini (Kostenlos)</option>
                        <option value="openai">OpenAI (ChatGPT / GPT-4o)</option>
                    </select>
                </div>
                <div class="sm:col-span-6">
                    <label class="block text-[11px] font-bold text-stone-700 uppercase mb-1">API-Schlüssel</label>
                    <input type="password" wire:model.defer="apiKeyInput" placeholder="AIzaSy... oder sk-proj-..."
                           class="w-full text-xs rounded-lg border border-stone-300 px-3 py-2 font-mono">
                </div>
                <div class="sm:col-span-3">
                    <button type="button" wire:click="saveApiKey" class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-xs font-bold rounded-lg shadow-xs transition">
                        <span class="material-symbols-outlined text-base mr-1">save</span>
                        <span>Schlüssel speichern</span>
                    </button>
                </div>
            </div>

            @if ($isAiConfigured)
                <div class="pt-2 flex justify-between items-center text-xs border-t border-stone-200/60">
                    <span class="text-stone-600">Aktueller Anbieter: <strong>{{ ucfirst($activeAiProvider) }}</strong> (aktiv)</span>
                    <button type="button" wire:click="removeApiKey" class="text-rose-600 hover:text-rose-800 font-semibold hover:underline">
                        Schlüssel entfernen
                    </button>
                </div>
            @endif

            <div class="text-[11px] text-stone-500 bg-white/70 p-3 rounded-xl border border-stone-200/80">
                <strong class="text-stone-700">Tipp für Google Gemini (Kostenlos):</strong> Besuchen Sie 
                <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-[#8ea92c] font-semibold underline">aistudio.google.com</a>, 
                melden Sie sich mit Ihrem Google-Konto an und klicken Sie auf <em>„Create API key“</em>.
            </div>
        </div>
    @endif

    <!-- HAUPTBEREICH: REZEPT GENERIEREN ODER ERGEBNIS ANZEIGEN -->
    @if (! $generatedRecipe)
        
        <!-- MODUS-TABS -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <div class="border-b border-stone-200 flex">
                <button type="button" wire:click="$set('mode', 'photo')" 
                        class="flex-1 py-3 px-4 text-center border-b-2 text-sm font-medium flex items-center justify-center space-x-2 transition {{ $mode === 'photo' ? 'border-[#8ea92c] text-[#8ea92c] font-bold bg-[#8ea92c]/5' : 'border-transparent text-stone-600 hover:text-stone-900' }}">
                    <span class="material-symbols-outlined text-xl">photo_camera</span>
                    <span>1. Kühlschrank fotografieren (Foto-Scan)</span>
                </button>
                <button type="button" wire:click="$set('mode', 'text')" 
                        class="flex-1 py-3 px-4 text-center border-b-2 text-sm font-medium flex items-center justify-center space-x-2 transition {{ $mode === 'text' ? 'border-[#8ea92c] text-[#8ea92c] font-bold bg-[#8ea92c]/5' : 'border-transparent text-stone-600 hover:text-stone-900' }}">
                    <span class="material-symbols-outlined text-xl">edit_note</span>
                    <span>2. Zutaten als Text nennen</span>
                </button>
                <button type="button" wire:click="$set('mode', 'manual')" 
                        class="flex-1 py-3 px-4 text-center border-b-2 text-sm font-medium flex items-center justify-center space-x-2 transition {{ $mode === 'manual' ? 'border-[#8ea92c] text-[#8ea92c] font-bold bg-[#8ea92c]/5' : 'border-transparent text-stone-600 hover:text-stone-900' }}">
                    <span class="material-symbols-outlined text-xl">content_copy</span>
                    <span>3. ChatGPT-Prompt kopieren</span>
                </button>
            </div>

            <!-- TAB 1: KÜHLSCHRANK FOTOGRAFIEREN -->
            @if ($mode === 'photo')
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
                        
                        <!-- Linke Spalte: Foto Upload / Kamera -->
                        <div class="md:col-span-5 space-y-4">
                            @if (! $photoPreviewUrl)
                                <label class="border-2 border-dashed border-[#8ea92c]/60 hover:border-[#8ea92c] bg-stone-50 hover:bg-[#8ea92c]/5 rounded-2xl p-8 flex flex-col items-center justify-center cursor-pointer transition text-center group h-64">
                                    <input type="file" accept="image/*" capture="environment" wire:model="fridgePhoto" class="hidden">
                                    <span class="w-16 h-16 rounded-full bg-[#8ea92c]/10 text-[#8ea92c] flex items-center justify-center mb-3 group-hover:scale-105 transition">
                                        <span class="material-symbols-outlined text-3xl">photo_camera</span>
                                    </span>
                                    <span class="font-bold text-stone-800 text-base">Kühlschrank fotografieren</span>
                                    <span class="text-xs text-stone-500 mt-1">Öffnet Tablet- / Smartphone-Kamera</span>
                                </label>
                            @else
                                <div class="relative rounded-2xl overflow-hidden border border-stone-200 h-64 bg-black/5 flex items-center justify-center">
                                    <img src="{{ $photoPreviewUrl }}" alt="Kühlschrank" class="w-full h-full object-contain">
                                    <div wire:loading wire:target="fridgePhoto" class="absolute inset-0 bg-white/80 flex items-center justify-center text-xs font-semibold text-stone-700">
                                        Lädt Foto hoch...
                                    </div>
                                    <button type="button" wire:click="$set('fridgePhoto', null); $set('photoPreviewUrl', null)" 
                                            class="absolute top-2 right-2 bg-black/60 hover:bg-black/80 text-white rounded-full p-1.5 transition">
                                        <span class="material-symbols-outlined text-base">close</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Rechte Spalte: Kriterien & Aktion -->
                        <div class="md:col-span-7 space-y-4">
                            <div>
                                <h3 class="font-bold text-stone-900 text-base">Kühlschrank-Foto per KI analysieren</h3>
                                <p class="text-xs text-stone-500">
                                    Die KI erkennt die Lebensmittel im Bild und kreiert daraus ein köstliches Rezept.
                                </p>
                            </div>

                            <!-- Kriterien-Raster -->
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-stone-700 mb-1">Max. Zeit</label>
                                    <select wire:model="timeLimit" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs bg-white">
                                        <option value="15">15 Min.</option>
                                        <option value="30">30 Min.</option>
                                        <option value="45">45 Min.</option>
                                        <option value="60">60 Min.</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-stone-700 mb-1">Portionen</label>
                                    <input wire:model="portions" type="number" min="1" max="20"
                                           class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-stone-700 mb-1">Ernährung</label>
                                    <select wire:model="diet" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs bg-white">
                                        <option value="normal">Normal</option>
                                        <option value="vegetarian">Vegetarisch</option>
                                        <option value="vegan">Vegan</option>
                                        <option value="lowcarb">Low-Carb</option>
                                        <option value="quick">Schnell</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-stone-700 mb-1">Zusatzwünsche (optional)</label>
                                <input type="text" wire:model.defer="extraWishes" placeholder="z. B. Gerne mit Pasta, nicht zu scharf..."
                                       class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs">
                            </div>

                            <div class="pt-2">
                                <button type="button" wire:click="generateFromPhoto" 
                                        wire:loading.attr="disabled"
                                        class="w-full inline-flex items-center justify-center px-6 py-3.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-bold rounded-xl shadow transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="generateFromPhoto" class="flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-xl">auto_awesome</span>
                                        <span>Rezept aus Kühlschrankfoto zaubern</span>
                                    </span>
                                    <span wire:loading wire:target="generateFromPhoto" class="flex items-center space-x-2">
                                        <span class="material-symbols-outlined text-xl animate-spin">autorenew</span>
                                        <span>Analysiere Kühlschrank & erstelle Rezept...</span>
                                    </span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            @endif

            <!-- TAB 2: ZUTATEN ALS TEXT -->
            @if ($mode === 'text')
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">
                            Welche Zutaten haben Sie im Kühlschrank oder Vorrat?
                        </label>
                        <textarea wire:model.defer="fridgeIngredients" rows="3" 
                                  placeholder="z. B. 400g Hähnchenbrust, Zucchini, Reis, etwas Rahm, eine halbe Zwiebel..."
                                  class="w-full px-3.5 py-2.5 border border-stone-300 rounded-xl text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Max. Zeit</label>
                            <select wire:model="timeLimit" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs bg-white">
                                <option value="15">15 Min.</option>
                                <option value="30">30 Min.</option>
                                <option value="45">45 Min.</option>
                                <option value="60">60 Min.</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Portionen</label>
                            <input wire:model="portions" type="number" min="1" max="20" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">Ernährung</label>
                            <select wire:model="diet" class="w-full px-3 py-2 border border-stone-300 rounded-lg text-xs bg-white">
                                <option value="normal">Normal</option>
                                <option value="vegetarian">Vegetarisch</option>
                                <option value="vegan">Vegan</option>
                                <option value="lowcarb">Low-Carb</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" wire:click="generateFromTextAi" 
                                wire:loading.attr="disabled"
                                class="inline-flex items-center px-6 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-bold rounded-xl shadow transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="generateFromTextAi" class="flex items-center space-x-1.5">
                                <span class="material-symbols-outlined text-lg">auto_awesome</span>
                                <span>Rezept mit KI erstellen</span>
                            </span>
                            <span wire:loading wire:target="generateFromTextAi" class="flex items-center space-x-1.5">
                                <span class="material-symbols-outlined text-lg animate-spin">autorenew</span>
                                <span>Erstelle Rezept...</span>
                            </span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- TAB 3: CHATGPT-PROMPT KOPIEREN (MANUELL) -->
            @if ($mode === 'manual')
                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h3 class="font-bold text-stone-900 text-sm">Schritt 1: Prompt kopieren</h3>
                        <textarea readonly rows="8" class="w-full p-3 bg-stone-50 border border-stone-300 rounded-xl text-xs font-mono text-stone-800">{{ $this->generatedPrompt }}</textarea>
                        <button type="button" onclick="navigator.clipboard.writeText(`{{ addslashes($this->generatedPrompt) }}`); alert('Prompt kopiert!');" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold rounded-xl border border-stone-300 transition">
                            <span class="material-symbols-outlined text-base mr-1">content_copy</span>
                            <span>Prompt in Zwischenablage kopieren</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <h3 class="font-bold text-stone-900 text-sm">Schritt 2: ChatGPT-Antwort einfügen</h3>
                        <textarea wire:model.defer="pastedResponse" rows="8" placeholder="Fügen Sie hier die Antwort von ChatGPT ein..."
                                  class="w-full p-3 border border-stone-300 rounded-xl text-xs font-mono"></textarea>
                        <button type="button" wire:click="importPastedRecipe" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-base mr-1">download</span>
                            <span>Rezept aus Antwort importieren</span>
                        </button>
                    </div>
                </div>
            @endif

        </div>

    @else
        
        <!-- GENERIERTES REZEPT-ERGEBNIS -->
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-6">
            
            <div class="flex items-center justify-between border-b border-stone-100 pb-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-[#8ea92c]">KI-Rezeptvorschlag</span>
                    <h2 class="text-2xl font-extrabold text-stone-900 mt-0.5">{{ $generatedRecipe['title'] }}</h2>
                    @if (!empty($generatedRecipe['description']))
                        <p class="text-sm text-stone-600 mt-1">{{ $generatedRecipe['description'] }}</p>
                    @endif
                </div>
                <button type="button" wire:click="$set('generatedRecipe', null)" class="text-stone-400 hover:text-stone-600 p-1">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Erkannte Zutaten im Kühlschrank -->
            @if (!empty($generatedRecipe['detected_ingredients']))
                <div class="bg-stone-50 p-4 rounded-xl border border-stone-200/80">
                    <div class="text-xs font-bold text-stone-700 uppercase mb-2 flex items-center space-x-1">
                        <span class="material-symbols-outlined text-sm text-[#8ea92c]">visibility</span>
                        <span>Im Kühlschrank erkannt:</span>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($generatedRecipe['detected_ingredients'] as $detected)
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-white text-stone-800 border border-stone-200 shadow-2xs">
                                ✓ {{ $detected }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Kennzahlen & Nährwerte -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs font-semibold text-stone-700 bg-[#8ea92c]/5 p-3 rounded-xl border border-[#8ea92c]/20">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center space-x-1">
                        <span class="material-symbols-outlined text-base text-[#8ea92c]">group</span>
                        <span>{{ $generatedRecipe['portions'] }} Portionen</span>
                    </div>
                    @if (!empty($generatedRecipe['prep_time_minutes']))
                        <div class="flex items-center space-x-1">
                            <span class="material-symbols-outlined text-base text-[#8ea92c]">timer</span>
                            <span>Vorbereitung: {{ $generatedRecipe['prep_time_minutes'] }} Min.</span>
                        </div>
                    @endif
                    @if (!empty($generatedRecipe['cook_time_minutes']))
                        <div class="flex items-center space-x-1">
                            <span class="material-symbols-outlined text-base text-[#8ea92c]">skillet</span>
                            <span>Kochzeit: {{ $generatedRecipe['cook_time_minutes'] }} Min.</span>
                        </div>
                    @endif
                </div>

                @if (!empty($generatedRecipe['calories']) || !empty($generatedRecipe['protein']) || !empty($generatedRecipe['fat']) || !empty($generatedRecipe['carbohydrates']))
                    <div class="flex flex-wrap items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-stone-200/50">
                        <span class="text-[11px] uppercase tracking-wider text-stone-500 font-bold">Nährwerte / Port.:</span>
                        @if (!empty($generatedRecipe['calories']))
                            <span class="bg-amber-100/80 text-amber-900 px-2 py-0.5 rounded-md font-bold">{{ $generatedRecipe['calories'] }} kcal</span>
                        @endif
                        @if (!empty($generatedRecipe['carbohydrates']))
                            <span class="bg-stone-100 text-stone-800 px-2 py-0.5 rounded-md">{{ $generatedRecipe['carbohydrates'] }} g KH</span>
                        @endif
                        @if (!empty($generatedRecipe['fat']))
                            <span class="bg-stone-100 text-stone-800 px-2 py-0.5 rounded-md">{{ $generatedRecipe['fat'] }} g Fett</span>
                        @endif
                        @if (!empty($generatedRecipe['protein']))
                            <span class="bg-emerald-100/80 text-emerald-900 px-2 py-0.5 rounded-md font-bold">{{ $generatedRecipe['protein'] }} g Eiweiss</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Zutaten und Schritte nebeneinander -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Zutaten -->
                <div class="md:col-span-5 space-y-2">
                    <h3 class="font-bold text-stone-900 text-sm flex items-center space-x-1 border-b border-stone-100 pb-2">
                        <span class="material-symbols-outlined text-[#8ea92c] text-lg">grocery</span>
                        <span>Benötigte Zutaten</span>
                    </h3>
                    <ul class="space-y-1.5 text-xs text-stone-800">
                        @foreach ($generatedRecipe['ingredients'] as $ing)
                            <li class="flex items-baseline space-x-2 py-1 border-b border-stone-50">
                                <span class="font-bold text-stone-900 w-16 text-right">
                                    {{ $ing['amount_text'] ?? ($ing['amount_numeric'] ? $ing['amount_numeric'] . ' ' . $ing['unit'] : 'etwas') }}
                                </span>
                                <span>{{ $ing['name'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Zubereitung -->
                <div class="md:col-span-7 space-y-2">
                    <h3 class="font-bold text-stone-900 text-sm flex items-center space-x-1 border-b border-stone-100 pb-2">
                        <span class="material-symbols-outlined text-[#8ea92c] text-lg">format_list_numbered</span>
                        <span>Zubereitung</span>
                    </h3>
                    <div class="space-y-2.5 text-xs text-stone-800">
                        @foreach ($generatedRecipe['steps'] as $st)
                            <div class="flex items-start space-x-2">
                                <span class="w-5 h-5 rounded-full bg-stone-100 text-stone-700 font-bold flex items-center justify-center shrink-0 text-[10px]">
                                    {{ $st['step_number'] ?? $loop->iteration }}
                                </span>
                                <p class="leading-relaxed">{{ $st['instruction'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Aktionen -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-stone-200">
                <button type="button" wire:click="$set('generatedRecipe', null)" class="text-xs text-stone-600 hover:text-stone-900 font-medium">
                    ← Anderes Rezept probieren
                </button>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <button type="button" wire:click="editInFullEditor" 
                            class="inline-flex items-center px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold rounded-xl border border-stone-300 transition">
                        <span class="material-symbols-outlined text-base mr-1">edit_note</span>
                        <span>Im Editor anpassen</span>
                    </button>
                    <button type="button" wire:click="saveGeneratedRecipe" 
                            class="inline-flex items-center px-5 py-2 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-xs font-bold rounded-xl shadow transition">
                        <span class="material-symbols-outlined text-base mr-1">save</span>
                        <span>In Rezeptschatz speichern</span>
                    </button>
                </div>
            </div>

        </div>

    @endif

</div>
