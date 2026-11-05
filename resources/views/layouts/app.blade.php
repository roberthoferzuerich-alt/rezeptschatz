<!DOCTYPE html>
<html lang="de" class="h-full bg-stone-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Rezeptschatz' }} – Private Rezeptverwaltung</title>

    <!-- PWA / Tablet-App Konfiguration -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#8ea92c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Rezeptschatz">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512x512.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">

    <!-- Tailwind CSS (CDN für garantierte lokale Direktfunktion) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cookmate: {
                            DEFAULT: '#8ea92c',
                            dark: '#7e9626',
                            light: '#9bb730',
                        },
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
    <!-- COOKmate-Style Header-Navigation -->
    <header class="bg-white border-b border-stone-200 sticky top-0 z-40 shadow-xs" x-data="{ mobileOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Zeile 1: Logo & Titel (links), Desktop-Nav (ab xl), User-Konto (ab md) & Mobile Burger (< md) -->
            <div class="flex items-center justify-between h-14">
                
                <!-- Logo & Brand (links) -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('recipes.index') }}" class="flex items-center space-x-2 text-stone-800 hover:text-[#8ea92c] transition font-bold text-lg tracking-tight">
                        <span class="w-8 h-8 rounded-lg bg-[#8ea92c] text-white flex items-center justify-center shadow-xs">
                            <span class="material-symbols-outlined text-xl">menu_book</span>
                        </span>
                        <span class="hidden sm:inline">Rezeptschatz</span>
                    </a>
                </div>

                <!-- Desktop Navigation Links (nur auf großen Bildschirmen ab xl direkt in Zeile 1) -->
                <nav class="hidden xl:flex items-center space-x-5 text-sm text-stone-700 font-medium">
                    @include('layouts.navigation-links')
                    <!-- Trennlinie vertikal | -->
                    <div class="h-6 w-px bg-stone-300"></div>
                </nav>

                <!-- Rechte Seite: User Dropdown (ab md) & Mobile Burger (< md) -->
                <div class="flex items-center space-x-2">
                    <!-- User Dropdown (ab Tablet md sichtbar) -->
                    <div class="hidden md:block relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open" 
                                class="flex items-center space-x-2 py-1.5 px-2 rounded-lg hover:bg-stone-100 text-stone-800 transition focus:outline-none">
                            <span class="w-7 h-7 rounded-full bg-stone-200 text-stone-500 flex items-center justify-center overflow-hidden">
                                <span class="material-symbols-outlined text-2xl" style="font-size: 26px; line-height: 1;">account_circle</span>
                            </span>
                            <span class="font-medium text-sm text-stone-800">{{ Auth::user()->name }}</span>
                            <span class="text-stone-400 font-normal text-xs select-none">&gt;</span>
                        </button>

                        <!-- Das originale grüne Fenster "Dein Konto" -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 transform scale-95 -translate-y-2"
                             x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                             x-transition:leave-end="opacity-0 transform scale-95 -translate-y-2"
                             class="absolute right-0 mt-1 w-72 bg-[#8ea92c] text-white rounded-xl shadow-2xl p-5 z-50"
                             style="display: none;">
                            
                            <h3 class="text-white font-bold text-base mb-3 tracking-wide">
                                Dein Konto
                            </h3>

                            <ul class="space-y-2.5 text-sm font-medium text-white/95">
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'account']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>Account-Informationen</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'personal']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>Persönliche Informationen</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'password']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>Passwort ändern</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'credits']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>Credits</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'notifications']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>E-Mail-Benachrichtigungen</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'settings']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>COOKmate-Einstellungen</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'header-image']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>Ändere das Kopfzeilenbild</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('account.index', ['tab' => 'close-account']) }}" 
                                       @click="open = false"
                                       class="flex items-center space-x-2.5 hover:underline transition">
                                        <span class="text-base leading-none">•</span>
                                        <span>Schließe deinen Account</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- Abmelden-Option -->
                            <div class="border-t border-white/25 pt-3 mt-4">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full flex items-center justify-between text-xs font-semibold text-white/95 hover:text-white bg-black/15 hover:bg-black/25 px-3 py-2 rounded-lg transition">
                                        <span>Abmelden</span>
                                        <span class="material-symbols-outlined text-sm">logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Hamburger Button (< md) -->
                    <div class="flex items-center md:hidden space-x-2">
                        <button type="button" @click="mobileOpen = !mobileOpen" 
                                class="p-2 rounded-lg text-stone-600 hover:text-stone-900 hover:bg-stone-100 focus:outline-none">
                            <span class="material-symbols-outlined text-2xl" x-text="mobileOpen ? 'close' : 'menu'">menu</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Zeile 2: Tablet-Navigation (EINE ZEILE UNTER DEM TITEL REZEPTSCHATZ für md bis xl) -->
            <nav class="hidden md:flex xl:hidden items-center space-x-6 py-2 border-t border-stone-100 text-sm text-stone-700 font-medium">
                @include('layouts.navigation-links')
            </nav>
        </div>

        <!-- Mobile Drawer Menü -->
        <div x-show="mobileOpen" 
             x-transition 
             class="md:hidden border-t border-stone-200 bg-white px-4 pt-3 pb-6 space-y-4 shadow-lg text-stone-700" 
             style="display: none;">
            
            <!-- Benutzer Info Mobile -->
            <div class="flex items-center space-x-3 pb-3 border-b border-stone-200">
                <span class="w-8 h-8 rounded-full bg-stone-300 text-stone-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl text-stone-400">account_circle</span>
                </span>
                <div>
                    <div class="font-semibold text-sm text-stone-900">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-stone-500">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="space-y-1 text-sm font-medium">
                <div class="text-xs uppercase text-stone-400 font-bold px-2 py-1">Rezepte</div>
                <a href="{{ route('recipes.index') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100">Liste der Rezepte</a>
                <a href="{{ route('recipes.index', ['author' => 'shared']) }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100">Rezepte meiner Freunde</a>
                <a href="{{ route('recipes.index', ['fav' => 1]) }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between">
                    <span>Lesezeichen (Favoriten)</span>
                    <span class="material-symbols-outlined text-amber-500 text-sm">star</span>
                </a>
                <a href="{{ route('recipes.create') }}" class="block px-3 py-2 rounded-lg bg-stone-100 text-[#8ea92c] font-bold flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base">add</span>
                    <span>Rezept erstellen</span>
                </a>
                <a href="{{ route('recipes.scan') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between text-stone-800">
                    <span>Scann ein Rezept</span>
                    <span class="material-symbols-outlined text-[#8ea92c] text-base">document_scanner</span>
                </a>
                <a href="{{ route('recipes.import.web') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between text-stone-800">
                    <span>Aus dem Web (URL) importieren</span>
                    <span class="material-symbols-outlined text-[#8ea92c] text-base">public</span>
                </a>
                <a href="{{ route('recipes.chatgpt') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between text-stone-800">
                    <span>Mit KI & Kühlschrank kochen</span>
                    <span class="text-[10px] uppercase font-bold bg-emerald-50 text-emerald-600 px-1.5 py-0.5 rounded">KI</span>
                </a>
                <a href="{{ route('recipes.import') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between text-stone-800">
                    <span>COOKmate-Backup importieren</span>
                    <span class="material-symbols-outlined text-stone-400 text-base">folder_zip</span>
                </a>
                <a href="{{ route('shopping-list.index') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between text-stone-800 font-semibold">
                    <span>Einkaufsliste</span>
                    <span class="material-symbols-outlined text-[#8ea92c] text-base">shopping_cart</span>
                </a>
                <a href="{{ route('meal-planner.index') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100 flex items-center justify-between text-stone-800 font-semibold">
                    <span>Wochen-Menüplaner</span>
                    <span class="material-symbols-outlined text-[#8ea92c] text-base">calendar_month</span>
                </a>
                <a href="{{ route('categories.index') }}" class="block px-3 py-2 rounded-lg hover:bg-stone-100">Kategorien</a>
            </div>

            <div class="pt-2 border-t border-stone-200 space-y-1 text-sm font-medium">
                <div class="text-xs uppercase text-stone-400 font-bold px-2 py-1">Dein Konto</div>
                <a href="{{ route('account.index', ['tab' => 'account']) }}" class="block px-3 py-1.5 rounded-lg hover:bg-stone-100">• Account-Informationen</a>
                <a href="{{ route('account.index', ['tab' => 'personal']) }}" class="block px-3 py-1.5 rounded-lg hover:bg-stone-100">• Persönliche Informationen</a>
                <a href="{{ route('account.index', ['tab' => 'password']) }}" class="block px-3 py-1.5 rounded-lg hover:bg-stone-100">• Passwort ändern</a>
                <a href="{{ route('account.index', ['tab' => 'credits']) }}" class="block px-3 py-1.5 rounded-lg hover:bg-stone-100">• Credits</a>
                <a href="{{ route('account.index', ['tab' => 'settings']) }}" class="block px-3 py-1.5 rounded-lg hover:bg-stone-100">• Einstellungen</a>
            </div>

            <div class="pt-3 border-t border-stone-200">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-sm transition">
                        <span class="material-symbols-outlined text-lg">logout</span>
                        <span>Abmelden</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-md shadow-xs mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-2 text-emerald-800">
                    <span class="material-symbols-outlined text-xl">check_circle</span>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-md shadow-xs mb-4 flex items-center justify-between">
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
            <span>Rezeptschatz &bull; Private Rezeptverwaltung &bull; Verbunden mit MariaDB</span>
        </div>
    </footer>

    @livewireScripts
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>
