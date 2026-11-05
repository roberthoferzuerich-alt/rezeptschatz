<!DOCTYPE html>
<html lang="de" class="h-full bg-stone-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anmeldung – Rezeptbuch</title>

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
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#7a9c33',
                            600: '#688527',
                            700: '#506720',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg border border-stone-200">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-500/10 text-brand-600 mb-3">
                <span class="material-symbols-outlined text-4xl">menu_book</span>
            </div>
            <h2 class="text-3xl font-bold tracking-tight text-stone-900">Rezeptschatz</h2>
            <p class="mt-2 text-sm text-stone-600">Private Rezeptverwaltung</p>
        </div>

        @if ($errors->any())
            <div class="rounded-md bg-rose-50 p-4 border border-rose-200">
                <div class="flex">
                    <span class="material-symbols-outlined text-rose-500 mr-2 text-xl">error</span>
                    <div class="text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">E-Mail-Adresse</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" 
                           class="appearance-none rounded-none relative block w-full px-3 py-2.5 border border-stone-300 placeholder-stone-400 text-stone-900 rounded-t-md focus:outline-none focus:ring-brand-500 focus:border-brand-500 focus:z-10 sm:text-sm" 
                           placeholder="E-Mail-Adresse">
                </div>
                <div>
                    <label for="password" class="sr-only">Passwort</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2.5 border border-stone-300 placeholder-stone-400 text-stone-900 rounded-b-md focus:outline-none focus:ring-brand-500 focus:border-brand-500 focus:z-10 sm:text-sm" 
                           placeholder="Passwort">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" checked class="h-4 w-4 text-[#8ea92c] focus:ring-[#8ea92c] border-stone-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-stone-700">Angemeldet bleiben (dauerhaft)</label>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 shadow transition">
                    Anmelden
                </button>
            </div>
        </form>
    </div>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
</body>
</html>

