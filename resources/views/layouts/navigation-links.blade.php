<!-- 1. Rezepte > -->
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseenter="open = true" @mouseleave="open = false">
    <button type="button" @click="open = !open" class="flex items-center hover:text-stone-900 transition py-2 xl:py-4 focus:outline-none">
        <span>Rezepte</span>
        <span class="text-stone-400 font-normal ml-1 text-xs select-none">&gt;</span>
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute left-0 mt-1 w-60 bg-white rounded-b-xl shadow-xl border border-stone-200/80 py-2 z-50 text-stone-700"
         style="display: none;">
        <div class="h-0.5 bg-[#8ea92c] -mt-2 mb-1 rounded-t"></div>
        <a href="{{ route('recipes.index') }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Liste der Rezepte
        </a>
        <a href="{{ route('recipes.index', ['author' => 'shared']) }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Rezepte meiner Freunde
        </a>
        <a href="{{ route('recipes.index') }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Neues Rezept suchen
        </a>

        <hr class="my-1.5 border-stone-100">

        <a href="{{ route('recipes.create') }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] font-semibold text-stone-800 transition">
            <span>Rezept erstellen</span>
            <span class="material-symbols-outlined text-base text-[#8ea92c]">add_circle</span>
        </a>
        <a href="{{ route('recipes.scan') }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            <span>Scann ein Rezept</span>
            <span class="text-[10px] uppercase font-bold bg-[#8ea92c]/15 text-[#8ea92c] px-1.5 py-0.5 rounded">Neu</span>
        </a>
        <a href="{{ route('recipes.chatgpt') }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            <span>Mit KI & Kühlschrank kochen</span>
            <span class="text-[10px] uppercase font-bold bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded">KI</span>
        </a>
        <a href="{{ route('recipes.import.web') }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            <span>Importiere Rezepte</span>
            <span class="text-[10px] uppercase font-bold bg-[#8ea92c]/15 text-[#8ea92c] px-1.5 py-0.5 rounded">Neu</span>
        </a>

        <hr class="my-1.5 border-stone-100">

        <a href="{{ route('categories.index') }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Kategorien
        </a>
        <a href="{{ route('tags.index') }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            <span>Schlagwörter</span>
            <span class="text-[10px] uppercase font-bold bg-[#8ea92c]/15 text-[#8ea92c] px-1.5 py-0.5 rounded">Neu</span>
        </a>
        <a href="{{ route('account.index', ['tab' => 'help']) }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            <span>Verknüpfte Rezepte</span>
            <span class="text-[10px] uppercase font-bold bg-stone-100 text-stone-500 px-1.5 py-0.5 rounded">In Kürze</span>
        </a>
    </div>
</div>

<!-- 2. Neues Rezept suchen > -->
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseenter="open = true" @mouseleave="open = false">
    <button type="button" @click="open = !open" class="flex items-center hover:text-stone-900 transition py-2 xl:py-4 focus:outline-none">
        <span>Neues Rezept suchen</span>
        <span class="text-stone-400 font-normal ml-1 text-xs select-none">&gt;</span>
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute left-0 mt-1 w-52 bg-white rounded-b-xl shadow-xl border border-stone-200/80 py-2 z-50 text-stone-700"
         style="display: none;">
        <div class="h-0.5 bg-[#8ea92c] -mt-2 mb-1 rounded-t"></div>
        <a href="{{ route('recipes.index') }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Im Rezeptschatz suchen
        </a>
        <a href="{{ route('recipes.import.web') }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition font-semibold text-stone-800">
            <span>Aus dem Internet (URL)</span>
            <span class="material-symbols-outlined text-[#8ea92c] text-base">public</span>
        </a>
        <a href="{{ route('recipes.index', ['fav' => 1]) }}" class="flex items-center justify-between px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            <span>Lesezeichen</span>
            <span class="material-symbols-outlined text-amber-500 text-base">star</span>
        </a>
    </div>
</div>

<!-- 3. Einkaufsliste -->
<a href="{{ route('shopping-list.index') }}" class="hover:text-stone-900 transition py-2 xl:py-4 flex items-center space-x-1 {{ request()->routeIs('shopping-list.*') ? 'text-[#8ea92c] font-bold' : '' }}" title="Einkaufsliste">
    <span>Einkaufsliste</span>
</a>

<!-- 4. Menüplaner -->
<a href="{{ route('meal-planner.index') }}" class="hover:text-stone-900 transition py-2 xl:py-4 flex items-center space-x-1 {{ request()->routeIs('meal-planner.*') ? 'text-[#8ea92c] font-bold' : '' }}" title="Menüplaner">
    <span>Menüplaner</span>
</a>

<!-- 5. Mehr > -->
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseenter="open = true" @mouseleave="open = false">
    <button type="button" @click="open = !open" class="flex items-center hover:text-stone-900 transition py-2 xl:py-4 focus:outline-none">
        <span>Mehr</span>
        <span class="text-stone-400 font-normal ml-1 text-xs select-none">&gt;</span>
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute left-0 mt-1 w-56 bg-white rounded-b-xl shadow-xl border border-stone-200/80 py-2 z-50 text-stone-700"
         style="display: none;">
        <div class="h-0.5 bg-[#8ea92c] -mt-2 mb-1 rounded-t"></div>
        <a href="{{ route('account.index', ['tab' => 'settings']) }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Einstellungen
        </a>
        <a href="{{ route('account.index', ['tab' => 'news']) }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Neuigkeiten
        </a>
        <a href="{{ route('account.index', ['tab' => 'websites']) }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Unterstützte Webseiten
        </a>
        <a href="{{ route('account.index', ['tab' => 'credits']) }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Blog
        </a>
        <a href="{{ route('account.index', ['tab' => 'help']) }}" class="block px-4 py-2 hover:bg-stone-50 hover:text-[#8ea92c] transition">
            Hilfe und Support
        </a>
    </div>
</div>

