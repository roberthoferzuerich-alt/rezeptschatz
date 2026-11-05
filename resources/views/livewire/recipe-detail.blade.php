<div class="max-w-4xl mx-auto space-y-6">
    <!-- Zurück-Button & Aktionsleiste -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="{{ route('recipes.index') }}" class="inline-flex items-center space-x-1 text-sm font-medium text-stone-600 hover:text-[#8ea92c] transition">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zurück zur Übersicht</span>
        </a>

        <!-- Umschalter: Formatiert vs. Raw Markdown -->
        <div class="flex items-center space-x-2">
            <div class="inline-flex items-center p-1 bg-stone-200/80 rounded-lg text-xs font-semibold">
                <button wire:click="setTab('formatted')" 
                        class="px-3 py-1.5 rounded-md transition flex items-center space-x-1.5 {{ $detailTab === 'formatted' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    <span class="material-symbols-outlined text-base text-[#8ea92c]">auto_awesome</span>
                    <span>Formatiert</span>
                </button>
                <button wire:click="setTab('raw')" 
                        class="px-3 py-1.5 rounded-md transition flex items-center space-x-1.5 {{ $detailTab === 'raw' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    <span class="material-symbols-outlined text-base text-stone-500">code</span>
                    <span>Raw Markdown</span>
                </button>
            </div>

            <!-- Zusätzliche Aktionen (Bearbeiten, Drucken, Kopieren, Löschen) -->
            <div class="flex items-center space-x-2">
                <!-- Drucken -->
                <a href="{{ route('recipes.print', $recipe->id) }}" target="_blank"
                   title="Rezept drucken oder als PDF speichern"
                   class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg border border-stone-300 text-stone-700 bg-white hover:bg-stone-50 text-sm font-medium shadow-xs transition cursor-pointer">
                    <span class="material-symbols-outlined text-base">print</span>
                    <span class="hidden sm:inline">Drucken</span>
                </a>

                @if ($canEdit)
                    <a href="{{ route('recipes.edit', $recipe->id) }}" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg border border-stone-300 text-stone-700 bg-white hover:bg-stone-50 text-sm font-medium shadow-xs transition">
                        <span class="material-symbols-outlined text-base">edit</span>
                        <span class="hidden sm:inline">Bearbeiten</span>
                    </a>
                @endif

                @if ($canDelete)
                    <button wire:click="delete" wire:confirm="Möchten Sie dieses Rezept wirklich löschen?" 
                            class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 text-sm font-medium transition">
                        <span class="material-symbols-outlined text-base">delete</span>
                    </button>
                @endif

                @if (!$isOwner)
                    <button wire:click="copyRecipe" 
                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-semibold shadow-xs transition" title="Erstellt eine eigene Kopie zum individuellen Anpassen">
                        <span class="material-symbols-outlined text-base">content_copy</span>
                        <span>Als eigenes Rezept kopieren</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- 1. FORMATIERTE KOCHANSICHT -->
    @if ($detailTab === 'formatted')
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
            <!-- Bild-Bereich -->
            @if ($recipe->image_path)
                <div class="relative w-full aspect-video md:aspect-21/9 bg-stone-100 overflow-hidden">
                    <img src="{{ route('recipes.image', $recipe->id) }}" alt="{{ $recipe->title }}" class="w-full h-full object-cover">
                    <button wire:click="toggleFavorite" class="absolute top-4 right-4 p-2.5 rounded-full bg-white/90 hover:bg-white text-stone-400 hover:text-amber-500 shadow-md transition">
                        <span class="material-symbols-outlined text-2xl {{ $isFavorited ? 'text-amber-500 fill-current' : '' }}">
                            {{ $isFavorited ? 'star' : 'star_border' }}
                        </span>
                    </button>
                </div>
            @endif

            <div class="p-6 sm:p-8">
                <!-- Titel & Favorit -->
                <div class="flex items-start justify-between">
                    <div>
                        <!-- Kategorien & Autor -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-2">
                            <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $isOwner ? 'bg-brand-50 text-brand-700' : 'bg-stone-100 text-stone-700' }}">
                                <span class="material-symbols-outlined text-xs">{{ $isOwner ? 'person' : 'group' }}</span>
                                <span>{{ $isOwner ? 'Mein Rezept' : 'Von ' . ($recipe->user?->name ?: 'Familie') }}</span>
                            </span>

                            @foreach ($recipe->categories as $category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-stone-100 text-stone-700">
                                    {{ $category->name }}
                                </span>
                            @endforeach
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">{{ $recipe->title }}</h1>
                    </div>

                    @if (!$recipe->image_path)
                        <button wire:click="toggleFavorite" class="p-2 rounded-full hover:bg-stone-100 text-stone-400 hover:text-amber-500 transition">
                            <span class="material-symbols-outlined text-3xl {{ $isFavorited ? 'text-amber-500 fill-current' : '' }}">
                                {{ $isFavorited ? 'star' : 'star_border' }}
                            </span>
                        </button>
                    @endif
                </div>

                <!-- Beschreibung (formatiert mit Markdown-Unterstützung) -->
                @if ($recipe->description)
                    <div class="mt-3 text-stone-600 text-base leading-relaxed prose prose-stone max-w-none">
                        {!! Str::markdown(e($recipe->description)) !!}
                    </div>
                @endif

                <!-- Metadaten: Zeiten & Portionen -->
                <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-stone-50 border border-stone-100 text-center">
                    <div>
                        <span class="block text-xs text-stone-500 uppercase tracking-wider font-semibold">Vorbereitung</span>
                        <span class="text-base font-bold text-stone-800">
                            {{ $recipe->prep_time_minutes ? $recipe->prep_time_minutes . ' Min.' : '–' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-stone-500 uppercase tracking-wider font-semibold">Kochen / Backen</span>
                        <span class="text-base font-bold text-stone-800">
                            {{ $recipe->cook_time_minutes ? $recipe->cook_time_minutes . ' Min.' : '–' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-stone-500 uppercase tracking-wider font-semibold">Ruhezeit</span>
                        <span class="text-base font-bold text-stone-800">
                            {{ $recipe->rest_time_minutes ? $recipe->rest_time_minutes . ' Min.' : '–' }}
                        </span>
                    </div>
                    <div>
                        <span class="block text-xs text-stone-500 uppercase tracking-wider font-semibold">Portionen</span>
                        <div class="inline-flex items-center space-x-1.5 mt-0.5">
                            <button wire:click="decrementPortions" class="w-6 h-6 rounded bg-stone-200 hover:bg-stone-300 text-stone-700 flex items-center justify-center font-bold text-xs transition">
                                -
                            </button>
                            <span class="text-base font-bold text-[#8ea92c] min-w-6">{{ $currentPortions }}</span>
                            <button wire:click="incrementPortions" class="w-6 h-6 rounded bg-stone-200 hover:bg-stone-300 text-stone-700 flex items-center justify-center font-bold text-xs transition">
                                +
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Nährwerte-Leiste (falls vorhanden) -->
                @if ($recipe->calories || $recipe->protein || $recipe->fat || $recipe->carbohydrates || $recipe->nutrition_raw)
                    <div class="mt-4 p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200/60 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center space-x-1.5 font-bold text-emerald-900">
                            <span class="material-symbols-outlined text-base text-[#8ea92c]">nutrition</span>
                            <span>Nährwerte pro Portion:</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                            @if ($recipe->calories)
                                <div class="bg-white px-2.5 py-1 rounded-md border border-emerald-200/80 font-semibold text-stone-800 shadow-2xs">
                                    <span class="text-emerald-700 font-bold">{{ $recipe->calories }}</span> kcal
                                </div>
                            @endif
                            @if ($recipe->protein)
                                <div class="bg-white px-2.5 py-1 rounded-md border border-emerald-200/80 text-stone-700 shadow-2xs">
                                    Eiweiß: <strong class="text-stone-900">{{ $recipe->protein }} g</strong>
                                </div>
                            @endif
                            @if ($recipe->fat)
                                <div class="bg-white px-2.5 py-1 rounded-md border border-emerald-200/80 text-stone-700 shadow-2xs">
                                    Fett: <strong class="text-stone-900">{{ $recipe->fat }} g</strong>
                                </div>
                            @endif
                            @if ($recipe->carbohydrates)
                                <div class="bg-white px-2.5 py-1 rounded-md border border-emerald-200/80 text-stone-700 shadow-2xs">
                                    Kohlenhydrate: <strong class="text-stone-900">{{ $recipe->carbohydrates }} g</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Hauptbereich: Zutaten & Zubereitung -->
                <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Zutaten -->
                    <div class="lg:col-span-5 space-y-4">
                        <div class="flex items-center space-x-2 border-b-2 border-[#8ea92c] pb-2">
                            <span class="material-symbols-outlined text-[#8ea92c]">kitchen</span>
                            <h2 class="text-lg font-bold text-stone-900">Zutaten</h2>
                        </div>

                        @php
                            $scaleFactor = ($recipe->portions > 0) ? ($currentPortions / $recipe->portions) : 1;
                        @endphp

                        @foreach ($groupedIngredients as $groupName => $items)
                            <div class="space-y-2">
                                @if ($groupedIngredients->count() > 1 || $groupName !== 'Hauptzutaten')
                                    <h3 class="text-xs font-bold text-stone-400 uppercase tracking-wider pt-2">{{ $groupName }}</h3>
                                @endif

                                <ul class="divide-y divide-stone-100 text-sm">
                                    @foreach ($items as $ing)
                                        <li class="py-2 flex items-baseline justify-between">
                                            <span class="text-stone-800 font-medium">{{ $ing->name }}</span>
                                            <span class="text-stone-600 font-semibold text-right whitespace-nowrap pl-4">
                                                @if ($ing->amount_numeric !== null)
                                                    @php
                                                        $scaled = round($ing->amount_numeric * $scaleFactor, 2);
                                                        $formatted = ($scaled == (int)$scaled) ? (int)$scaled : number_format($scaled, 1, ',', '');
                                                    @endphp
                                                    {{ $formatted }} {{ $ing->unit }}
                                                @elseif ($ing->unit)
                                                    {{ $ing->unit }}
                                                @endif

                                                @if ($ing->amount_text)
                                                    <span class="text-xs font-normal text-stone-500">({{ $ing->amount_text }})</span>
                                                @endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                    <!-- Zubereitungsschritte -->
                    <div class="lg:col-span-7 space-y-4">
                        <div class="flex items-center space-x-2 border-b-2 border-[#8ea92c] pb-2">
                            <span class="material-symbols-outlined text-[#8ea92c]">menu_book</span>
                            <h2 class="text-lg font-bold text-stone-900">Zubereitung</h2>
                        </div>

                        <ol class="space-y-4">
                            @foreach ($recipe->steps as $step)
                                <li class="flex items-start space-x-3 text-stone-800 text-sm leading-relaxed">
                                    <span class="flex-shrink-0 w-7 h-7 rounded-full bg-[#8ea92c] text-white font-bold flex items-center justify-center text-xs shadow-xs">
                                        {{ $step->step_number }}
                                    </span>
                                    <div class="pt-0.5 prose prose-stone max-w-none text-sm">
                                        {!! Str::markdown(e($step->instruction)) !!}
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <!-- Notizen & Quelle -->
                @if ($recipe->notes || $recipe->source_url)
                    <div class="mt-8 pt-6 border-t border-stone-200 space-y-3">
                        @if ($recipe->notes)
                            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">
                                <strong class="block font-semibold mb-1 flex items-center space-x-1">
                                    <span class="material-symbols-outlined text-base">sticky_note_2</span>
                                    <span>Persönliche Notizen:</span>
                                </strong>
                                <div class="prose prose-amber max-w-none text-sm">
                                    {!! Str::markdown(e($recipe->notes)) !!}
                                </div>
                            </div>
                        @endif

                        @if ($recipe->source_url)
                            <div class="text-xs text-stone-500 flex items-center space-x-1">
                                <span class="material-symbols-outlined text-sm">link</span>
                                <span>Quelle:</span>
                                <a href="{{ $recipe->source_url }}" target="_blank" rel="noopener noreferrer" class="text-[#8ea92c] hover:underline truncate">
                                    {{ $recipe->source_url }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    <!-- 2. RAW MARKDOWN ANSICHT -->
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 sm:p-8 space-y-4" x-data="{ copied: false }">
            <div class="flex items-center justify-between border-b border-stone-200 pb-4">
                <div>
                    <h2 class="text-lg font-bold text-stone-900 flex items-center space-x-2">
                        <span class="material-symbols-outlined text-[#8ea92c]">description</span>
                        <span>Raw Markdown-Format</span>
                    </h2>
                    <p class="text-xs text-stone-500 mt-0.5">
                        Vollständiges Rezept als reiner, portabler Markdown-Quelltext (auf {{ $currentPortions }} Portionen skaliert).
                    </p>
                </div>

                <!-- Kopieren-Button mit Clipboard API -->
                <button type="button" 
                        @click="navigator.clipboard.writeText($refs.markdownRaw.value); copied = true; setTimeout(() => copied = false, 2500)"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-lg bg-[#8ea92c] hover:bg-[#7e9626] text-white text-xs font-semibold shadow-xs transition">
                    <span class="material-symbols-outlined text-base" x-text="copied ? 'check' : 'content_copy'">content_copy</span>
                    <span x-text="copied ? 'Kopiert!' : 'In Zwischenablage kopieren'">In Zwischenablage kopieren</span>
                </button>
            </div>

            <!-- Monospace Textanzeige -->
            <div class="relative">
                <textarea x-ref="markdownRaw" readonly rows="22" 
                          class="w-full font-mono text-xs sm:text-sm bg-stone-900 text-stone-100 p-4 sm:p-6 rounded-xl border border-stone-700 leading-relaxed focus:outline-none select-all resize-y">{{ $markdownContent }}</textarea>
            </div>

            <!-- Nutzen-Hinweis -->
            <div class="p-3 bg-stone-50 rounded-lg border border-stone-200 flex items-center space-x-2 text-xs text-stone-600">
                <span class="material-symbols-outlined text-base text-[#8ea92c]">tips_and_updates</span>
                <span>Tipp: Dieser Text lässt sich 1:1 in Obsidian, Notion, GitHub oder direkt in <strong>ChatGPT</strong> zum Kochen und Planen einfügen!</span>
            </div>
        </div>
    @endif
</div>
