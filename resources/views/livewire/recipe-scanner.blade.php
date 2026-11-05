<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" 
     x-data="{
        ocrProgress: 0,
        ocrStatus: '',
        isOcrRunning: false,
        activeTab: 'camera', // 'camera' oder 'text'

        async startOcr(imageElement) {
            if (!imageElement || !imageElement.src) return;
            this.isOcrRunning = true;
            this.ocrProgress = 5;
            this.ocrStatus = 'Lade Texterkennung (Tesseract)...';

            try {
                const worker = await Tesseract.createWorker('deu', 1, {
                    logger: m => {
                        if (m.status === 'recognizing text') {
                            this.ocrProgress = Math.round(m.progress * 100);
                            this.ocrStatus = `Lese Text... ${this.ocrProgress}%`;
                        } else if (m.status === 'loading tesseract core') {
                            this.ocrStatus = 'Lade OCR-Kern...';
                            this.ocrProgress = 20;
                        } else if (m.status === 'initializing tesseract') {
                            this.ocrStatus = 'Initialisiere...';
                            this.ocrProgress = 40;
                        }
                    }
                });

                const ret = await worker.recognize(imageElement);
                await worker.terminate();

                this.ocrProgress = 100;
                this.ocrStatus = 'Texterkennung abgeschlossen!';
                this.isOcrRunning = false;

                if (ret && ret.data && ret.data.text) {
                    $wire.setExtractedText(ret.data.text);
                } else {
                    alert('Kein Text auf dem Bild erkannt. Bitte versuchen Sie eine schärfere Aufnahme.');
                }
            } catch (err) {
                console.error(err);
                this.isOcrRunning = false;
                this.ocrStatus = 'Fehler bei der Texterkennung.';
                alert('Texterkennung fehlgeschlagen: ' + err.message);
            }
        }
     }"
     @image-ready-for-ocr.window="
        setTimeout(() => {
            const img = document.getElementById('scannedImagePreview');
            if (img && img.src) {
                startOcr(img);
            }
        }, 600);
     ">

    <!-- Tesseract.js für 100% lokale Browser-Texterkennung -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>

    <!-- Kopfbereich -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-stone-500 text-sm mb-1">
                <a href="{{ route('recipes.index') }}" class="hover:text-[#8ea92c]">Rezepte</a>
                <span>&gt;</span>
                <span class="text-stone-800 font-semibold">Scann ein Rezept</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-stone-900 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-3xl text-[#8ea92c]">document_scanner</span>
                <span>Scann ein Rezept</span>
            </h1>
            <p class="text-stone-600 text-sm mt-1">
                Fotografieren Sie ein Kochbuch, eine Zeitschrift oder eine Notiz – das Rezept wird automatisch erkannt und strukturiert.
            </p>
        </div>

        <a href="{{ route('recipes.index') }}" class="inline-flex items-center text-sm font-medium text-stone-600 hover:text-stone-900">
            <span class="material-symbols-outlined text-base mr-1">arrow_back</span>
            Abbrechen
        </a>
    </div>

    <!-- Fehlermeldungen -->
    @if ($errorMessage)
        <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="material-symbols-outlined text-rose-500">error</span>
                <span class="text-sm font-medium">{{ $errorMessage }}</span>
            </div>
            <button type="button" wire:click="$set('errorMessage', null)" class="text-rose-400 hover:text-rose-600">
                <span class="material-symbols-outlined text-lg">close</span>
            </button>
        </div>
    @endif

    @if (! $isParsed)
        <!-- SCHRITT 1: FOTO AUFNEHMEN / HOCHLADEN ODER TEXT EINFÜGEN -->
        <div class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden mb-8">
            
            <!-- Tabs: Foto oder Text -->
            <div class="border-b border-stone-200 flex">
                <button type="button" @click="activeTab = 'camera'" 
                        :class="activeTab === 'camera' ? 'border-[#8ea92c] text-[#8ea92c] font-bold bg-[#8ea92c]/5' : 'border-transparent text-stone-600 hover:text-stone-900'"
                        class="flex-1 py-3 px-4 text-center border-b-2 text-sm font-medium flex items-center justify-center space-x-2 transition">
                    <span class="material-symbols-outlined text-xl">photo_camera</span>
                    <span>Foto / Scan</span>
                </button>
                <button type="button" @click="activeTab = 'text'" 
                        :class="activeTab === 'text' ? 'border-[#8ea92c] text-[#8ea92c] font-bold bg-[#8ea92c]/5' : 'border-transparent text-stone-600 hover:text-stone-900'"
                        class="flex-1 py-3 px-4 text-center border-b-2 text-sm font-medium flex items-center justify-center space-x-2 transition">
                    <span class="material-symbols-outlined text-xl">edit_note</span>
                    <span>Rezepttext manuell</span>
                </button>
            </div>

            <!-- TAB 1: Foto / Scan -->
            <div x-show="activeTab === 'camera'" class="p-6">
                @if (! $imagePreviewUrl)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <!-- Option A: Kamera auf Tablet / Smartphone -->
                        <label class="border-2 border-dashed border-[#8ea92c]/50 hover:border-[#8ea92c] bg-stone-50 hover:bg-[#8ea92c]/5 rounded-2xl p-8 flex flex-col items-center justify-center cursor-pointer transition text-center group">
                            <input type="file" accept="image/*" capture="environment" wire:model="imageFile" class="hidden">
                            <span class="w-16 h-16 rounded-full bg-[#8ea92c]/10 text-[#8ea92c] flex items-center justify-center mb-3 group-hover:scale-105 transition">
                                <span class="material-symbols-outlined text-3xl">photo_camera</span>
                            </span>
                            <span class="font-bold text-stone-800 text-base">Foto mit Kamera aufnehmen</span>
                            <span class="text-xs text-stone-500 mt-1">Optimal für Samsung Tablet & Smartphone</span>
                        </label>

                        <!-- Option B: Datei / Bild aus Galerie hochladen -->
                        <label class="border-2 border-dashed border-stone-200 hover:border-stone-400 bg-stone-50 hover:bg-stone-100 rounded-2xl p-8 flex flex-col items-center justify-center cursor-pointer transition text-center group">
                            <input type="file" accept="image/*" wire:model="imageFile" class="hidden">
                            <span class="w-16 h-16 rounded-full bg-stone-200 text-stone-600 flex items-center justify-center mb-3 group-hover:scale-105 transition">
                                <span class="material-symbols-outlined text-3xl">upload_file</span>
                            </span>
                            <span class="font-bold text-stone-800 text-base">Bild oder Screenshot hochladen</span>
                            <span class="text-xs text-stone-500 mt-1">JPG, PNG, WEBP (bis 15 MB)</span>
                        </label>

                    </div>
                @else
                    <!-- Bildvorschau & OCR Steuerung -->
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row items-center gap-6 bg-stone-50 p-4 rounded-xl border border-stone-200">
                            <div class="w-full sm:w-48 h-48 bg-black/5 rounded-lg overflow-hidden flex items-center justify-center relative">
                                <img id="scannedImagePreview" src="{{ $imagePreviewUrl }}" alt="Scan" class="w-full h-full object-contain">
                                <div wire:loading wire:target="imageFile" class="absolute inset-0 bg-white/80 flex items-center justify-center text-xs font-semibold text-stone-700">
                                    Lädt Bild hoch...
                                </div>
                            </div>

                            <div class="flex-1 w-full space-y-3">
                                <div>
                                    <h3 class="font-bold text-stone-900 text-base">Foto geladen</h3>
                                    <p class="text-xs text-stone-500">Das Foto wird automatisch auch als Rezeptbild gespeichert.</p>
                                </div>

                                <!-- OCR Status / Fortschrittsbalken -->
                                <div x-show="isOcrRunning || ocrProgress > 0" class="space-y-1">
                                    <div class="flex justify-between text-xs font-medium text-stone-700">
                                        <span x-text="ocrStatus">Texterkennung läuft...</span>
                                        <span x-text="ocrProgress + '%'">0%</span>
                                    </div>
                                    <div class="w-full bg-stone-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-[#8ea92c] h-2 transition-all duration-200" :style="'width: ' + ocrProgress + '%'"></div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 pt-2">
                                    <button type="button" 
                                            @click="startOcr(document.getElementById('scannedImagePreview'))" 
                                            :disabled="isOcrRunning"
                                            class="inline-flex items-center px-4 py-2 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-semibold rounded-lg shadow-sm transition disabled:opacity-50">
                                        <span class="material-symbols-outlined text-lg mr-1.5" :class="isOcrRunning ? 'animate-spin' : ''">
                                            <span x-text="isOcrRunning ? 'autorenew' : 'document_scanner'">document_scanner</span>
                                        </span>
                                        <span x-text="isOcrRunning ? 'Erkenne Text...' : 'Texterkennung starten'">Texterkennung starten</span>
                                    </button>

                                    <button type="button" wire:click="resetScan" class="inline-flex items-center px-3 py-2 bg-stone-200 hover:bg-stone-300 text-stone-700 text-sm font-medium rounded-lg transition">
                                        Anderes Foto wählen
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Textbereich zur manuellen Überprüfung / Korrektur des Scans -->
                        <div class="space-y-2 pt-2">
                            <label class="block text-sm font-semibold text-stone-800">
                                Erkannter Rezepttext (kann vor der Strukturierung korrigiert werden):
                            </label>
                            <textarea wire:model.defer="rawText" rows="8" 
                                      placeholder="Der gescannte Text erscheint hier..."
                                      class="w-full rounded-xl border border-stone-300 px-3 py-2 text-sm text-stone-800 focus:border-[#8ea92c] focus:ring-1 focus:ring-[#8ea92c] font-mono"></textarea>
                            
                            <div class="flex justify-end">
                                <button type="button" wire:click="parseText" class="inline-flex items-center px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-bold rounded-xl shadow transition">
                                    <span>Rezept strukturieren</span>
                                    <span class="material-symbols-outlined text-lg ml-1">arrow_forward</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- TAB 2: Text manuell einfügen -->
            <div x-show="activeTab === 'text'" class="p-6 space-y-4" style="display: none;">
                <div>
                    <label class="block text-sm font-bold text-stone-900 mb-1">
                        Rezepttext hier einfügen:
                    </label>
                    <p class="text-xs text-stone-500 mb-2">
                        Fügen Sie ein beliebiges deutsches Rezept ein. Titel, Portionen, Kochzeiten, Zutaten und Schritte werden automatisch erkannt.
                    </p>
                    <textarea wire:model.defer="rawText" rows="12" 
                              placeholder="Beispiel:&#10;Rüeblisuppe mit Ingwer&#10;Für 4 Personen&#10;&#10;Zutaten:&#10;500 g Rüebli&#10;1 Zwiebel&#10;8 dl Gemüsebouillon&#10;&#10;Zubereitung:&#10;1. Rüebli und Zwiebel klein schneiden.&#10;2. In Butter andünsten und mit Bouillon köcheln lassen."
                              class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm text-stone-800 focus:border-[#8ea92c] focus:ring-1 focus:ring-[#8ea92c]"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="button" wire:click="parseText" class="inline-flex items-center px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-bold rounded-xl shadow transition">
                        <span>Rezept strukturieren</span>
                        <span class="material-symbols-outlined text-lg ml-1">arrow_forward</span>
                    </button>
                </div>
            </div>

        </div>
    @else
        <!-- SCHRITT 2: STRUKTURIERTE VORSCHAU & KONTROLLE VOR DEM SPEICHERN -->
        <div class="space-y-6">
            
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                    <span class="text-sm font-semibold">Text erfolgreich analysiert! Bitte prüfen Sie die Angaben vor dem Speichern.</span>
                </div>
                <button type="button" wire:click="$set('isParsed', false)" class="text-xs font-semibold text-emerald-700 hover:underline">
                    Zurück zum Text
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 space-y-6">
                
                <!-- Titel & Bildvorschau -->
                <div class="flex flex-col md:flex-row gap-6">
                    @if ($imagePreviewUrl)
                        <div class="w-full md:w-44 h-44 rounded-xl overflow-hidden bg-stone-100 border border-stone-200 shrink-0">
                            <img src="{{ $imagePreviewUrl }}" alt="Rezeptbild" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="flex-1 space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-stone-500 mb-1">Rezepttitel</label>
                            <input type="text" wire:model.defer="title" 
                                   class="w-full text-lg sm:text-xl font-bold text-stone-900 rounded-xl border border-stone-300 px-3.5 py-2 focus:border-[#8ea92c] focus:ring-1 focus:ring-[#8ea92c]">
                            @error('title') <span class="text-xs text-rose-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-stone-500 mb-1">Kurzbeschreibung (optional)</label>
                            <input type="text" wire:model.defer="description" placeholder="Feine Notiz zum Rezept..." 
                                   class="w-full text-sm text-stone-800 rounded-xl border border-stone-300 px-3.5 py-2 focus:border-[#8ea92c] focus:ring-1 focus:ring-[#8ea92c]">
                        </div>

                        <!-- Kennzahlen -->
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-stone-500 mb-1">Portionen</label>
                                <input type="number" min="1" max="99" wire:model.defer="portions" 
                                       class="w-full text-sm font-semibold text-stone-800 rounded-lg border border-stone-300 px-3 py-1.5 focus:border-[#8ea92c]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-stone-500 mb-1">Vorbereitung</label>
                                <div class="relative">
                                    <input type="number" min="0" wire:model.defer="prep_time_minutes" placeholder="Min"
                                           class="w-full text-sm font-semibold text-stone-800 rounded-lg border border-stone-300 px-3 py-1.5 focus:border-[#8ea92c]">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-stone-500 mb-1">Kochzeit</label>
                                <div class="relative">
                                    <input type="number" min="0" wire:model.defer="cook_time_minutes" placeholder="Min"
                                           class="w-full text-sm font-semibold text-stone-800 rounded-lg border border-stone-300 px-3 py-1.5 focus:border-[#8ea92c]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-stone-100">

                <!-- Zutatenliste -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-stone-900 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[#8ea92c] text-xl">grocery</span>
                            <span>Zutaten ({{ count($ingredients) }})</span>
                        </h3>
                        <button type="button" wire:click="addIngredient" class="inline-flex items-center text-xs font-semibold text-[#8ea92c] hover:underline">
                            <span class="material-symbols-outlined text-sm mr-0.5">add</span>
                            Zutat hinzufügen
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($ingredients as $index => $ing)
                            <div class="flex items-center gap-2" wire:key="ingredient-{{ $index }}">
                                <input type="number" step="any" wire:model.defer="ingredients.{{ $index }}.amount_numeric" placeholder="Menge" 
                                       class="w-20 text-sm rounded-lg border border-stone-300 px-2.5 py-1.5 focus:border-[#8ea92c]">
                                <input type="text" wire:model.defer="ingredients.{{ $index }}.unit" placeholder="Einheit (g, EL...)" 
                                       class="w-24 text-sm rounded-lg border border-stone-300 px-2.5 py-1.5 focus:border-[#8ea92c]">
                                <input type="text" wire:model.defer="ingredients.{{ $index }}.name" placeholder="Zutat" 
                                       class="flex-1 text-sm font-medium rounded-lg border border-stone-300 px-3 py-1.5 focus:border-[#8ea92c]">
                                <button type="button" wire:click="removeIngredient({{ $index }})" class="p-1.5 text-stone-400 hover:text-rose-500 transition">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="border-stone-100">

                <!-- Zubereitungsschritte -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-base font-bold text-stone-900 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[#8ea92c] text-xl">format_list_numbered</span>
                            <span>Zubereitungsschritte ({{ count($steps) }})</span>
                        </h3>
                        <button type="button" wire:click="addStep" class="inline-flex items-center text-xs font-semibold text-[#8ea92c] hover:underline">
                            <span class="material-symbols-outlined text-sm mr-0.5">add</span>
                            Schritt hinzufügen
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($steps as $index => $step)
                            <div class="flex items-start gap-3" wire:key="step-{{ $index }}">
                                <span class="w-6 h-6 rounded-full bg-stone-100 text-stone-700 font-bold text-xs flex items-center justify-center shrink-0 mt-2">
                                    {{ $index + 1 }}
                                </span>
                                <textarea rows="2" wire:model.defer="steps.{{ $index }}.instruction" placeholder="Schritt beschreiben..." 
                                          class="flex-1 text-sm rounded-xl border border-stone-300 px-3 py-2 focus:border-[#8ea92c]"></textarea>
                                <button type="button" wire:click="removeStep({{ $index }})" class="p-1.5 text-stone-400 hover:text-rose-500 transition mt-1">
                                    <span class="material-symbols-outlined text-lg">delete</span>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Aktionen -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-stone-200">
                    <button type="button" wire:click="resetScan" class="w-full sm:w-auto px-4 py-2 text-stone-600 hover:text-stone-900 text-sm font-medium transition">
                        Scan verwerfen & neu starten
                    </button>

                    <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto justify-end">
                        <a href="{{ route('recipes.index') }}" class="px-4 py-2.5 border border-stone-300 text-stone-700 hover:bg-stone-50 text-sm font-semibold rounded-xl transition">
                            Abbrechen
                        </a>
                        <button type="button" wire:click="editInEditor" class="inline-flex items-center px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 text-sm font-semibold rounded-xl border border-stone-300 transition">
                            <span class="material-symbols-outlined text-base mr-1.5 text-stone-600">edit_note</span>
                            <span>Im Editor weiterbearbeiten</span>
                        </button>
                        <button type="button" wire:click="saveRecipe" class="inline-flex items-center px-6 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white text-sm font-bold rounded-xl shadow transition">
                            <span class="material-symbols-outlined text-lg mr-1.5">save</span>
                            <span>Direkt speichern</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    @endif

</div>

