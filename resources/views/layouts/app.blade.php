<!DOCTYPE html>
<html lang="de" class="h-full bg-stone-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Rezeptbuch' }} – Private Rezeptverwaltung</title>

    <!-- Tailwind CSS (CDN für garantierte lokale Direktfunktion in Laragon) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f4f8ec',
                            100: '#e7f0d4',
                            200: '#d1e2ad',
                            300: '#b4cf7d',
                            400: '#99bc53',
                            500: '#7a9c33',
                            600: '#688527',
                            700: '#506720',
                            800: '#41521d',
                            900: '#37451c',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Google Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    @livewireStyles
</head>
<body class="h-full flex flex-col font-sans antialiased text-stone-800">
    <!-- Kopfbereich / Navigation -->
    <header class="bg-brand-600 text-white shadow-md sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo & Titel -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('recipes.index') }}" class="flex items-center space-x-2 text-xl font-bold tracking-tight text-white hover:text-brand-100 transition">
                        <span class="material-symbols-outlined text-2xl">menu_book</span>
                        <span>Rezeptbuch</span>
                    </a>
                </div>

                <!-- Navigation Links (Desktop) -->
                <nav class="hidden md:flex items-center space-x-4">
                    <a href="{{ route('recipes.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('recipes.index') ? 'bg-brand-700 text-white' : 'text-brand-100 hover:bg-brand-500 hover:text-white' }} transition">
                        Rezepte
                    </a>
                    <a href="{{ route('categories.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('categories.index') ? 'bg-brand-700 text-white' : 'text-brand-100 hover:bg-brand-500 hover:text-white' }} transition">
                        Kategorien
                    </a>
                    <a href="{{ route('recipes.create') }}" class="flex items-center space-x-1 bg-white text-brand-700 hover:bg-brand-50 px-3 py-2 rounded-md text-sm font-semibold shadow-sm transition">
                        <span class="material-symbols-outlined text-base">add</span>
                        <span>Neues Rezept</span>
                    </a>
                </nav>

                <!-- Benutzer-Menü / Logout -->
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex items-center space-x-2 text-sm text-brand-100">
                        <span class="material-symbols-outlined text-lg">account_circle</span>
                        <span>{{ Auth::user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" title="Abmelden" class="flex items-center space-x-1 text-brand-100 hover:text-white hover:bg-brand-700 px-2.5 py-1.5 rounded text-sm transition">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span class="hidden sm:inline">Abmelden</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Leiste -->
        <div class="md:hidden border-t border-brand-500 bg-brand-700 px-4 py-2 flex justify-around">
            <a href="{{ route('recipes.index') }}" class="text-xs text-brand-100 hover:text-white flex flex-col items-center">
                <span class="material-symbols-outlined text-lg">restaurant_menu</span>
                <span>Rezepte</span>
            </a>
            <a href="{{ route('categories.index') }}" class="text-xs text-brand-100 hover:text-white flex flex-col items-center">
                <span class="material-symbols-outlined text-lg">label</span>
                <span>Kategorien</span>
            </a>
            <a href="{{ route('recipes.create') }}" class="text-xs text-brand-100 hover:text-white flex flex-col items-center">
                <span class="material-symbols-outlined text-lg">add_circle</span>
                <span>Erstellen</span>
            </a>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2 text-emerald-800">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-md shadow-sm mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2 text-rose-800">
                    <span class="material-symbols-outlined text-xl">error</span>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Hauptinhalt -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Fussbereich -->
    <footer class="bg-white border-t border-stone-200 py-4 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-stone-500">
            <span>Rezeptbuch &bull; Private Rezeptverwaltung &bull; Verbunden mit MySQL</span>
        </div>
    </footer>

    @livewireScripts
</body>
</html>

