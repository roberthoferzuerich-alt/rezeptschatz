<div class="max-w-4xl mx-auto space-y-6">
    <!-- Zurück-Button & Aktionsleiste -->
    <div class="flex items-center justify-between">
        <a href="{{ route('recipes.index') }}" class="inline-flex items-center space-x-1 text-sm font-medium text-stone-600 hover:text-brand-600 transition">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zurück zur Übersicht</span>
        </a>

        <div class="flex items-center space-x-2">
            @if ($canEdit)
                <a href="{{ route('recipes.edit', $recipe->id) }}" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg border border-stone-300 text-stone-700 bg-white hover:bg-stone-50 text-sm font-medium shadow-sm transition">
                    <span class="material-symbols-outlined text-base">edit</span>
                    <span>Bearbeiten</span>
                </a>
            @endif

            @if ($canDelete)
                <button wire:click="delete" wire:confirm="Möchten Sie dieses Rezept wirklich löschen?" 
                        class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 text-sm font-medium transition">
                    <span class="material-symbols-outlined text-base">delete</span>
                    <span>Löschen</span>
                </button>
            @endif

            @if (!$isOwner)
                <button wire:click="copyRecipe" 
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm transition" title="Erstellt eine eigene Kopie zum individuellen Anpassen">
                    <span class="material-symbols-outlined text-base">content_copy</span>
                    <span>Als eigenes Rezept kopieren & anpassen</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Hauptkarte Rezept -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
        <!-- Bild-Bereich -->
        @if ($recipe->image_path)
            <div class="relative w-full aspect-video md:aspect-[21/9] bg-stone-100 overflow-hidden">
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
                    <!-- Kategorien & Autor-Hinweis -->
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

            <!-- Beschreibung -->
            @if ($recipe->description)
                <p class="mt-3 text-stone-600 text-base leading-relaxed">
                    {{ $recipe->description }}
                </p>
            @endif

            <!-- Metadaten: Zeiten & Portionen -->
            <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-xl bg-stone-50 border border-stone-100 text-center">
                <div>
                    <span class="block text-xs text-stone-500">Vorbereitung</span>
                    <span class="text-base font-bold text-stone-800">
                        {{ $recipe->prep_time_minutes ? $recipe->prep_time_minutes . ' Min.' : '–' }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-stone-500">Kochen / Backen</span>
                    <span class="text-base font-bold text-stone-800">
                        {{ $recipe->cook_time_minutes ? $recipe->cook_time_minutes . ' Min.' : '–' }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-stone-500">Ruhezeit</span>
                    <span class="text-base font-bold text-stone-800">
                        {{ $recipe->rest_time_minutes ? $recipe->rest_time_minutes . ' Min.' : '–' }}
                    </span>
                </div>
                <div>
                    <span class="block text-xs text-stone-500">Portionen</span>
                    <div class="inline-flex items-center space-x-1.5 mt-0.5">
                        <button wire:click="decrementPortions" class="w-6 h-6 rounded bg-stone-200 hover:bg-stone-300 text-stone-700 flex items-center justify-center font-bold text-xs transition">
                            -
                        </button>
                        <span class="text-base font-bold text-brand-700 min-w-[1.5rem]">{{ $currentPortions }}</span>
                        <button wire:click="incrementPortions" class="w-6 h-6 rounded bg-stone-200 hover:bg-stone-300 text-stone-700 flex items-center justify-center font-bold text-xs transition">
                            +
                        </button>
                    </div>
                </div>
            </div>

            <!-- Hauptbereich: Zutaten & Zubereitung -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Zutaten -->
                <div class="lg:col-span-5 space-y-4">
                    <div class="flex items-center space-x-2 border-b-2 border-brand-500 pb-2">
                        <span class="material-symbols-outlined text-brand-600">kitchen</span>
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
                    <div class="flex items-center space-x-2 border-b-2 border-brand-500 pb-2">
                        <span class="material-symbols-outlined text-brand-600">menu_book</span>
                        <h2 class="text-lg font-bold text-stone-900">Zubereitung</h2>
                    </div>

                    <ol class="space-y-4">
                        @foreach ($recipe->steps as $step)
                            <li class="flex items-start space-x-3 text-stone-800 text-sm leading-relaxed">
                                <span class="flex-shrink-0 w-7 h-7 rounded-full bg-brand-500 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                    {{ $step->step_number }}
                                </span>
                                <div class="pt-0.5">
                                    {{ $step->instruction }}
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
                            <p class="whitespace-pre-line">{{ $recipe->notes }}</p>
                        </div>
                    @endif

                    @if ($recipe->source_url)
                        <div class="text-xs text-stone-500 flex items-center space-x-1">
                            <span class="material-symbols-outlined text-sm">link</span>
                            <span>Quelle:</span>
                            <a href="{{ $recipe->source_url }}" target="_blank" rel="noopener noreferrer" class="text-brand-600 hover:underline truncate">
                                {{ $recipe->source_url }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
