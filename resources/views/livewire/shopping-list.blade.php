<div class="max-w-4xl mx-auto space-y-6">
    <!-- Kopfzeile -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-stone-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#8ea92c] text-3xl">shopping_cart</span>
                <span>Einkaufsliste</span>
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1">
                {{ $openCount }} {{ $openCount === 1 ? 'Artikel' : 'Artikel' }} offen @if($completedCount > 0) • {{ $completedCount }} erledigt @endif
            </p>
        </div>

        <div class="flex items-center space-x-2">
            @if ($completedCount > 0)
                <button type="button" wire:click="clearCompleted" 
                        class="inline-flex items-center space-x-1 px-3 py-1.5 text-xs font-medium text-stone-700 bg-white border border-stone-300 rounded-lg hover:bg-stone-50 transition shadow-2xs">
                    <span class="material-symbols-outlined text-sm text-stone-500">done_all</span>
                    <span>Erledigte löschen</span>
                </button>
            @endif

            @if ($totalCount > 0)
                <button type="button" wire:click="clearAll" wire:confirm="Möchten Sie wirklich die gesamte Einkaufsliste leeren?" 
                        class="inline-flex items-center space-x-1 px-3 py-1.5 text-xs font-medium text-rose-700 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 transition shadow-2xs">
                    <span class="material-symbols-outlined text-sm">delete_sweep</span>
                    <span>Liste leeren</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Schnelleingabe für neue Artikel -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4 sm:p-5">
        <form wire:submit.prevent="addItem" class="flex flex-col sm:flex-row items-center gap-2">
            <div class="relative flex-1 w-full">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-lg pointer-events-none">add_shopping_cart</span>
                <input wire:model="newItemName" type="text" placeholder="Neuer Artikel (z. B. Butter, Milch, Äpfel)..." 
                       class="w-full pl-10 pr-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c] focus:border-[#8ea92c]">
            </div>

            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <input wire:model="newItemAmount" type="number" step="any" placeholder="Menge" 
                       class="w-24 px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c] text-center">
                <input wire:model="newItemUnit" type="text" placeholder="Einheit (g, Stk)" 
                       class="w-28 px-3 py-2 text-sm border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c]">
                <button type="submit" 
                        class="px-4 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-semibold rounded-lg shadow-xs transition shrink-0 cursor-pointer">
                    + Hinzufügen
                </button>
            </div>
        </form>
        @error('newItemName')
            <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
        @enderror
    </div>

    <!-- Filterleiste: Status & Kategorien -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
        <!-- Status-Filter -->
        <div class="inline-flex items-center p-1 bg-stone-200/70 rounded-lg text-xs font-semibold self-start">
            <button type="button" wire:click="$set('filter', 'open')" 
                    class="px-3 py-1.5 rounded-md transition {{ $filter === 'open' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                Offen ({{ $openCount }})
            </button>
            <button type="button" wire:click="$set('filter', 'all')" 
                    class="px-3 py-1.5 rounded-md transition {{ $filter === 'all' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                Alle ({{ $totalCount }})
            </button>
            <button type="button" wire:click="$set('filter', 'completed')" 
                    class="px-3 py-1.5 rounded-md transition {{ $filter === 'completed' ? 'bg-white text-stone-900 shadow-xs' : 'text-stone-600 hover:text-stone-900' }}">
                Erledigt ({{ $completedCount }})
            </button>
        </div>

        <!-- Kategorie-Filter-Chips -->
        @if ($availableCategories->count() > 1)
            <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 max-w-full scrollbar-none text-xs">
                <button type="button" wire:click="$set('selectedCategoryFilter', '')" 
                        class="px-2.5 py-1 rounded-full font-medium whitespace-nowrap transition {{ $selectedCategoryFilter === '' ? 'bg-[#8ea92c] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                    Alle Warengruppen
                </button>
                @foreach ($availableCategories as $cat)
                    <button type="button" wire:click="$set('selectedCategoryFilter', '{{ $cat }}')" 
                            class="px-2.5 py-1 rounded-full font-medium whitespace-nowrap transition {{ $selectedCategoryFilter === $cat ? 'bg-[#8ea92c] text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                        {{ $cat }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <!-- EINKAUFSLISTEN-ELEMENTE (NACH SUPERMARKT-KATEGORIEN GRUPPIERT) -->
    @if ($groupedItems->count() > 0)
        <div class="space-y-4">
            @foreach ($groupedItems as $categoryName => $items)
                <div class="bg-white rounded-2xl shadow-sm border border-stone-200 overflow-hidden">
                    <!-- Abteilungs-Kopfzeile -->
                    <div class="bg-stone-50/80 px-4 py-2 border-b border-stone-100 flex items-center justify-between text-xs font-bold text-stone-700">
                        <span class="flex items-center space-x-1.5">
                            <span class="material-symbols-outlined text-sm text-[#8ea92c]">label</span>
                            <span>{{ $categoryName }}</span>
                        </span>
                        <span class="text-stone-400 font-normal">({{ $items->count() }})</span>
                    </div>

                    <!-- Artikelliste -->
                    <ul class="divide-y divide-stone-100 text-sm">
                        @foreach ($items as $item)
                            <li class="px-4 py-2.5 flex items-center justify-between gap-3 hover:bg-stone-50/70 transition group {{ $item->is_completed ? 'bg-stone-50/40 text-stone-400' : 'text-stone-800' }}">
                                <div class="flex items-center space-x-3 flex-1 min-w-0 cursor-pointer select-none" wire:click="toggleItem('{{ $item->id }}')">
                                    <!-- Checkbox -->
                                    <div class="w-5 h-5 rounded-md border flex items-center justify-center transition shrink-0 {{ $item->is_completed ? 'bg-[#8ea92c] border-[#8ea92c] text-white' : 'border-stone-300 group-hover:border-[#8ea92c]' }}">
                                        @if ($item->is_completed)
                                            <span class="material-symbols-outlined text-sm leading-none font-bold">check</span>
                                        @endif
                                    </div>

                                    <!-- Menge & Name -->
                                    <div class="flex items-baseline space-x-2 truncate">
                                        @if ($item->formatted_amount)
                                            <span class="font-bold text-stone-900 shrink-0 text-xs sm:text-sm {{ $item->is_completed ? 'line-through text-stone-400 font-normal' : '' }}">
                                                {{ $item->formatted_amount }}
                                            </span>
                                        @endif
                                        <span class="truncate {{ $item->is_completed ? 'line-through text-stone-400' : 'font-medium' }}">
                                            {{ $item->name }}
                                        </span>
                                    </div>

                                    <!-- Herkunft (falls aus Rezept) -->
                                    @if ($item->recipe)
                                        <span class="hidden md:inline-block px-1.5 py-0.5 rounded text-[10px] bg-stone-100 text-stone-500 truncate max-w-xs" title="Aus Rezept: {{ $item->recipe->title }}">
                                            {{ $item->recipe->title }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Löschen-Button -->
                                <button type="button" wire:click="deleteItem('{{ $item->id }}')" 
                                        class="p-1 rounded text-stone-300 hover:text-red-500 hover:bg-stone-100 transition opacity-0 group-hover:opacity-100 focus:opacity-100" title="Artikel entfernen">
                                    <span class="material-symbols-outlined text-base">close</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @else
        <!-- Leerzustand -->
        <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center max-w-md mx-auto space-y-4">
            <div class="w-16 h-16 rounded-full bg-[#8ea92c]/10 text-[#8ea92c] flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-3xl">shopping_cart</span>
            </div>
            <div>
                <h3 class="text-base font-bold text-stone-900">Ihre Einkaufsliste ist leer</h3>
                <p class="text-xs text-stone-500 mt-1 leading-relaxed">
                    Fügen Sie oben eigene Einkäufe hinzu oder übernehmen Sie die Zutaten mit einem Klick direkt aus Ihren Rezepten oder dem Menüplaner!
                </p>
            </div>
            <a href="{{ route('recipes.index') }}" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs font-semibold rounded-lg shadow-xs transition">
                <span class="material-symbols-outlined text-sm">restaurant_menu</span>
                <span>Rezepte durchstöbern</span>
            </a>
        </div>
    @endif
</div>

