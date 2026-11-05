<!DOCTYPE html>
<html lang="de" class="h-full bg-stone-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $recipe->title }} – Rezeptschatz Druckansicht</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        @media print {
            body {
                background: white !important;
                color: #111 !important;
                font-size: 10pt !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .page-break-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            a {
                text-decoration: none !important;
                color: inherit !important;
            }
        }
    </style>
</head>
<body class="font-sans antialiased text-stone-900 p-4 sm:p-8" x-data="{ showImage: true }">

    <!-- Aktionsleiste oben (wird beim Drucken ausgeblendet) -->
    <div class="no-print max-w-4xl mx-auto mb-6 bg-white p-4 rounded-xl shadow-sm border border-stone-200 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('recipes.show', $recipe->id) }}" class="inline-flex items-center space-x-1 text-sm font-medium text-stone-600 hover:text-stone-900">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zurück zum Rezept</span>
        </a>

        <div class="flex items-center space-x-4">
            @if ($recipe->image_path)
                <label class="flex items-center space-x-2 text-xs text-stone-600 cursor-pointer select-none">
                    <input type="checkbox" x-model="showImage" class="rounded border-stone-300 text-[#8ea92c] focus:ring-[#8ea92c]">
                    <span>Rezeptbild mitdrucken</span>
                </label>
            @endif

            <button type="button" onclick="window.print()" 
                    class="inline-flex items-center space-x-2 px-5 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-sm font-semibold rounded-lg shadow-xs transition cursor-pointer">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Jetzt drucken</span>
            </button>
        </div>
    </div>

    <!-- DRUCKBLATT (DIN A4 optimiert) -->
    <div class="print-card max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-stone-200 p-6 sm:p-10 space-y-6">
        
        <!-- Header: Titel, Meta & Bild -->
        <div class="border-b border-stone-200 pb-5">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div class="flex-1 space-y-2">
                    <div class="flex items-center space-x-2 text-xs text-stone-500 uppercase tracking-wider font-semibold">
                        <span>Rezeptschatz</span>
                        @if ($recipe->categories->isNotEmpty())
                            <span>•</span>
                            <span>{{ $recipe->categories->pluck('name')->join(', ') }}</span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight">
                        {{ $recipe->title }}
                    </h1>

                    @if ($recipe->description)
                        <p class="text-xs sm:text-sm text-stone-600 leading-relaxed italic">
                            {{ $recipe->description }}
                        </p>
                    @endif

                    <!-- Eckdaten (Portionen, Zeiten) -->
                    <div class="flex flex-wrap gap-4 pt-2 text-xs font-semibold text-stone-700">
                        <div class="flex items-center space-x-1 bg-stone-100 px-2.5 py-1 rounded-md">
                            <span class="material-symbols-outlined text-sm text-[#8ea92c]">group</span>
                            <span>{{ $recipe->portions }} Portionen</span>
                        </div>
                        @if ($recipe->prep_time_minutes)
                            <div class="flex items-center space-x-1 bg-stone-100 px-2.5 py-1 rounded-md">
                                <span class="material-symbols-outlined text-sm text-[#8ea92c]">timer</span>
                                <span>Vorbereitung: {{ $recipe->prep_time_minutes }} Min.</span>
                            </div>
                        @endif
                        @if ($recipe->cook_time_minutes)
                            <div class="flex items-center space-x-1 bg-stone-100 px-2.5 py-1 rounded-md">
                                <span class="material-symbols-outlined text-sm text-[#8ea92c]">skillet</span>
                                <span>Kochen/Backen: {{ $recipe->cook_time_minutes }} Min.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Foto (falls vorhanden & aktiviert) -->
                @if ($recipe->image_path)
                    <div x-show="showImage" class="w-full sm:w-44 h-32 shrink-0 rounded-xl overflow-hidden border border-stone-200 bg-stone-50">
                        <img src="{{ route('recipes.image', $recipe->id) }}" alt="{{ $recipe->title }}" class="w-full h-full object-cover">
                    </div>
                @endif
            </div>

            <!-- Nährwerte (falls vorhanden) -->
            @if ($recipe->calories || $recipe->protein || $recipe->fat || $recipe->carbohydrates || $recipe->nutrition_raw)
                <div class="mt-4 pt-3 border-t border-dashed border-stone-200 text-xs text-stone-600 flex flex-wrap items-center gap-3">
                    <span class="font-bold text-stone-800">Nährwerte pro Portion:</span>
                    @if ($recipe->calories) <span class="font-semibold text-emerald-800">{{ $recipe->calories }} kcal</span> @endif
                    @if ($recipe->carbohydrates) <span>KH: {{ $recipe->carbohydrates }} g</span> @endif
                    @if ($recipe->fat) <span>Fett: {{ $recipe->fat }} g</span> @endif
                    @if ($recipe->protein) <span>Eiweiss: {{ $recipe->protein }} g</span> @endif
                    @if ($recipe->nutrition_raw && !$recipe->calories) <span>{{ $recipe->nutrition_raw }}</span> @endif
                </div>
            @endif
        </div>

        <!-- Zwei Spalten: Zutaten & Zubereitung -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
            
            <!-- Linke Spalte: Zutaten (4 von 12 Spalten) -->
            <div class="md:col-span-5 space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-stone-800 border-b-2 border-[#8ea92c] pb-1 flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base text-[#8ea92c]">kitchen</span>
                    <span>Zutaten</span>
                </h2>

                <ul class="text-xs text-stone-800 divide-y divide-stone-100">
                    @forelse ($recipe->ingredients as $ing)
                        @php
                            $amount = $ing->amount_text ?: ($ing->amount_numeric ? ($ing->amount_numeric + 0) . ' ' . $ing->unit : '');
                        @endphp
                        <li class="py-1.5 flex items-start space-x-2 page-break-avoid">
                            <!-- Kleines Kästchen zum Abhaken in der Küche -->
                            <span class="inline-block w-3.5 h-3.5 border border-stone-400 rounded-xs shrink-0 mt-0.5"></span>
                            <div>
                                @if (!empty(trim($amount)))
                                    <span class="font-bold text-stone-900">{{ trim($amount) }}</span>
                                @endif
                                <span>{{ $ing->name }}</span>
                            </div>
                        </li>
                    @empty
                        <li class="py-1 text-stone-400 italic">Keine Zutaten hinterlegt</li>
                    @endforelse
                </ul>
            </div>

            <!-- Rechte Spalte: Zubereitungsschritte (7 von 12 Spalten) -->
            <div class="md:col-span-7 space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider text-stone-800 border-b-2 border-[#8ea92c] pb-1 flex items-center space-x-1">
                    <span class="material-symbols-outlined text-base text-[#8ea92c]">format_list_numbered</span>
                    <span>Zubereitung</span>
                </h2>

                <ol class="text-xs text-stone-800 space-y-3">
                    @forelse ($recipe->steps as $step)
                        <li class="flex items-start space-x-2.5 page-break-avoid">
                            <span class="w-5 h-5 rounded-full bg-[#8ea92c] text-white flex items-center justify-center font-bold text-[10px] shrink-0 mt-0.5">
                                {{ $step->step_number }}
                            </span>
                            <div class="leading-relaxed text-stone-700">
                                {{ $step->instruction }}
                            </div>
                        </li>
                    @empty
                        <li class="text-stone-400 italic">Keine Zubereitungsschritte hinterlegt</li>
                    @endforelse
                </ol>

                @if ($recipe->notes)
                    <div class="mt-6 p-3 bg-amber-50/60 rounded-xl border border-amber-200 text-xs page-break-avoid">
                        <span class="font-bold text-amber-900 block mb-1">Hinweise / Notizen:</span>
                        <p class="text-amber-800 leading-relaxed">{{ $recipe->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-stone-200 pt-4 flex items-center justify-between text-[10px] text-stone-400">
            <span>Rezeptschatz • Gedruckt am {{ now()->format('d.m.Y H:i') }}</span>
            @if ($recipe->source_url)
                <span class="truncate max-w-xs">Quelle: {{ $recipe->source_url }}</span>
            @endif
        </div>
    </div>

</body>
</html>
