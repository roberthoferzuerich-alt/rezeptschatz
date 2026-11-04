<div>
    <!-- Oberer Filter- und Suchbereich -->
    <div class="bg-white rounded-xl shadow-sm border border-stone-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
            <!-- Suchfeld -->
            <div class="md:col-span-4 relative">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-xl pointer-events-none">search</span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Rezept oder Zutat suchen..." 
                       class="w-full pl-10 pr-4 py-2 border border-stone-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                @if ($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-2.5 text-stone-400 hover:text-stone-600">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                @endif
            </div>

            <!-- Sortierung -->
            <div class="md:col-span-3 flex items-center space-x-2">
                <span class="text-xs text-stone-500 whitespace-nowrap">Sortieren:</span>
                <select wire:model.live="sortBy" class="w-full py-2 px-3 border border-stone-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <option value="updated_at">Zuletzt geändert</option>
                    <option value="created_at">Kürzlich erstellt</option>
                    <option value="title">Titel (A-Z)</option>
                    <option value="total_time">Zubereitungszeit</option>
                </select>
            </div>

            <!-- Autoren-Filter (Familie) -->
            <div class="md:col-span-3 flex items-center space-x-1 bg-stone-100 p-1 rounded-lg">
                <button wire:click="$set('authorFilter', 'all')" 
                        class="flex-1 py-1.5 px-2 rounded-md text-xs font-semibold transition {{ $authorFilter === 'all' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    Alle
                </button>
                <button wire:click="$set('authorFilter', 'mine')" 
                        class="flex-1 py-1.5 px-2 rounded-md text-xs font-semibold transition {{ $authorFilter === 'mine' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    Meine
                </button>
                <button wire:click="$set('authorFilter', 'shared')" 
                        class="flex-1 py-1.5 px-2 rounded-md text-xs font-semibold transition {{ $authorFilter === 'shared' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                    Geteilt
                </button>
            </div>

            <!-- Schnellfilter Favoriten -->
            <div class="md:col-span-2 flex justify-end">
                <button wire:click="toggleFavoriteFilter" 
                        class="w-full flex items-center justify-center space-x-1.5 px-3 py-2 rounded-lg text-xs font-semibold border transition {{ $onlyFavorites ? 'bg-amber-500 border-amber-600 text-white shadow-sm' : 'bg-stone-50 border-stone-300 text-stone-700 hover:bg-stone-100' }}">
                    <span class="material-symbols-outlined text-base {{ $onlyFavorites ? 'text-white' : 'text-amber-500' }}">star</span>
                    <span>Favoriten</span>
                </button>
            </div>
        </div>

        <!-- Kategorien-Chips / Schnellfilter -->
        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center space-x-2 overflow-x-auto pb-1 scrollbar-thin">
            <button wire:click="selectCategory('')" 
                    class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition {{ $selectedCategory === '' ? 'bg-brand-600 text-white shadow-sm' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                Alle Kategorien ({{ $totalRecipeCount }})
            </button>
            @foreach ($categories as $cat)
                <button wire:click="selectCategory('{{ $cat->id }}')" 
                        class="px-3 py-1 rounded-full text-xs font-medium whitespace-nowrap transition flex items-center space-x-1 {{ $selectedCategory === $cat->id ? 'bg-brand-600 text-white shadow-sm' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    <span class="w-2 h-2 rounded-full" style="background-color: {{ $cat->color ?? '#7a9c33' }};"></span>
                    <span>{{ $cat->name }} ({{ $cat->recipes_count }})</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Rezept-Grid -->
    @if ($recipes->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($recipes as $recipe)
                @php
                    $isFavorited = $recipe->isFavoritedBy(Auth::user());
                    $isMyRecipe = ($recipe->user_id === $currentUserId);
                @endphp
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md border border-stone-200 overflow-hidden flex flex-col transition duration-150 group">
                    <!-- Bild oder Platzhalter -->
                    <a href="{{ route('recipes.show', $recipe->id) }}" class="block relative aspect-video bg-stone-100 overflow-hidden">
                        @if ($recipe->image_path)
                            <img src="{{ route('recipes.image', $recipe->id) }}" alt="{{ $recipe->title }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-stone-300 bg-stone-50">
                                <span class="material-symbols-outlined text-5xl">restaurant</span>
                                <span class="text-xs text-stone-400 mt-1">Kein Bild vorhanden</span>
                            </div>
                        @endif

                        <!-- Zeit-Badge -->
                        @php
                            $totalTime = ($recipe->prep_time_minutes ?? 0) + ($recipe->cook_time_minutes ?? 0);
                        @endphp
                        @if ($totalTime > 0)
                            <span class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-sm text-white text-xs px-2 py-0.5 rounded-md flex items-center space-x-1">
                                <span class="material-symbols-outlined text-xs">schedule</span>
                                <span>{{ $totalTime }}m</span>
                            </span>
                        @endif

                        <!-- Autoren-Badge (wer hat das Rezept erstellt?) -->
                        <span class="absolute top-2 left-2 {{ $isMyRecipe ? 'bg-brand-600/90 text-white' : 'bg-stone-800/80 text-stone-100' }} text-[11px] font-semibold px-2 py-0.5 rounded-md shadow-xs flex items-center space-x-1 backdrop-blur-xs">
                            <span class="material-symbols-outlined text-xs">{{ $isMyRecipe ? 'person' : 'group' }}</span>
                            <span>{{ $isMyRecipe ? 'Mein Rezept' : ($recipe->user?->name ?: 'Geteilt') }}</span>
                        </span>

                        <!-- Favoriten-Stern -->
                        <button wire:click.prevent="toggleFavorite('{{ $recipe->id }}')" 
                                title="{{ $isFavorited ? 'Aus persönlichen Favoriten entfernen' : 'Zu persönlichen Favoriten hinzufügen' }}" 
                                class="absolute top-2 right-2 p-1.5 rounded-full bg-white/80 hover:bg-white text-stone-400 hover:text-amber-500 shadow-sm transition">
                            <span class="material-symbols-outlined text-xl {{ $isFavorited ? 'text-amber-500 fill-current' : '' }}">
                                {{ $isFavorited ? 'star' : 'star_border' }}
                            </span>
                        </button>
                    </a>

                    <!-- Kachel-Inhalt -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Titel -->
                            <h3 class="font-bold text-stone-900 group-hover:text-brand-600 transition line-clamp-2 text-base">
                                <a href="{{ route('recipes.show', $recipe->id) }}">
                                    {{ $recipe->title }}
                                </a>
                            </h3>

                            <!-- Beschreibung -->
                            @if ($recipe->description)
                                <p class="text-xs text-stone-500 mt-1 line-clamp-2">
                                    {{ $recipe->description }}
                                </p>
                            @endif
                        </div>

                        <!-- Kategorien-Tags am unteren Kachelrand -->
                        <div class="mt-4 pt-3 border-t border-stone-100 flex items-center justify-between">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($recipe->categories->take(2) as $category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-brand-50 text-brand-700">
                                        {{ $category->name }}
                                    </span>
                                @empty
                                    <span class="text-[11px] text-stone-400 italic">Ohne Kategorie</span>
                                @endforelse
                                @if ($recipe->categories->count() > 2)
                                    <span class="text-[11px] text-stone-400">+{{ $recipe->categories->count() - 2 }}</span>
                                @endif
                            </div>

                            <a href="{{ route('recipes.show', $recipe->id) }}" class="text-xs text-brand-600 font-semibold hover:underline">
                                Details &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $recipes->links() }}
        </div>
    @else
        <!-- Leerzustand -->
        <div class="bg-white rounded-xl border border-stone-200 p-12 text-center max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl">soup_kitchen</span>
            </div>
            <h3 class="text-lg font-bold text-stone-900 mb-2">Keine Rezepte gefunden</h3>
            <p class="text-sm text-stone-600 mb-6">
                Zu Ihren ausgewählten Filtern wurden keine Rezepte gefunden.
            </p>
            <div class="flex items-center justify-center space-x-3">
                <button wire:click="resetFilters" class="px-4 py-2 border border-stone-300 rounded-lg text-sm text-stone-700 hover:bg-stone-50 transition">
                    Filter zurücksetzen
                </button>
                <a href="{{ route('recipes.create') }}" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold shadow transition flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>Neues Rezept anlegen</span>
                </a>
            </div>
        </div>
    @endif
</div>
