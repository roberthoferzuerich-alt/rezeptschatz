<div class="max-w-4xl mx-auto space-y-6">
    <!-- Kopfzeile -->
    <div class="flex items-center justify-between border-b border-stone-200 pb-3">
        <div class="flex items-center space-x-2">
            <a href="{{ route('recipes.index') }}" class="text-stone-500 hover:text-stone-800 transition p-1 rounded-md" title="Zurück">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#8ea92c]">public</span>
                <span>Rezept aus dem Internet importieren</span>
            </h1>
        </div>
    </div>

    <!-- Reiter (Tabs) zwischen Web-URL und COOKmate-Datei -->
    <div class="flex border-b border-stone-200 text-sm font-medium">
        <button type="button" class="py-2.5 px-4 border-b-2 border-[#8ea92c] text-[#8ea92c] font-bold flex items-center space-x-2">
            <span class="material-symbols-outlined text-base">link</span>
            <span>Webseite (URL)</span>
        </button>
        <a href="{{ route('recipes.import') }}" class="py-2.5 px-4 text-stone-500 hover:text-stone-800 transition flex items-center space-x-2 border-b-2 border-transparent">
            <span class="material-symbols-outlined text-base">folder_zip</span>
            <span>COOKmate-Backup (.mcb)</span>
        </a>
    </div>

    <!-- URL-Eingabeformular -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-4">
        <div>
            <label for="url" class="block text-sm font-semibold text-stone-800 mb-1">
                Internet-Adresse (URL) des Rezepts
            </label>
            <p class="text-xs text-stone-500 mb-3">
                Kopieren Sie den Link der Rezeptseite (z. B. von Betty Bossi, Swissmilk, Chefkoch, GuteKüche.ch, Lecker.de etc.) und fügen Sie ihn hier ein.
            </p>
            
            <form wire:submit.prevent="analyzeUrl" class="flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-lg pointer-events-none">link</span>
                    <input wire:model="url" type="url" id="url" 
                           placeholder="https://www.bettybossi.ch/... oder https://www.chefkoch.de/rezepte/..." 
                           class="w-full pl-10 pr-4 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c] focus:border-[#8ea92c] @error('url') border-red-500 @enderror">
                </div>
                <button type="submit" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center space-x-2 px-5 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-semibold rounded-lg shadow-xs transition disabled:opacity-50 cursor-pointer">
                    <span wire:loading.remove wire:target="analyzeUrl" class="material-symbols-outlined text-base">search</span>
                    <span wire:loading wire:target="analyzeUrl" class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                    <span wire:loading.remove wire:target="analyzeUrl">Rezept analysieren</span>
                    <span wire:loading wire:target="analyzeUrl">Lade Rezept...</span>
                </button>
            </form>
            @error('url')
                <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
            @enderror
        </div>

        @if ($errorMessage)
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm flex items-start space-x-3">
                <span class="material-symbols-outlined text-red-600 text-xl mt-0.5">error</span>
                <div class="space-y-1">
                    <p class="font-bold">Import fehlgeschlagen</p>
                    <p class="text-xs">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        <!-- Dezent einklappbare Übersicht der beliebtesten Webseiten (stört nicht) -->
        <div class="border-t border-stone-100 pt-3" x-data="{ open: false }">
            <button type="button" @click="open = !open" 
                    class="text-xs text-stone-500 hover:text-stone-800 transition flex items-center space-x-1.5 focus:outline-none select-none py-1">
                <span class="material-symbols-outlined text-sm text-[#8ea92c]">help_outline</span>
                <span>Welche Webseiten werden unterstützt?</span>
                <span class="text-[11px] text-stone-400" x-text="open ? '(Ausblenden)' : '(Hier klicken zum Einblenden)'"></span>
                <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>

            <div x-show="open" x-transition.opacity.duration.200ms style="display: none;" class="mt-3 text-xs text-stone-600 space-y-3 bg-stone-50/80 p-4 rounded-xl border border-stone-200">
                <p class="text-stone-500">
                    Rezeptschatz nutzt das universelle <strong>Schema.org (JSON-LD)</strong> Format und funktioniert mit hunderten Koch-Portalen weltweit. Hier eine Auswahl der beliebtesten Seiten (Klick öffnet die Seite im neuen Tab zum Stöbern):
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Schweiz -->
                    <div class="bg-white p-3 rounded-lg border border-stone-200 space-y-1.5 shadow-2xs">
                        <div class="font-bold text-stone-800 flex items-center space-x-1 text-xs border-b border-stone-100 pb-1">
                            <span>🇨🇭 Schweiz</span>
                        </div>
                        <ul class="space-y-1 text-stone-600">
                            <li><a href="https://migusto.migros.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Migusto (Migros) ↗</a></li>
                            <li><a href="https://www.bettybossi.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Betty Bossi ↗</a></li>
                            <li><a href="https://www.swissmilk.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Swissmilk ↗</a></li>
                            <li><a href="https://fooby.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Fooby (Coop) ↗</a></li>
                            <li><a href="https://www.gutekueche.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">GuteKüche Schweiz ↗</a></li>
                            <li><a href="https://www.wildeisen.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Annemarie Wildeisen ↗</a></li>
                            <li><a href="https://www.srf.ch/sendungen/landfrauenkueche" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">SRF Landfrauenküche ↗</a></li>
                        </ul>
                    </div>

                    <!-- Deutschland & Österreich -->
                    <div class="bg-white p-3 rounded-lg border border-stone-200 space-y-1.5 shadow-2xs">
                        <div class="font-bold text-stone-800 flex items-center space-x-1 text-xs border-b border-stone-100 pb-1">
                            <span>🇩🇪 / 🇦🇹 DE & AT</span>
                        </div>
                        <ul class="space-y-1 text-stone-600">
                            <li><a href="https://www.chefkoch.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Chefkoch.de ↗</a></li>
                            <li><a href="https://www.essen-und-trinken.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Essen & Trinken ↗</a></li>
                            <li><a href="https://www.lecker.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Lecker.de ↗</a></li>
                            <li><a href="https://www.kuechengoetter.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Küchengötter ↗</a></li>
                            <li><a href="https://www.einfachbacken.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Einfach Backen ↗</a></li>
                            <li><a href="https://www.gutekueche.at" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">GuteKüche Österreich ↗</a></li>
                        </ul>
                    </div>

                    <!-- International & Food Blogs -->
                    <div class="bg-white p-3 rounded-lg border border-stone-200 space-y-1.5 shadow-2xs">
                        <div class="font-bold text-stone-800 flex items-center space-x-1 text-xs border-b border-stone-100 pb-1">
                            <span>🌍 International & Blogs</span>
                        </div>
                        <ul class="space-y-1 text-stone-600">
                            <li><a href="https://www.bbcgoodfood.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">BBC Good Food ↗</a></li>
                            <li><a href="https://www.allrecipes.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Allrecipes ↗</a></li>
                            <li><a href="https://www.jamieoliver.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Jamie Oliver ↗</a></li>
                            <li><a href="https://www.seriouseats.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Serious Eats ↗</a></li>
                            <li class="pt-1 text-[11px] text-stone-500 italic">
                                Sowie tausende private Food-Blogs mit WP Recipe Maker.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VORSCHAU & VOLLSTÄNDIGKEITSPRÜFUNG -->
    @if ($parsedData)
        <div class="space-y-4">
            
            <!-- FALL 1: UNVOLLSTÄNDIGER IMPORT (WARNUNG MIT ABBRECH-MÖGLICHKEIT) -->
            @if (!$parsedData['is_complete'])
                <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 shadow-xs space-y-4">
                    <div class="flex items-start space-x-3">
                        <span class="material-symbols-outlined text-amber-600 text-3xl">warning</span>
                        <div class="flex-1 space-y-2">
                            <h3 class="text-base font-bold text-amber-900">
                                Achtung: Das Rezept konnte nicht vollständig importiert werden!
                            </h3>
                            <p class="text-xs text-amber-800">
                                Die Webseite stellt ihre Daten nicht im vollständigen Schema.org-Standard bereit oder schützt Teile des Inhalts. Folgende Punkte fehlen:
                            </p>
                            <ul class="list-disc list-inside text-xs text-amber-900 space-y-1 font-medium bg-amber-100/60 p-3 rounded-lg border border-amber-200">
                                @foreach ($parsedData['missing_fields'] as $missing)
                                    <li class="flex items-center space-x-1.5">
                                        <span class="material-symbols-outlined text-red-600 text-sm">cancel</span>
                                        <span>{{ $missing }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-amber-800">
                                Möchten Sie den Import <strong>abbrechen</strong> oder die bisher erkannten Angaben (wie Titel, Bild oder Teiltexte) im Formular öffnen und <strong>manuell ergänzen</strong>?
                            </p>
                        </div>
                    </div>

                    <!-- Klare Entscheidungs-Buttons bei unvollständigem Import -->
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2 border-t border-amber-200">
                        <button type="button" wire:click="cancel" 
                                class="w-full sm:w-auto inline-flex items-center justify-center space-x-1.5 px-4 py-2 border border-stone-300 bg-white hover:bg-stone-50 text-stone-700 text-xs font-semibold rounded-lg shadow-xs transition">
                            <span class="material-symbols-outlined text-sm text-red-600">close</span>
                            <span>Vorgang abbrechen</span>
                        </button>
                        <button type="button" wire:click="editBeforeSave" 
                                class="w-full sm:w-auto inline-flex items-center justify-center space-x-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <span class="material-symbols-outlined text-sm">edit_note</span>
                            <span>Trotzdem übernehmen & manuell ergänzen</span>
                        </button>
                    </div>
                </div>
            
            <!-- FALL 2: VOLLSTÄNDIGER IMPORT (ERFOLG) -->
            @else
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center space-x-3">
                        <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                        <div>
                            <h3 class="text-sm font-bold text-emerald-900">Rezept vollständig erkannt!</h3>
                            <p class="text-xs text-emerald-700">
                                {{ count($parsedData['ingredients']) }} Zutaten, {{ count($parsedData['steps']) }} Zubereitungsschritte, Zeiten & Nährwerte wurden erfolgreich erfasst.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- REZEPT-VORSCHAU-KARTE -->
            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 space-y-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Bildvorschau -->
                    <div class="w-full md:w-64 shrink-0">
                        @if (!empty($parsedData['image_url']))
                            <div class="rounded-xl overflow-hidden aspect-4/3 bg-stone-100 border border-stone-200 relative group">
                                <img src="{{ $parsedData['image_url'] }}" alt="{{ $parsedData['title'] }}" class="w-full h-full object-cover">
                                <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[10px] px-1.5 py-0.5 rounded">
                                    Vorschau
                                </span>
                            </div>
                        @else
                            <div class="rounded-xl aspect-4/3 bg-stone-100 border border-stone-200 flex flex-col items-center justify-center text-stone-400">
                                <span class="material-symbols-outlined text-4xl">no_photography</span>
                                <span class="text-xs mt-1">Kein Bild</span>
                            </div>
                        @endif

                        <!-- Eckdaten -->
                        <div class="mt-4 space-y-2 text-xs text-stone-600">
                            <div class="flex justify-between py-1 border-b border-stone-100">
                                <span class="font-medium text-stone-500">Portionen:</span>
                                <span class="font-semibold text-stone-800">{{ $parsedData['portions'] }}</span>
                            </div>
                            @if ($parsedData['prep_time_minutes'])
                                <div class="flex justify-between py-1 border-b border-stone-100">
                                    <span class="font-medium text-stone-500">Vorbereitung:</span>
                                    <span class="font-semibold text-stone-800">{{ $parsedData['prep_time_minutes'] }} Min.</span>
                                </div>
                            @endif
                            @if ($parsedData['cook_time_minutes'])
                                <div class="flex justify-between py-1 border-b border-stone-100">
                                    <span class="font-medium text-stone-500">Kochen / Backen:</span>
                                    <span class="font-semibold text-stone-800">{{ $parsedData['cook_time_minutes'] }} Min.</span>
                                </div>
                            @endif
                            @if ($parsedData['domain'])
                                <div class="flex justify-between py-1 border-b border-stone-100">
                                    <span class="font-medium text-stone-500">Quelle:</span>
                                    <a href="{{ $parsedData['url'] }}" target="_blank" class="font-semibold text-[#8ea92c] hover:underline truncate max-w-[140px]">
                                        {{ $parsedData['domain'] }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Nährwerte -->
                        @if ($parsedData['nutrition']['calories'] || $parsedData['nutrition']['raw'])
                            <div class="mt-3 p-2.5 rounded-lg bg-stone-50 border border-stone-200 text-xs">
                                <span class="font-semibold text-stone-700 block mb-1">Nährwerte:</span>
                                <span class="text-stone-600">
                                    {{ $parsedData['nutrition']['raw'] ?: ($parsedData['nutrition']['calories'] . ' kcal') }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Titel, Beschreibung, Zutaten & Schritte -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <h2 class="text-xl font-bold text-stone-900 leading-snug">
                                {{ $parsedData['title'] ?: 'Unbenanntes Rezept' }}
                            </h2>
                            @if ($parsedData['description'])
                                <p class="text-xs text-stone-600 mt-1.5 leading-relaxed">
                                    {{ $parsedData['description'] }}
                                </p>
                            @endif
                        </div>

                        <!-- Kategorien -->
                        @if (!empty($parsedData['categories']))
                            <div class="flex flex-wrap gap-1">
                                @foreach ($parsedData['categories'] as $cat)
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-stone-100 text-stone-600 border border-stone-200">
                                        {{ $cat }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Zwei Spalten: Zutaten & Zubereitung -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-stone-100">
                            <!-- Zutatenliste -->
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2 flex items-center space-x-1">
                                    <span class="material-symbols-outlined text-sm text-[#8ea92c]">kitchen</span>
                                    <span>Zutaten ({{ count($parsedData['ingredients']) }})</span>
                                </h3>
                                @if (!empty($parsedData['ingredients']))
                                    <ul class="text-xs text-stone-700 space-y-1 max-h-56 overflow-y-auto pr-1 scrollbar-thin">
                                        @foreach ($parsedData['ingredients'] as $ing)
                                            <li class="flex items-start space-x-1 py-0.5 border-b border-stone-50">
                                                <span class="text-stone-400 font-bold">•</span>
                                                <span>{{ $ing['raw'] ?: trim(($ing['amount_text'] ?: $ing['amount_numeric'] . ' ' . $ing['unit']) . ' ' . $ing['name']) }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-stone-400 italic">Keine Zutaten erkannt</p>
                                @endif
                            </div>

                            <!-- Schritte -->
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2 flex items-center space-x-1">
                                    <span class="material-symbols-outlined text-sm text-[#8ea92c]">format_list_numbered</span>
                                    <span>Zubereitung ({{ count($parsedData['steps']) }})</span>
                                </h3>
                                @if (!empty($parsedData['steps']))
                                    <ol class="text-xs text-stone-700 space-y-2 max-h-56 overflow-y-auto pr-1 scrollbar-thin">
                                        @foreach ($parsedData['steps'] as $step)
                                            <li class="flex items-start space-x-2">
                                                <span class="font-bold text-[#8ea92c] shrink-0">{{ $step['step_number'] }}.</span>
                                                <span class="leading-relaxed">{{ $step['instruction'] }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @else
                                    <p class="text-xs text-stone-400 italic">Keine Schritte erkannt</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Aktionen -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-stone-200">
                    <button type="button" wire:click="cancel" class="text-xs text-stone-500 hover:text-stone-800 transition">
                        Abbrechen & andere URL eingeben
                    </button>

                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                        <button type="button" wire:click="editBeforeSave" 
                                class="flex-1 sm:flex-none inline-flex items-center justify-center space-x-1.5 px-4 py-2 border border-stone-300 hover:bg-stone-50 text-stone-700 text-xs font-semibold rounded-lg shadow-xs transition">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            <span>Im Formular anpassen</span>
                        </button>
                        <button type="button" wire:click="saveDirectly" 
                                class="flex-1 sm:flex-none inline-flex items-center justify-center space-x-1.5 px-5 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <span class="material-symbols-outlined text-sm">save</span>
                            <span>Rezept direkt speichern</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

