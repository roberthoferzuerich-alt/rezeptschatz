<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RezeptSchatz - App herunterladen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-950 via-emerald-950 to-stone-900 text-white min-h-screen py-10 px-4 sm:px-6 lg:px-8 flex flex-col justify-between">
    
    <div class="max-w-3xl mx-auto w-full my-auto">
        <!-- Logo / Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-600 rounded-3xl shadow-xl shadow-emerald-500/25 mb-4 transform hover:scale-105 transition">
                <!-- Kochmütze / Rezeptbuch SVG Icon -->
                <svg class="w-11 h-11 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">RezeptSchatz</h1>
            <p class="mt-2 text-base text-emerald-200/90 font-medium">Familien-Kochbuch & Rezeptverwaltung für unsere Familie</p>
            <div class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-xs text-gray-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Aktueller Flutter Build (Release 1.0) • 52.8 MB</span>
            </div>
        </div>

        <!-- Cards Container -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Android Card -->
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-3xl border border-white/10 flex flex-col justify-between shadow-2xl hover:border-emerald-500/40 transition">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-3 bg-emerald-500/20 text-emerald-400 rounded-2xl">
                            <!-- Android SVG Icon -->
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993s-.4483.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9994.4482.9994.9993s-.4483.9997-.9994.9997m11.4045-6.02l1.996-3.4567a.4158.4158 0 0 0-.1521-.5676.416.416 0 0 0-.568.1522L17.135 8.9482C15.6262 8.2616 13.8827 7.89 12 7.89c-1.8833 0-3.6267.3716-5.1355 1.0582L4.842 5.4493a.4162.4162 0 0 0-.568-.1522.416.416 0 0 0-.1521.5676l1.996 3.4567C2.6888 11.2863 0 15.1587 0 19.64h24c0-4.4813-2.6888-8.3537-6.1185-10.3186"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">Android-Geräte</h2>
                            <p class="text-xs text-emerald-300 font-medium">Samsung, Google Pixel, Xiaomi, Tablets</p>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-200 mb-4">
                        Installiere die native RezeptSchatz Flutter-App mit voller Offline-Nutzung, Bild-Erkennung und schnellem MySQL-Sync.
                    </p>

                    <ol class="text-xs text-gray-300 space-y-2.5 mb-6 bg-black/25 p-4 rounded-2xl border border-white/5">
                        <li class="flex items-start space-x-2.5">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[11px]">1</span>
                            <span>Auf <strong>APK herunterladen</strong> tippen (Download startet).</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[11px]">2</span>
                            <span>Heruntergeladene Datei öffnen & <em>„Installieren“</em> antippen (ggf. <em>„Aus dieser Quelle zulassen“</em> aktivieren).</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[11px]">3</span>
                            <span>App öffnen – über 760 Rezepte & Bilder direkt durchsuchen!</span>
                        </li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <a href="{{ url('/download/apk') }}" download="RezeptSchatz.apk" class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center space-x-2.5 transition text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>RezeptSchatz APK herunterladen</span>
                        <span class="text-xs bg-emerald-700/60 px-2 py-0.5 rounded-full font-normal">52.8 MB</span>
                    </a>
                    <p class="text-center text-[11px] text-gray-400">Direkter Download-Link • Sicher & Virengeprüft</p>
                </div>
            </div>

            <!-- Apple Card -->
            <div class="bg-white/10 backdrop-blur-md p-6 rounded-3xl border border-white/10 flex flex-col justify-between shadow-2xl hover:border-blue-500/40 transition">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-3 bg-blue-500/20 text-blue-400 rounded-2xl">
                            <!-- Apple SVG Icon -->
                            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 0.92-2.85-.9.04-2 .6-2.65 1.35-.58.66-1.09 1.73-.95 2.76.99.08 2.06-.51 2.68-1.26z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">Apple iPhone & iPad</h2>
                            <p class="text-xs text-blue-300 font-medium">iOS / Safari Web-App (PWA)</p>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-200 mb-4">
                        Öffne RezeptSchatz direkt in Safari und füge es als Home-Bildschirm-App ohne störende Browserleiste hinzu.
                    </p>

                    <ol class="text-xs text-gray-300 space-y-2.5 mb-6 bg-black/25 p-4 rounded-2xl border border-white/5">
                        <li class="flex items-start space-x-2.5">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center text-[11px]">1</span>
                            <span>Unten auf <strong>Web-App im Browser öffnen</strong> tippen (in Safari).</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center text-[11px]">2</span>
                            <span>Unten in der Menüleiste auf das <strong>Teilen-Symbol</strong> <span class="inline-block px-1.5 py-0.5 bg-white/15 rounded text-[11px] font-mono">⎋</span> tippen.</span>
                        </li>
                        <li class="flex items-start space-x-2.5">
                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 font-bold flex items-center justify-center text-[11px]">3</span>
                            <span>Etwas nach unten wischen und <strong>„Zum Home-Bildschirm“</strong> auswählen.</span>
                        </li>
                    </ol>
                </div>

                <div class="space-y-2">
                    <a href="{{ url('/') }}" class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg shadow-blue-600/30 flex items-center justify-center space-x-2.5 transition text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span>Web-App im Browser öffnen</span>
                    </a>
                    <p class="text-center text-[11px] text-gray-400">Keine Installation im App Store nötig</p>
                </div>
            </div>

        </div>

        <!-- Info Box Server -->
        <div class="mt-8 bg-black/20 backdrop-blur-sm border border-white/5 rounded-2xl p-4 text-xs text-gray-300 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center space-x-3">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400"></div>
                <span><strong>Server:</strong> Raspberry Pi 5 (rhz.internet-box.ch:8449)</span>
            </div>
            <span class="text-gray-400">MariaDB • Laravel 13 • Flutter 3</span>
        </div>

        <!-- Footer Links -->
        <div class="mt-8 text-center text-xs text-gray-400 flex flex-wrap items-center justify-center gap-4">
            <a href="{{ url('/') }}" class="hover:text-emerald-300 transition">Zur Rezeptübersicht</a>
            <span>&bull;</span>
            <a href="{{ route('login') }}" class="hover:text-emerald-300 transition">Anmelden / Login</a>
            <span>&bull;</span>
            <a href="{{ route('download.apk') }}" class="hover:text-emerald-300 transition">Direkt-Download APK</a>
        </div>
    </div>

</body>
</html>
