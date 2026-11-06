<div class="max-w-5xl mx-auto space-y-8 pb-12">

    <!-- OBERER BEREICH: DAS ORIGINAL-DESIGN AUS DEM SCREENSHOT -->
    <div class="pt-4 sm:pt-8 text-center">
        <!-- Grünes rundes Icon mit weisser Weltkugel -->
        <div class="w-20 h-20 rounded-full bg-[#8ea92c] flex items-center justify-center mx-auto shadow-sm">
            <span class="material-symbols-outlined text-white select-none" style="font-size: 44px;">public</span>
        </div>

        <!-- Zentrierter Einführungstext -->
        <p class="text-stone-700 text-sm sm:text-base leading-relaxed text-center max-w-md mx-auto mt-4 font-normal">
            Suche Rezepte auf kompatiblen<br class="hidden sm:inline">
            Webseiten und nutze den Import-<br class="hidden sm:inline">
            Button um das Rezept in Rezeptschatz<br class="hidden sm:inline">
            zu importieren
        </p>

        <!-- Formular mit den 3 Eingabefeldern und grünem Akzentrand links -->
        <div class="max-w-xl mx-auto mt-6 text-left">
            <form wire:submit.prevent="search" class="space-y-3">
                
                <!-- Feld 1: Vollbreite Suche oder URL-Eingabe -->
                <div class="relative bg-white border border-stone-300 shadow-2xs border-l-4 border-l-[#8ea92c] flex items-center">
                    <input type="text"
                           wire:model="query"
                           placeholder="Suche oder URL-Eingabe"
                           class="w-full px-3.5 py-3 text-stone-800 placeholder-stone-400 focus:outline-none text-sm sm:text-base bg-transparent">
                    
                    @if (!empty($query))
                        <button type="button" 
                                wire:click="resetSearch" 
                                class="p-2 text-stone-400 hover:text-stone-600 transition" 
                                title="Eingabe löschen">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    @endif
                </div>

                <!-- Zeile 2: Zwei Felder nebeneinander (Sprache & Seite) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    
                    <!-- Feld 2 links: Sprache -->
                    <div class="relative bg-white border border-stone-300 shadow-2xs border-l-4 border-l-[#8ea92c] px-3 py-1.5 flex flex-col justify-center">
                        <label for="search-lang" class="text-[11px] text-stone-400 font-medium leading-tight">Sprache</label>
                        <select id="search-lang" 
                                wire:model="language" 
                                class="w-full bg-transparent text-stone-800 text-sm font-normal focus:outline-none cursor-pointer py-0.5 border-none p-0 pr-6">
                            @foreach ($languages as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Feld 3 rechts: Seite mit Pfeil > -->
                    <div class="relative bg-white border border-stone-300 shadow-2xs border-l-4 border-l-[#8ea92c] px-3 py-1.5 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <label for="search-site" class="text-[11px] text-stone-400 font-medium leading-tight block">Seite</label>
                            <select id="search-site" 
                                    wire:model="site" 
                                    class="w-full bg-transparent text-stone-800 text-sm font-normal focus:outline-none cursor-pointer py-0.5 border-none p-0 appearance-none truncate pr-6">
                                @foreach ($sites as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="material-symbols-outlined text-stone-400 text-lg pointer-events-none select-none ml-1">chevron_right</span>
                    </div>

                </div>

                <!-- Zentrierter Korallen-/Lachs-Button: Suchen -->
                <div class="flex justify-center pt-3">
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-14 sm:px-18 py-2.5 bg-[#fa7a5b] hover:bg-[#e76546] active:bg-[#d55536] text-white font-medium text-sm sm:text-base rounded-lg shadow-sm transition cursor-pointer flex items-center space-x-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="search">Suchen</span>
                        <span wire:loading wire:target="search" class="flex items-center space-x-2">
                            <span class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                            <span>Suchen...</span>
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Kleiner Hinweis auf COOKmate-Backup Import -->
        <div class="mt-4 text-xs text-stone-500">
            Möchten Sie eine Datei importieren? 
            <a href="{{ route('recipes.import') }}" class="text-[#8ea92c] hover:underline font-semibold ml-1 inline-flex items-center space-x-0.5">
                <span>COOKmate-Backup (.mcb) laden</span>
                <span class="material-symbols-outlined text-xs">arrow_forward</span>
            </a>
        </div>
    </div>

    <!-- FEHLER-MELDUNG -->
    @if ($errorMessage)
        <div class="max-w-3xl mx-auto p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start space-x-3 shadow-xs">
            <span class="material-symbols-outlined text-red-600 text-xl mt-0.5">error</span>
            <div class="flex-1">
                <p class="font-bold">Hinweis</p>
                <p class="text-xs">{{ $errorMessage }}</p>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-stone-400 hover:text-stone-600">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    <!-- REZEPT-VORSCHAU VOR DEM SPEICHERN (WENN URL EINGEGEBEN ODER IMPORT GEKLICKT WURDE) -->
    @if ($parsedRecipe)
        <div id="recipe-preview-box" class="max-w-4xl mx-auto bg-white rounded-2xl shadow-md border-2 border-[#8ea92c] p-6 space-y-6">
            
            <!-- Kopfzeile der Vorschau -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-stone-200 pb-4">
                <div>
                    <div class="flex items-center space-x-2">
                        @if ($parsedRecipe['is_complete'] ?? true)
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                <span>Rezept vollständig erkannt</span>
                            </span>
                        @else
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                <span class="material-symbols-outlined text-xs">warning</span>
                                <span>Teilweise erkannt (kann ergänzt werden)</span>
                            </span>
                        @endif
                        <span class="text-xs text-stone-400">• {{ parse_url($parsedRecipe['url'] ?? '', PHP_URL_HOST) }}</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-stone-900 mt-1">
                        {{ $parsedRecipe['title'] ?? 'Rezept-Vorschau' }}
                    </h2>
                </div>

                <!-- Aktions-Buttons oben -->
                <div class="flex items-center flex-wrap gap-2">
                    <button type="button" 
                            wire:click="saveParsedRecipe"
                            class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-semibold rounded-lg shadow-xs transition cursor-pointer">
                        <span class="material-symbols-outlined text-base">save</span>
                        <span>In Rezeptschatz speichern</span>
                    </button>
                    <button type="button" 
                            wire:click="editBeforeSave"
                            class="inline-flex items-center space-x-1.5 px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 text-sm font-medium rounded-lg transition cursor-pointer"
                            title="Vor dem Speichern bearbeiten">
                        <span class="material-symbols-outlined text-base">edit</span>
                        <span class="hidden sm:inline">Bearbeiten</span>
                    </button>
                    <button type="button" 
                            wire:click="closePreview"
                            class="p-2 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100 transition"
                            title="Vorschau schließen">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
            </div>

            <!-- Beschreibung -->
            @if (!empty($parsedRecipe['description']))
                <p class="text-sm text-stone-600 leading-relaxed italic">
                    „{{ $parsedRecipe['description'] }}“
                </p>
            @endif

            <!-- Metadaten-Kacheln -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-stone-50 rounded-xl p-3 border border-stone-100 text-center">
                    <span class="material-symbols-outlined text-stone-400 text-lg">group</span>
                    <div class="text-xs text-stone-500 font-medium">Portionen</div>
                    <div class="text-base font-bold text-stone-800">{{ $parsedRecipe['portions'] ?? 4 }}</div>
                </div>
                <div class="bg-stone-50 rounded-xl p-3 border border-stone-100 text-center">
                    <span class="material-symbols-outlined text-stone-400 text-lg">schedule</span>
                    <div class="text-xs text-stone-500 font-medium">Vorbereitung</div>
                    <div class="text-base font-bold text-stone-800">
                        {{ !empty($parsedRecipe['prep_time_minutes']) ? $parsedRecipe['prep_time_minutes'] . ' Min.' : '—' }}
                    </div>
                </div>
                <div class="bg-stone-50 rounded-xl p-3 border border-stone-100 text-center">
                    <span class="material-symbols-outlined text-stone-400 text-lg">skillet</span>
                    <div class="text-xs text-stone-500 font-medium">Koch-/Backzeit</div>
                    <div class="text-base font-bold text-stone-800">
                        {{ !empty($parsedRecipe['cook_time_minutes']) ? $parsedRecipe['cook_time_minutes'] . ' Min.' : '—' }}
                    </div>
                </div>
                <div class="bg-stone-50 rounded-xl p-3 border border-stone-100 text-center">
                    <span class="material-symbols-outlined text-stone-400 text-lg">local_fire_department</span>
                    <div class="text-xs text-stone-500 font-medium">Kalorien</div>
                    <div class="text-base font-bold text-stone-800">
                        {{ !empty($parsedRecipe['nutrition']['calories']) ? $parsedRecipe['nutrition']['calories'] . ' kcal' : '—' }}
                    </div>
                </div>
            </div>

            <!-- Nährwert-Leiste (falls vorhanden) -->
            @if (!empty($parsedRecipe['nutrition']['carbohydrates']) || !empty($parsedRecipe['nutrition']['fat']) || !empty($parsedRecipe['nutrition']['protein']))
                <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-3 text-xs text-emerald-900 flex flex-wrap items-center justify-around gap-2">
                    <span class="font-bold flex items-center space-x-1">
                        <span class="material-symbols-outlined text-sm">monitoring</span>
                        <span>Nährwerte pro Portion:</span>
                    </span>
                    @if (!empty($parsedRecipe['nutrition']['carbohydrates']))
                        <span>Kohlenhydrate: <strong>{{ $parsedRecipe['nutrition']['carbohydrates'] }}g</strong></span>
                    @endif
                    @if (!empty($parsedRecipe['nutrition']['fat']))
                        <span>Fett: <strong>{{ $parsedRecipe['nutrition']['fat'] }}g</strong></span>
                    @endif
                    @if (!empty($parsedRecipe['nutrition']['protein']))
                        <span>Eiweiss: <strong>{{ $parsedRecipe['nutrition']['protein'] }}g</strong></span>
                    @endif
                </div>
            @endif

            <!-- Hauptinhalt: Bild + Zutaten + Zubereitung -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Spalte links: Bild & Zutaten -->
                <div class="md:col-span-1 space-y-4">
                    @if (!empty($parsedRecipe['image_url']))
                        <div class="rounded-xl overflow-hidden border border-stone-200 bg-stone-100 aspect-video md:aspect-square">
                            <img src="{{ $parsedRecipe['image_url'] }}" alt="{{ $parsedRecipe['title'] }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="bg-stone-50 rounded-xl p-4 border border-stone-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-stone-900 text-sm flex items-center space-x-1.5">
                                <span class="material-symbols-outlined text-[#8ea92c] text-base">format_list_bulleted</span>
                                <span>Zutaten</span>
                            </h3>
                            <span class="text-xs text-stone-500 font-medium">
                                {{ count($parsedRecipe['ingredients'] ?? []) }} Zutaten
                            </span>
                        </div>

                        <ul class="space-y-1.5 text-xs text-stone-700 divide-y divide-stone-200/60 pt-1">
                            @forelse ($parsedRecipe['ingredients'] ?? [] as $ing)
                                <li class="pt-1.5 first:pt-0">
                                    {{ $ing['raw'] ?? (($ing['amount'] ?? '') . ' ' . ($ing['unit'] ?? '') . ' ' . ($ing['name'] ?? '')) }}
                                </li>
                            @empty
                                <li class="text-stone-400 italic">Keine Zutaten erkannt.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Spalte rechts: Zubereitungsschritte -->
                <div class="md:col-span-2 space-y-4">
                    <div class="bg-stone-50 rounded-xl p-4 border border-stone-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-stone-900 text-sm flex items-center space-x-1.5">
                                <span class="material-symbols-outlined text-[#8ea92c] text-base">cooking</span>
                                <span>Zubereitung</span>
                            </h3>
                            <span class="text-xs text-stone-500 font-medium">
                                {{ count($parsedRecipe['steps'] ?? []) }} Schritte
                            </span>
                        </div>

                        <ol class="space-y-3 pt-1">
                            @forelse ($parsedRecipe['steps'] ?? [] as $idx => $step)
                                <li class="flex items-start space-x-3 text-xs sm:text-sm text-stone-800 leading-relaxed">
                                    <span class="w-6 h-6 rounded-full bg-[#8ea92c] text-white flex-shrink-0 flex items-center justify-center font-bold text-xs mt-0.5">
                                        {{ $idx + 1 }}
                                    </span>
                                    <span class="flex-1">{{ $step['instruction'] ?? $step }}</span>
                                </li>
                            @empty
                                <li class="text-xs text-stone-400 italic">Keine Zubereitungsschritte erkannt.</li>
                            @endforelse
                        </ol>
                    </div>

                    <!-- Button unten: In Rezeptschatz speichern -->
                    <div class="pt-2 flex justify-end space-x-2">
                        <button type="button" 
                                wire:click="saveParsedRecipe"
                                class="inline-flex items-center space-x-1.5 px-6 py-2.5 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-bold rounded-lg shadow-xs transition cursor-pointer">
                            <span class="material-symbols-outlined text-base">save</span>
                            <span>Jetzt in Rezeptschatz speichern</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    @endif

    <!-- EXTERNER SUCH-BANNER (FÜR SCHWEIZER PORTALE WIE BETTY BOSSI, SWISSMILK ETC.) -->
    @if ($externalUrl)
        <div class="max-w-3xl mx-auto p-5 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 shadow-2xs space-y-3">
            <div class="flex items-start space-x-3">
                <span class="material-symbols-outlined text-amber-600 text-2xl">travel_explore</span>
                <div class="flex-1 space-y-2">
                    <h3 class="font-bold text-amber-950 text-sm sm:text-base">
                        Direktsuche auf {{ $sites[$site] ?? $site }}
                    </h3>
                    <p class="text-xs text-amber-900/90 leading-relaxed">
                        Öffnen Sie die Rezeptsuche auf <strong>{{ $sites[$site] ?? $site }}</strong> mit einem Klick. Sobald Sie Ihr Wunschrezept gefunden haben, kopieren Sie einfach die Link-Adresse (URL) aus der Adresszeile des Browsers und fügen Sie sie oben in das Suchfeld ein, um das Rezept direkt in Ihren Rezeptschatz zu importieren!
                    </p>
                    <div class="pt-1">
                        <a href="{{ $externalUrl }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center space-x-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-2xs transition">
                            <span>Auf {{ $sites[$site] ?? $site }} nach „{{ $query }}“ suchen</span>
                            <span class="material-symbols-outlined text-sm">open_in_new</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- SUCH-ERGEBNISSE -->
    @if ($hasSearched && !$parsedRecipe)
        
        <!-- 1. GEFUNDEN IM EIGENEN REZEPTSCHATZ -->
        @if ($localResults && $localResults->isNotEmpty())
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-stone-200 pb-2">
                    <h3 class="text-lg font-bold text-stone-900 flex items-center space-x-2">
                        <span class="material-symbols-outlined text-[#8ea92c]">menu_book</span>
                        <span>In deinem Rezeptschatz gefunden ({{ $localResults->count() }})</span>
                    </h3>
                    <a href="{{ route('recipes.index') }}" class="text-xs text-[#8ea92c] hover:underline font-semibold">
                        Alle Rezepte ansehen
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($localResults as $rec)
                        <div class="bg-white rounded-xl shadow-xs border border-stone-200 overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <!-- Bild -->
                                <div class="relative aspect-video bg-stone-100 overflow-hidden">
                                    @if ($rec->image_path)
                                        <img src="{{ route('recipes.image', $rec->id) }}" alt="{{ $rec->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-stone-300">
                                            <span class="material-symbols-outlined text-4xl">restaurant</span>
                                        </div>
                                    @endif

                                    <!-- Kategorie-Badge -->
                                    @if ($rec->categories && $rec->categories->isNotEmpty())
                                        @php $primaryCat = $rec->categories->first(); @endphp
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold text-white shadow-2xs" style="background-color: {{ $primaryCat->color ?? '#8ea92c' }}">
                                            {{ $primaryCat->name }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Text -->
                                <div class="p-3 space-y-1.5">
                                    <h4 class="font-bold text-sm text-stone-900 line-clamp-1">
                                        {{ $rec->title }}
                                    </h4>

                                    <!-- Tags -->
                                    @if ($rec->tags && $rec->tags->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($rec->tags->take(2) as $t)
                                                <span class="text-[10px] bg-stone-100 text-stone-600 px-1.5 py-0.2 rounded font-medium">
                                                    #{{ $t->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="flex items-center text-xs text-stone-500 space-x-3 pt-1">
                                        @if ($rec->total_time)
                                            <span class="flex items-center space-x-1">
                                                <span class="material-symbols-outlined text-xs">schedule</span>
                                                <span>{{ $rec->total_time }} Min.</span>
                                            </span>
                                        @endif
                                        @if ($rec->rating)
                                            <span class="flex items-center space-x-0.5 text-amber-500 font-semibold">
                                                <span class="material-symbols-outlined text-xs">star</span>
                                                <span>{{ $rec->rating }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 pt-0">
                                <a href="{{ route('recipes.show', $rec->id) }}" 
                                   class="w-full inline-flex items-center justify-center space-x-1 px-3 py-1.5 bg-stone-100 hover:bg-[#8ea92c] hover:text-white text-stone-700 text-xs font-semibold rounded-lg transition">
                                    <span>Rezept ansehen</span>
                                    <span class="material-symbols-outlined text-xs">arrow_forward</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 2. GEFUNDEN IM INTERNET (CHEFKOCH & KOMPATIBLE QUELLEN) -->
        @if (!empty($webResults))
            <div class="space-y-4 pt-2">
                <div class="flex items-center justify-between border-b border-stone-200 pb-2">
                    <h3 class="text-lg font-bold text-stone-900 flex items-center space-x-2">
                        <span class="material-symbols-outlined text-[#8ea92c]">public</span>
                        <span>Rezepte aus dem Internet ({{ count($webResults) }})</span>
                    </h3>
                    <span class="text-xs text-stone-500">
                        Klicken Sie auf «In Rezeptschatz importieren»
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($webResults as $item)
                        <div class="bg-white rounded-xl shadow-xs border border-stone-200 overflow-hidden hover:shadow-md transition flex flex-col justify-between">
                            <div>
                                <!-- Bild -->
                                <div class="relative aspect-video bg-stone-100 overflow-hidden">
                                    @if (!empty($item['image_url']))
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-stone-300">
                                            <span class="material-symbols-outlined text-4xl">restaurant</span>
                                        </div>
                                    @endif

                                    <!-- Quellen-Badge -->
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[10px] font-bold bg-black/60 text-white shadow-2xs backdrop-blur-xs">
                                        {{ $item['source'] ?? 'Web' }}
                                    </span>

                                    <!-- Zeit-Badge -->
                                    @if (!empty($item['prep_time_minutes']))
                                        <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white/90 text-stone-700 shadow-2xs">
                                            ⏱ {{ $item['prep_time_minutes'] }} Min.
                                        </span>
                                    @endif
                                </div>

                                <!-- Titel & Infos -->
                                <div class="p-3 space-y-1.5">
                                    <h4 class="font-bold text-sm text-stone-900 line-clamp-2 leading-snug">
                                        {{ $item['title'] }}
                                    </h4>

                                    @if (!empty($item['subtitle']))
                                        <p class="text-xs text-stone-500 line-clamp-1 italic">
                                            {{ $item['subtitle'] }}
                                        </p>
                                    @endif

                                    <!-- Details -->
                                    <div class="flex items-center text-xs text-stone-500 space-x-2 pt-1">
                                        @if (!empty($item['difficulty']))
                                            <span class="px-1.5 py-0.5 rounded bg-stone-100 text-stone-600 text-[10px] font-medium">
                                                {{ $item['difficulty'] }}
                                            </span>
                                        @endif
                                        @if (!empty($item['rating']))
                                            <span class="flex items-center space-x-0.5 text-amber-500 font-semibold text-[11px]">
                                                <span class="material-symbols-outlined text-xs">star</span>
                                                <span>{{ $item['rating'] }}</span>
                                                @if (!empty($item['votes']))
                                                    <span class="text-stone-400 font-normal">({{ $item['votes'] }})</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Import-Button -->
                            <div class="p-3 pt-0">
                                <button type="button" 
                                        wire:click="importFromUrl('{{ $item['url'] }}')"
                                        wire:loading.attr="disabled"
                                        class="w-full inline-flex items-center justify-center space-x-1 px-3 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs font-bold rounded-lg shadow-2xs transition cursor-pointer disabled:opacity-50">
                                    
                                    @if ($isImporting && $activeImportUrl === $item['url'])
                                        <span class="material-symbols-outlined text-sm animate-spin">progress_activity</span>
                                        <span>Lade Rezept...</span>
                                    @else
                                        <span class="material-symbols-outlined text-sm">download</span>
                                        <span>In Rezeptschatz importieren</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- KEINE ERGEBNISSE -->
        @if (empty($webResults) && ($localResults === null || $localResults->isEmpty()) && !$externalUrl)
            <div class="max-w-md mx-auto text-center py-8 space-y-3">
                <div class="w-12 h-12 rounded-full bg-stone-100 flex items-center justify-center mx-auto text-stone-400">
                    <span class="material-symbols-outlined text-2xl">search_off</span>
                </div>
                <h4 class="font-bold text-stone-800 text-base">Keine Rezepte gefunden</h4>
                <p class="text-xs text-stone-500 leading-relaxed">
                    Für „{{ $query }}“ konnten keine Rezepte gefunden werden. Versuchen Sie einen allgemeineren Begriff (z. B. „Kürbis“, „Auflauf“, „Pasta“) oder wählen Sie <strong>„Alle kompatiblen Webseiten“</strong>.
                </p>
            </div>
        @endif

    @endif

</div>
