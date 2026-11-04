<div class="max-w-3xl mx-auto py-6 space-y-6">
    <!-- Kopfzeile -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-[#8ea92c] text-3xl">upload_file</span>
                <span>Rezepte importieren (COOKmate / My CookBook)</span>
            </h1>
            <p class="text-sm text-stone-500 mt-1">
                Übertragen Sie Ihre bestehenden Rezepte mitsamt Bildern, Zutaten, Kategorien und Zubereitungsschritten.
            </p>
        </div>
        <a href="{{ route('recipes.index') }}" 
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg shadow-xs hover:bg-stone-50 transition">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zurück</span>
        </a>
    </div>

    <!-- Fehlermeldung -->
    @if ($errorMessage)
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start space-x-3">
            <span class="material-symbols-outlined text-xl flex-shrink-0 text-rose-600">error</span>
            <div>
                <strong class="font-semibold block">Import fehlgeschlagen:</strong>
                <span>{{ $errorMessage }}</span>
            </div>
        </div>
    @endif

    <!-- Erfolgsanzeige nach Import -->
    @if ($resultStats)
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-6 sm:p-8 space-y-6">
            <div class="flex items-center space-x-3 text-emerald-700">
                <span class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl">check_circle</span>
                </span>
                <div>
                    <h2 class="text-xl font-bold text-stone-900">Import erfolgreich abgeschlossen!</h2>
                    <p class="text-sm text-stone-600">Ihre Rezepte wurden erfolgreich in Rezeptschatz übertragen.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-100 text-center">
                    <span class="block text-2xl font-extrabold text-emerald-800">{{ $resultStats['recipes_count'] }}</span>
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Rezepte importiert</span>
                </div>
                <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-100 text-center">
                    <span class="block text-2xl font-extrabold text-emerald-800">{{ $resultStats['images_count'] }}</span>
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Bilder verknüpft</span>
                </div>
                <div class="p-4 bg-emerald-50/60 rounded-xl border border-emerald-100 text-center">
                    <span class="block text-2xl font-extrabold text-emerald-800">{{ $resultStats['categories_count'] }}</span>
                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Neue Kategorien</span>
                </div>
            </div>

            <div class="pt-2 flex justify-end space-x-3">
                <button wire:click="$set('resultStats', null)" class="px-4 py-2 border border-stone-300 rounded-lg text-sm text-stone-700 hover:bg-stone-50 transition">
                    Weitere Datei importieren
                </button>
                <a href="{{ route('recipes.index') }}" 
                   class="inline-flex items-center space-x-2 px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white font-semibold rounded-lg text-sm shadow-sm transition">
                    <span>Zu meinen Rezepten</span>
                    <span class="material-symbols-outlined text-lg">arrow_forward</span>
                </a>
            </div>
        </div>
    @else
        <!-- Upload Formular -->
        <form wire:submit="startImport" class="bg-white rounded-2xl shadow-sm border border-stone-200 p-6 sm:p-8 space-y-6">
            
            <!-- Upload Box -->
            <div class="border-2 border-dashed border-stone-300 hover:border-[#8ea92c] rounded-2xl p-8 text-center transition bg-stone-50/50">
                <div class="w-16 h-16 rounded-full bg-brand-50 text-[#8ea92c] flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-3xl">cloud_upload</span>
                </div>

                <div class="space-y-2">
                    <label class="block text-base font-bold text-stone-800 cursor-pointer">
                        <span>Datei auswählen oder hierher ziehen</span>
                        <input wire:model="file" type="file" accept=".mcb,.zip,.xml" class="sr-only">
                    </label>
                    <p class="text-xs text-stone-500">
                        Unterstützte Formate: <strong>.mcb</strong> (empfohlen, enthält Bilder), <strong>.zip</strong> oder <strong>.xml</strong> (bis zu 300 MB)
                    </p>
                </div>

                <!-- Dateivorschau nach Auswahl -->
                @if ($file)
                    <div class="mt-4 inline-flex items-center space-x-2 px-3 py-1.5 bg-white border border-stone-200 rounded-lg text-xs font-semibold text-stone-800 shadow-xs">
                        <span class="material-symbols-outlined text-base text-[#8ea92c]">draft</span>
                        <span>{{ $file->getClientOriginalName() }} ({{ round($file->getSize() / 1024 / 1024, 2) }} MB)</span>
                    </div>
                @endif

                @error('file')
                    <div class="mt-3 text-xs text-rose-600 font-semibold">{{ $message }}</div>
                @enderror
            </div>

            <!-- Hinweise zum Export aus COOKmate -->
            <div class="bg-stone-50 rounded-xl p-4 border border-stone-200 text-xs text-stone-600 space-y-2">
                <div class="font-bold text-stone-800 flex items-center space-x-1.5">
                    <span class="material-symbols-outlined text-base text-[#8ea92c]">help</span>
                    <span>Wo finde ich die Export-Datei in COOKmate?</span>
                </div>
                <p class="leading-relaxed">
                    1. Öffnen Sie COOKmate auf dem Smartphone oder im Web.<br>
                    2. Gehen Sie auf <strong>Einstellungen &rarr; Sichern & Wiederherstellen &rarr; Exportieren</strong> (bzw. auf den Export-Link mit Bildern).<br>
                    3. Laden Sie die erzeugte <strong>.mcb</strong>- oder <strong>.zip</strong>-Datei herunter und laden Sie sie hier hoch.
                </p>
            </div>

            <!-- Start-Button -->
            <div class="flex items-center justify-end space-x-3 pt-2 border-t border-stone-100">
                <a href="{{ route('recipes.index') }}" class="px-4 py-2 border border-stone-300 rounded-lg text-sm text-stone-700 hover:bg-stone-50 transition">
                    Abbrechen
                </a>

                <button type="submit" 
                        wire:loading.attr="disabled"
                        @if (!$file || $isProcessing) disabled @endif
                        class="inline-flex items-center space-x-2 px-6 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg text-sm shadow-sm transition">
                    <span wire:loading.remove class="material-symbols-outlined text-lg">publish</span>
                    <span wire:loading class="material-symbols-outlined text-lg animate-spin">refresh</span>
                    <span wire:loading.remove>Import starten</span>
                    <span wire:loading>Rezepte werden importiert... Bitte warten</span>
                </button>
            </div>
        </form>
    @endif
</div>
