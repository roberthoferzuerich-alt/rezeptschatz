<div>
    <!-- Oberer Such- und Filterbereich -->
    <div class="bg-white rounded-xl shadow-xs border border-stone-200 p-4 mb-5 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Suchfeld -->
            <div class="md:col-span-6 relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-xl pointer-events-none">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rezept oder Zutat suchen..." 
                       class="w-full pl-10 pr-4 py-2 border border-stone-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#8ea92c] focus:border-[#8ea92c]">
                @if ($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                @endif
            </div>

            <!-- Autoren-Filter (Familie) -->
            <div class="md:col-span-4 flex items-center space-x-1 bg-stone-100 p-1 rounded-lg">
                <button wire:click="$set('authorFilter', 'all')" 
                        class="flex-1 py-1.5 px-2 rounded-md text-xs font-semibold transition {{ $authorFilter === 'all' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    Alle Rezepte
                </button>
                <button wire:click="$set('authorFilter', 'mine')" 
                        class="flex-1 py-1.5 px-2 rounded-md text-xs font-semibold transition {{ $authorFilter === 'mine' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    Meine
                </button>
                <button wire:click="$set('authorFilter', 'shared')" 
                        class="flex-1 py-1.5 px-2 rounded-md text-xs font-semibold transition {{ $authorFilter === 'shared' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    Familie / Geteilt
                </button>
            </div>

            <!-- Schnellfilter Favoriten -->
            <div class="md:col-span-2 flex justify-end">
                <button wire:click="toggleFavoriteFilter" 
                        class="w-full flex items-center justify-center space-x-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition {{ $onlyFavorites ? 'bg-amber-500 border-amber-600 text-white shadow-xs' : 'bg-stone-50 border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                    <span class="material-symbols-outlined text-base {{ $onlyFavorites ? 'text-white' : 'text-amber-500' }}">star</span>
                    <span>Favoriten</span>
                </button>
            </div>
        </div>

        <!-- Kategorien-Chips -->
        <div class="pt-3 border-t border-stone-100 flex items-center space-x-2 overflow-x-auto pb-1 scrollbar-thin">
            <button wire:click="selectCategory('')" 
                    class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition {{ $selectedCategory === '' ? 'bg-[#8ea92c] text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                Alle Kategorien ({{ $totalRecipeCount }})
            </button>
            @foreach ($categories as $cat)
                <button wire:click="selectCategory('{{ $cat->id }}')" 
                        class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition flex items-center space-x-1 {{ $selectedCategory === $cat->id ? 'bg-[#8ea92c] text-white shadow-xs' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $cat->color ?? '#8ea92c' }};"></span>
                    <span>{{ $cat->name }} ({{ $cat->recipes_count }})</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- COOKmate-Style: Sortieren nach Leiste (wie im Screenshot) -->
    <div class="flex flex-wrap items-center justify-center gap-2 mb-4 text-xs sm:text-sm">
        <span class="text-stone-500 font-medium mr-1">Sortieren nach :</span>
        <button wire:click="$set('sortBy', 'title')" 
                class="px-3 py-1.5 rounded-md border text-xs transition {{ $sortBy === 'title' ? 'bg-[#50c0a0] border-[#50c0a0] text-white font-medium shadow-xs' : 'bg-white border-stone-300 text-stone-600 hover:bg-stone-50' }}">
            Titel
        </button>
        <button wire:click="$set('sortBy', 'last_viewed')" 
                class="px-3 py-1.5 rounded-md border text-xs transition {{ $sortBy === 'last_viewed' ? 'bg-[#50c0a0] border-[#50c0a0] text-white font-medium shadow-xs' : 'bg-white border-stone-300 text-stone-600 hover:bg-stone-50' }}">
            Zuletzt angesehen
        </button>
        <button wire:click="$set('sortBy', 'updated_at')" 
                class="px-3 py-1.5 rounded-md border text-xs transition {{ $sortBy === 'updated_at' ? 'bg-[#50c0a0] border-[#50c0a0] text-white font-medium shadow-xs' : 'bg-white border-stone-300 text-stone-600 hover:bg-stone-50' }}">
            Zuletzt geändert
        </button>
        <button wire:click="$set('sortBy', 'created_at')" 
                class="px-3 py-1.5 rounded-md border text-xs transition {{ $sortBy === 'created_at' ? 'bg-[#50c0a0] border-[#50c0a0] text-white font-medium shadow-xs' : 'bg-white border-stone-300 text-stone-600 hover:bg-stone-50' }}">
            Kürzlich erstellt
        </button>
        <button wire:click="$set('sortBy', 'rating')" 
                class="px-3 py-1.5 rounded-md border text-xs transition {{ $sortBy === 'rating' ? 'bg-[#50c0a0] border-[#50c0a0] text-white font-medium shadow-xs' : 'bg-white border-stone-300 text-stone-600 hover:bg-stone-50' }}">
            Bewertung
        </button>
    </div>

    <!-- Obere Zeile mit Anzahl & Umschalt-Button (wie im Screenshot) -->
    <div class="flex items-center justify-between mb-4 px-1">
        <div>
            @if ($search || $selectedCategory || $onlyFavorites || $authorFilter !== 'all')
                <button wire:click="resetFilters" class="text-xs text-[#8ea92c] hover:underline flex items-center space-x-1">
                    <span class="material-symbols-outlined text-sm">refresh</span>
                    <span>Filter zurücksetzen</span>
                </button>
            @endif
        </div>

        <div class="flex flex-col items-end space-y-1">
            <!-- Anzahl Rezepte -->
            <span class="text-xs sm:text-sm text-stone-600 font-normal">
                {{ $recipes->total() }} {{ $recipes->total() === 1 ? 'Rezept' : 'Rezepte' }}
            </span>

            <!-- Umschalt-Buttons rechts -->
            <div class="flex items-center space-x-2 text-stone-500">
                <!-- Toggle Button für Kacheln / Liste -->
                <button wire:click="toggleViewMode" 
                        title="{{ $viewMode === 'grid' ? 'Zur Listenansicht umschalten' : 'Zur Kachelansicht umschalten' }}"
                        class="p-1 rounded-md text-stone-600 hover:text-stone-900 hover:bg-stone-100 transition focus:outline-none">
                    @if ($viewMode === 'grid')
                        <!-- In Kachelansicht: Icon für Liste (wie im Screenshot 1) -->
                        <span class="material-symbols-outlined text-2xl" style="font-size: 26px;">format_list_bulleted</span>
                    @else
                        <!-- In Listenansicht: Icon für Kacheln (wie im Screenshot 2) -->
                        <span class="material-symbols-outlined text-2xl" style="font-size: 26px;">grid_view</span>
                    @endif
                </button>

                <!-- Neues Rezept Button / Stift-Icon (wie im Screenshot) -->
                <a href="{{ route('recipes.create') }}" 
                   title="Neues Rezept erstellen"
                   class="p-1 rounded-md text-stone-600 hover:text-stone-900 hover:bg-stone-100 transition">
                    <span class="material-symbols-outlined text-xl" style="font-size: 24px;">edit</span>
                </a>
            </div>
        </div>
    </div>

    <!-- REZEPTE ANZEIGE -->
    @if ($recipes->count() > 0)
        
        <!-- 1. KACHELANSICHT (Grid-View wie in Screenshot 1) -->
        @if ($viewMode === 'grid')
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                @foreach ($recipes as $recipe)
                    @php
                        $isFavorited = $recipe->isFavoritedBy(Auth::user());
                        $isMyRecipe = ($recipe->user_id === $currentUserId);
                    @endphp
                    <div class="bg-white rounded-lg border border-stone-200 overflow-hidden shadow-2xs hover:shadow-md transition duration-150 flex flex-col group">
                        
                        <!-- Bild -->
                        <a href="{{ route('recipes.show', $recipe->id) }}" class="block relative aspect-4/3 bg-stone-100 overflow-hidden">
                            @if ($recipe->image_path)
                                <img src="{{ route('recipes.image', $recipe->id) }}" alt="{{ $recipe->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-103 transition duration-200">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-stone-300 bg-stone-50">
                                    <span class="material-symbols-outlined text-4xl">restaurant</span>
                                </div>
                            @endif

                            <!-- Favoriten-Stern oben rechts -->
                            <button wire:click.prevent="toggleFavorite('{{ $recipe->id }}')" 
                                    title="{{ $isFavorited ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen' }}" 
                                    class="absolute top-2 right-2 p-1.5 rounded-full bg-white/80 hover:bg-white text-stone-400 hover:text-amber-500 shadow-xs transition">
                                <span class="material-symbols-outlined text-lg {{ $isFavorited ? 'text-amber-500 fill-current' : '' }}">
                                    {{ $isFavorited ? 'star' : 'star_border' }}
                                </span>
                            </button>

                            <!-- Autor-Badge oben links -->
                            @if (!$isMyRecipe)
                                <span class="absolute top-2 left-2 bg-black/60 backdrop-blur-xs text-white text-[10px] font-semibold px-2 py-0.5 rounded">
                                    {{ $recipe->user?->name ?: 'Geteilt' }}
                                </span>
                            @endif
                        </a>

                        <!-- Kachel-Footer mit Titel & 5 Sternen -->
                        <div class="p-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-stone-900 group-hover:text-[#8ea92c] transition text-sm sm:text-base line-clamp-2">
                                    <a href="{{ route('recipes.show', $recipe->id) }}">
                                        {{ $recipe->title }}
                                    </a>
                                </h3>

                                <!-- 5 Sterne Bewertung (grau wie im Screenshot) -->
                                <div class="flex items-center text-stone-300 text-xs mt-1 space-x-0.5">
                                    <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                    <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                    <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                    <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                    <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                </div>
                            </div>

                            @if ($recipe->categories->isNotEmpty())
                                <div class="mt-2 pt-2 border-t border-stone-100 flex flex-wrap gap-1">
                                    @foreach ($recipe->categories->take(2) as $category)
                                        <span class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-stone-100 text-stone-600">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        <!-- 2. LISTENANSICHT (List-View wie in Screenshot 2) -->
        @else
            <div class="space-y-3">
                @foreach ($recipes as $recipe)
                    @php
                        $isFavorited = $recipe->isFavoritedBy(Auth::user());
                        $isMyRecipe = ($recipe->user_id === $currentUserId);
                        
                        // Zutaten-Text wie im COOKmate-Screenshot erstellen
                        $ingredientsText = $recipe->ingredients->map(function($i) {
                            $amount = $i->amount_text ?: ($i->amount_numeric ? ($i->amount_numeric + 0) . ' ' . $i->unit : '');
                            return trim($amount . ' ' . $i->name);
                        })->filter()->join(' ');

                        if (empty($ingredientsText)) {
                            $ingredientsText = $recipe->description ?: 'Keine Zutaten oder Beschreibung hinterlegt';
                        }
                    @endphp
                    <div class="bg-white border border-stone-200 rounded-md p-2 hover:border-stone-300 hover:shadow-xs transition duration-150 flex items-start space-x-3 group">
                        
                        <!-- Thumbnail links -->
                        <a href="{{ route('recipes.show', $recipe->id) }}" class="w-24 h-20 sm:w-28 sm:h-24 bg-stone-100 rounded-sm overflow-hidden flex-shrink-0 block relative">
                            @if ($recipe->image_path)
                                <img src="{{ route('recipes.image', $recipe->id) }}" alt="{{ $recipe->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-stone-300 bg-stone-50">
                                    <span class="material-symbols-outlined text-3xl">restaurant</span>
                                </div>
                            @endif
                        </a>

                        <!-- Inhalt rechts -->
                        <div class="flex-1 min-w-0 py-0.5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <!-- Titel -->
                                    <h3 class="text-sm sm:text-base font-bold text-stone-900 group-hover:text-[#8ea92c] transition truncate">
                                        <a href="{{ route('recipes.show', $recipe->id) }}">
                                            {{ $recipe->title }}
                                        </a>
                                    </h3>

                                    <!-- 5 Sterne Bewertung -->
                                    <div class="flex items-center text-stone-300 text-xs mt-0.5 space-x-0.5">
                                        <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                        <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                        <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                        <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                        <span class="material-symbols-outlined text-sm leading-none">star_rate</span>
                                    </div>
                                </div>

                                <!-- Favoriten-Stern -->
                                <button wire:click.prevent="toggleFavorite('{{ $recipe->id }}')" 
                                        title="{{ $isFavorited ? 'Aus Favoriten entfernen' : 'Zu Favoriten hinzufügen' }}"
                                        class="p-1 rounded-full text-stone-400 hover:text-amber-500 transition flex-shrink-0 ml-2">
                                    <span class="material-symbols-outlined text-lg {{ $isFavorited ? 'text-amber-500 fill-current' : '' }}">
                                        {{ $isFavorited ? 'star' : 'star_border' }}
                                    </span>
                                </button>
                            </div>

                            <!-- Zutatenliste-Auszug (wie im Screenshot 2) -->
                            <p class="text-xs text-stone-600 mt-2 line-clamp-2 leading-relaxed">
                                {{ $ingredientsText }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Pagination -->
        <div class="mt-8 flex justify-center">
            {{ $recipes->links() }}
        </div>

    @else
        <!-- Leerzustand -->
        <div class="bg-white rounded-xl border border-stone-200 p-12 text-center max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-500 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">soup_kitchen</span>
            </div>
            <h3 class="text-lg font-bold text-stone-900 mb-2">Keine Rezepte gefunden</h3>
            <p class="text-sm text-stone-600 mb-6">
                Zu Ihren ausgewählten Suchbegriffen oder Filtern wurden keine Rezepte gefunden.
            </p>
            <div class="flex items-center justify-center space-x-3">
                <button wire:click="resetFilters" class="px-4 py-2 border border-stone-300 rounded-lg text-sm text-stone-700 hover:bg-stone-50 transition">
                    Filter zurücksetzen
                </button>
                <a href="{{ route('recipes.create') }}" class="px-4 py-2 bg-[#8ea92c] hover:bg-[#7e9626] text-white rounded-lg text-sm font-semibold shadow-xs transition flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>Neues Rezept anlegen</span>
                </a>
            </div>
        </div>
    @endif
</div>
