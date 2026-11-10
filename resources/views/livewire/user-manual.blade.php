<div class="max-w-5xl mx-auto space-y-6">
    <!-- Kopfzeile -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-stone-200 pb-4 gap-3">
        <div class="flex items-center space-x-3">
            <a href="{{ route('recipes.index') }}" class="text-stone-500 hover:text-stone-800 transition p-1.5 rounded-lg hover:bg-stone-100" title="Zurück zur Rezeptübersicht">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-stone-900 flex items-center space-x-2">
                    <span class="material-symbols-outlined text-[#8ea92c]">menu_book</span>
                    <span>Rezeptschatz Benutzerhandbuch</span>
                </h1>
                <p class="text-xs text-stone-500 mt-0.5">
                    Offizielle Dokumentation für Web, Tablet, Raspberry Pi Server & Flutter-App
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" onclick="window.print()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-white border border-stone-300 hover:bg-stone-50 text-stone-700 text-xs font-semibold rounded-lg shadow-2xs transition cursor-pointer">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Drucken / PDF</span>
            </button>
            <a href="{{ route('account.index', ['tab' => 'help']) }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-semibold rounded-lg transition">
                <span class="material-symbols-outlined text-base">help</span>
                <span>Hilfe & Support</span>
            </a>
        </div>
    </div>

    <!-- Schnell-Sprungmarken (Inhaltsverzeichnis-Chips) -->
    <div class="bg-white rounded-2xl shadow-xs border border-stone-200 p-4 space-y-3">
        <span class="text-xs font-bold text-stone-700 uppercase tracking-wider block">Schnellnavigation zu den Kapiteln:</span>
        <div class="flex flex-wrap gap-2 text-xs">
            <a href="#1-überblick--architektur" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">1. Überblick</a>
            <a href="#2-erste-schritte--pwa-installation-tablet--smartphone" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">2. PWA-Installation</a>
            <a href="#3-navigation--benutzeroberfläche" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">3. Navigation</a>
            <a href="#4-rezepte-finden--kochen" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">4. Rezepte finden & kochen</a>
            <a href="#5-die-wege-für-neue-rezepte" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">5. Wege für neue Rezepte</a>
            <a href="#6--nährwerte-ermitteln--kategorien-zuweisen-ki-rechner" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-lg transition font-bold border border-emerald-200/80">6. 🥑 Nährwert-Rechner (KI)</a>
            <a href="#7-einkaufsliste" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">7. Einkaufsliste</a>
            <a href="#8-menüplaner" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">8. Menüplaner</a>
            <a href="#9-kategorien--schlagwörter-verwalten" class="px-2.5 py-1 bg-stone-100 hover:bg-[#8ea92c]/15 hover:text-[#8ea92c] text-stone-700 rounded-lg transition font-medium">9. Kategorien & Tags</a>
            <a href="#10--mobile-flutter-app-synchronisation--offline-nutzung" class="px-2.5 py-1 bg-sky-50 hover:bg-sky-100 text-sky-800 rounded-lg transition font-bold border border-sky-200/80">10. 📱 Mobile Flutter-App</a>
            <a href="#11-administration--server-befehle-raspberry-pi" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg transition font-bold border border-amber-200/80">11. ⚙️ Raspberry Pi Befehle</a>
        </div>
    </div>

    <!-- Handbuch-Hauptinhalt -->
    <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 sm:p-10">
        <article class="user-manual-prose prose prose-stone max-w-none text-sm leading-relaxed text-stone-800">
            {!! $contentHtml !!}
        </article>
    </div>
</div>

<style>
    /* Styling für das gerenderte Markdown-Dokument */
    .user-manual-prose h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1c1917;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        border-bottom: 2px solid #8ea92c;
        padding-bottom: 0.5rem;
    }
    .user-manual-prose h2 {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1c1917;
        margin-top: 2rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #e7e5e4;
        padding-bottom: 0.35rem;
    }
    .user-manual-prose h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #292524;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
    }
    .user-manual-prose p {
        margin-top: 0.65rem;
        margin-bottom: 0.65rem;
        line-height: 1.65;
    }
    .user-manual-prose ul {
        list-style-type: disc;
        margin-left: 1.5rem;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .user-manual-prose ol {
        list-style-type: decimal;
        margin-left: 1.5rem;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }
    .user-manual-prose li {
        margin-top: 0.25rem;
        margin-bottom: 0.25rem;
    }
    .user-manual-prose pre {
        background-color: #1c1917;
        color: #f5f5f4;
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        overflow-x: auto;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.8rem;
        margin-top: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .user-manual-prose code {
        background-color: #f5f5f4;
        color: #292524;
        padding: 0.15rem 0.35rem;
        border-radius: 0.35rem;
        font-size: 0.825em;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        border: 1px solid #e7e5e4;
    }
    .user-manual-prose pre code {
        background-color: transparent;
        color: inherit;
        border: none;
        padding: 0;
    }
    .user-manual-prose hr {
        border-color: #e7e5e4;
        margin-top: 2rem;
        margin-bottom: 2rem;
    }
    .user-manual-prose table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        margin-bottom: 1rem;
        font-size: 0.85rem;
    }
    .user-manual-prose th, .user-manual-prose td {
        border: 1px solid #e7e5e4;
        padding: 0.5rem 0.75rem;
        text-align: left;
    }
    .user-manual-prose th {
        background-color: #f5f5f4;
        font-weight: 600;
    }
    .user-manual-prose a {
        color: #8ea92c;
        text-decoration: none;
        font-weight: 500;
    }
    .user-manual-prose a:hover {
        text-decoration: underline;
    }
</style>
