<div class="max-w-7xl mx-auto space-y-6">
    <!-- Kopfzeile -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-stone-200 pb-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#8ea92c] text-3xl">calendar_month</span>
                <span>Wochen-Menüplaner</span>
            </h1>
            <p class="text-xs sm:text-sm text-stone-500 mt-1">
                Planen Sie Ihre Mahlzeiten für die Woche und übertragen Sie alle benötigten Zutaten mit einem Klick auf die Einkaufsliste.
            </p>
        </div>

        <!-- Aktionen: Woche auf Einkaufsliste -->
        <div class="flex items-center space-x-2">
            @if ($totalPlannedMeals > 0)
                <button type="button" wire:click="sendWeekToShoppingList" 
                        class="inline-flex items-center space-x-2 px-4 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-xs transition cursor-pointer">
                    <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                    <span>Woche auf Einkaufsliste ({{ $totalPlannedMeals }} Gerichte)</span>
                </button>
            @endif

            <a href="{{ route('shopping-list.index') }}" class="inline-flex items-center space-x-1.5 px-3 py-2 border border-stone-300 hover:bg-stone-50 text-stone-700 text-xs sm:text-sm font-medium rounded-lg shadow-2xs transition">
                <span class="material-symbols-outlined text-base text-[#8ea92c]">shopping_cart</span>
                <span class="hidden sm:inline">Zur Einkaufsliste</span>
            </a>
        </div>
    </div>

    <!-- Wochen-Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div class="flex items-center space-x-2">
            <button type="button" wire:click="previousWeek" 
                    class="p-2 rounded-lg border border-stone-300 text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition" title="Vorherige Woche">
                <span class="material-symbols-outlined text-base">chevron_left</span>
            </button>
            <button type="button" wire:click="currentWeek" 
                    class="px-3 py-1.5 text-xs font-semibold text-stone-700 bg-stone-100 hover:bg-stone-200 rounded-lg transition">
                Aktuelle Woche
            </button>
            <button type="button" wire:click="nextWeek" 
                    class="p-2 rounded-lg border border-stone-300 text-stone-600 hover:text-stone-900 hover:bg-stone-50 transition" title="Nächste Woche">
                <span class="material-symbols-outlined text-base">chevron_right</span>
            </button>
        </div>

        <div class="text-sm sm:text-base font-bold text-stone-900 flex items-center space-x-2">
            <span class="material-symbols-outlined text-[#8ea92c]">date_range</span>
            <span>Woche {{ $weekLabel }}</span>
        </div>
    </div>

    <!-- 7-TAGE WOCHENÜBERSICHT -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-3">
        @foreach ($days as $day)
            @php
                $dateKey = $day['date_string'];
                $lunchMeals = $groupedPlans[$dateKey]['lunch'] ?? [];
                $dinnerMeals = $groupedPlans[$dateKey]['dinner'] ?? [];
            @endphp
            <div class="bg-white rounded-2xl border transition duration-150 flex flex-col {{ $day['is_today'] ? 'border-[#8ea92c] ring-2 ring-[#8ea92c]/20 shadow-sm' : 'border-stone-200 shadow-2xs' }}">
                <!-- Tages-Header -->
                <div class="p-3 border-b border-stone-100 flex items-center justify-between {{ $day['is_today'] ? 'bg-[#8ea92c]/10 text-stone-900 rounded-t-2xl' : 'bg-stone-50/70 text-stone-800' }}">
                    <div>
                        <span class="block font-bold text-xs uppercase tracking-wider {{ $day['is_today'] ? 'text-[#7a9323]' : 'text-stone-600' }}">
                            {{ $day['day_name'] }}
                        </span>
                        <span class="text-[11px] text-stone-400 font-medium">
                            {{ $day['short_date'] }}
                        </span>
                    </div>
                    @if ($day['is_today'])
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#8ea92c] text-white">
                            Heute
                        </span>
                    @endif
                </div>

                <!-- Mahlzeiten des Tages -->
                <div class="p-2.5 flex-1 space-y-3 flex flex-col justify-between text-xs">
                    
                    <!-- 1. Mittagessen -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <span>Mittag</span>
                            <button type="button" wire:click="openAddModal('{{ $dateKey }}', 'lunch')" 
                                    class="text-[#8ea92c] hover:underline" title="Gericht hinzufügen">
                                + Einplanen
                            </button>
                        </div>

                        @forelse ($lunchMeals as $meal)
                            <div class="p-2 rounded-xl bg-stone-50 border border-stone-200/80 space-y-1 group relative">
                                <button type="button" wire:click="removeMeal('{{ $meal->id }}')" 
                                        class="absolute top-1 right-1 text-stone-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition" title="Entfernen">
                                    <span class="material-symbols-outlined text-xs">close</span>
                                </button>

                                @if ($meal->recipe)
                                    <a href="{{ route('recipes.show', $meal->recipe->id) }}" class="font-semibold text-stone-800 hover:text-[#8ea92c] transition block line-clamp-2 leading-snug">
                                        {{ $meal->recipe->title }}
                                    </a>
                                    <span class="text-[10px] text-stone-400 block">{{ $meal->portions }} Portionen</span>
                                @else
                                    <span class="font-medium text-stone-800 block line-clamp-2 leading-snug">
                                        {{ $meal->custom_title }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="p-2 border border-dashed border-stone-200 rounded-xl text-center text-[11px] text-stone-300">
                                Keine Mahlzeit
                            </div>
                        @endforelse
                    </div>

                    <!-- 2. Abendessen -->
                    <div class="space-y-1.5 pt-2 border-t border-stone-100">
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-stone-400">
                            <span>Abend</span>
                            <button type="button" wire:click="openAddModal('{{ $dateKey }}', 'dinner')" 
                                    class="text-[#8ea92c] hover:underline" title="Gericht hinzufügen">
                                + Einplanen
                            </button>
                        </div>

                        @forelse ($dinnerMeals as $meal)
                            <div class="p-2 rounded-xl bg-stone-50 border border-stone-200/80 space-y-1 group relative">
                                <button type="button" wire:click="removeMeal('{{ $meal->id }}')" 
                                        class="absolute top-1 right-1 text-stone-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition" title="Entfernen">
                                    <span class="material-symbols-outlined text-xs">close</span>
                                </button>

                                @if ($meal->recipe)
                                    <a href="{{ route('recipes.show', $meal->recipe->id) }}" class="font-semibold text-stone-800 hover:text-[#8ea92c] transition block line-clamp-2 leading-snug">
                                        {{ $meal->recipe->title }}
                                    </a>
                                    <span class="text-[10px] text-stone-400 block">{{ $meal->portions }} Portionen</span>
                                @else
                                    <span class="font-medium text-stone-800 block line-clamp-2 leading-snug">
                                        {{ $meal->custom_title }}
                                    </span>
                                @endif
                            </div>
                        @empty
                            <div class="p-2 border border-dashed border-stone-200 rounded-xl text-center text-[11px] text-stone-300">
                                Keine Mahlzeit
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        @endforeach
    </div>

    <!-- MODAL FÜR REZEPT-AUSWAHL -->
    @if ($showRecipeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-stone-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-stone-200 max-w-lg w-full p-6 space-y-4 relative">
                <!-- Schließen-Button -->
                <button type="button" wire:click="closeAddModal" class="absolute top-4 right-4 text-stone-400 hover:text-stone-700">
                    <span class="material-symbols-outlined text-xl">close</span>
                </button>

                <div>
                    <h3 class="text-base sm:text-lg font-bold text-stone-900">
                        Gericht einplanen
                    </h3>
                    <p class="text-xs text-stone-500 mt-0.5">
                        Für {{ Carbon\Carbon::parse($modalDate)->isoFormat('dddd, D. MMMM') }} ({{ $modalMealType === 'lunch' ? 'Mittagessen' : 'Abendessen' }})
                    </p>
                </div>

                <!-- Portionen für dieses geplante Menü -->
                <div class="flex items-center justify-between p-3 bg-stone-50 rounded-xl border border-stone-200 text-xs">
                    <span class="font-medium text-stone-700">Portionen für dieses Essen:</span>
                    <div class="inline-flex items-center space-x-2">
                        <input wire:model="modalPortions" type="number" min="1" max="50" class="w-16 px-2 py-1 text-center font-bold text-sm border border-stone-300 rounded-md focus:ring-1 focus:ring-[#8ea92c]">
                        <span class="text-stone-500">Pers.</span>
                    </div>
                </div>

                <!-- 1. Aus Rezeptschatz wählen -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                        Rezept aus Sammlung wählen
                    </label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-stone-400 text-sm">search</span>
                        <input wire:model.live.debounce.200ms="recipeSearch" type="text" placeholder="Rezept suchen..." 
                               class="w-full pl-9 pr-3 py-1.5 text-xs border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c]">
                    </div>

                    <div class="max-h-52 overflow-y-auto divide-y divide-stone-100 border border-stone-200 rounded-xl">
                        @forelse ($searchRecipes as $r)
                            <div class="p-2.5 flex items-center justify-between hover:bg-stone-50 transition cursor-pointer" wire:click="assignRecipe('{{ $r->id }}')">
                                <div class="flex items-center space-x-2.5 truncate">
                                    @if ($r->image_path)
                                        <img src="{{ route('recipes.image', $r->id) }}" alt="{{ $r->title }}" class="w-8 h-8 rounded-lg object-cover shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-stone-100 flex items-center justify-center text-stone-400 shrink-0">
                                            <span class="material-symbols-outlined text-base">restaurant</span>
                                        </div>
                                    @endif
                                    <div class="truncate text-xs">
                                        <span class="font-semibold text-stone-800 block truncate">{{ $r->title }}</span>
                                        @if ($r->cook_time_minutes)
                                            <span class="text-[10px] text-stone-400">{{ $r->cook_time_minutes }} Min.</span>
                                        @endif
                                    </div>
                                </div>
                                <button type="button" class="px-2.5 py-1 bg-[#8ea92c]/10 text-[#8ea92c] hover:bg-[#8ea92c] hover:text-white rounded-md text-[11px] font-semibold transition shrink-0">
                                    Auswählen
                                </button>
                            </div>
                        @empty
                            <p class="p-4 text-xs text-stone-400 text-center italic">Keine Rezepte gefunden.</p>
                        @endforelse
                    </div>
                </div>

                <!-- 2. Oder freies Gericht eintragen -->
                <div class="pt-2 border-t border-stone-100 space-y-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">
                        Oder freies Gericht ohne Rezept eintragen
                    </label>
                    <div class="flex items-center space-x-2">
                        <input wire:model="customTitle" type="text" placeholder="z. B. Restaurant, Pizza bestellen, Resteessen..." 
                               class="flex-1 px-3 py-1.5 text-xs border border-stone-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#8ea92c]">
                        <button type="button" wire:click="assignCustomMeal" 
                                class="px-3 py-1.5 bg-stone-800 hover:bg-stone-900 text-white text-xs font-semibold rounded-lg shadow-2xs transition shrink-0 cursor-pointer">
                            Eintragen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
