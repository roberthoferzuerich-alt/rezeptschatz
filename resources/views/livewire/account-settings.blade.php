<div class="max-w-6xl mx-auto py-4">
    <!-- Breadcrumb / Titel -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-900 flex items-center space-x-2">
                <span class="material-symbols-outlined text-brand-600 text-3xl">manage_accounts</span>
                <span>Mein Konto & Einstellungen</span>
            </h1>
            <p class="text-sm text-stone-500 mt-1">
                Verwalten Sie Ihre persönlichen Angaben, Zugangsdaten und Präferenzen für Rezeptschatz.
            </p>
        </div>
        <a href="{{ route('recipes.index') }}" 
           class="inline-flex items-center space-x-1.5 px-3.5 py-2 text-sm font-medium text-stone-700 bg-white border border-stone-300 rounded-lg shadow-xs hover:bg-stone-50 transition">
            <span class="material-symbols-outlined text-base">arrow_back</span>
            <span>Zurück zu den Rezepten</span>
        </a>
    </div>

    <!-- Hauptbereich mit 2-Spalten-Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Linke Spalte: Die originale grüne "Dein Konto" Box (wie im Screenshot) -->
        <aside class="lg:col-span-4">
            <div class="bg-[#8ea92c] text-white rounded-xl shadow-lg p-6 space-y-4">
                <h2 class="text-xl font-bold text-white tracking-wide border-b border-white/20 pb-2">
                    Dein Konto
                </h2>
                <ul class="space-y-2.5 text-sm font-medium text-white/95">
                    <li>
                        <button type="button" wire:click="setTab('account')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'account' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Account-Informationen</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('personal')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'personal' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Persönliche Informationen</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('password')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'password' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Passwort ändern</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('credits')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'credits' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Credits</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('notifications')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'notifications' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>E-Mail-Benachrichtigungen</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('settings')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'settings' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Rezeptschatz-Einstellungen</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('header-image')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'header-image' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Ändere das Kopfzeilenbild</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" wire:click="setTab('close-account')" 
                                class="w-full text-left flex items-center space-x-2.5 py-1.5 px-2 rounded-lg transition {{ $tab === 'close-account' ? 'bg-white/25 font-bold shadow-xs' : 'hover:bg-white/15' }}">
                            <span class="text-base leading-none">•</span>
                            <span>Schließe deinen Account</span>
                        </button>
                    </li>
                </ul>

                <!-- Abmelden-Button unten in der Box -->
                <div class="border-t border-white/20 pt-4 mt-6">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 bg-black/20 hover:bg-black/30 text-white rounded-lg font-semibold text-sm transition">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span>Abmelden</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Zusätzliche Hilfe-Links -->
            <div class="bg-white rounded-xl shadow-xs border border-stone-200 p-4 mt-4 text-xs text-stone-600 space-y-2">
                <div class="font-semibold text-stone-800 flex items-center space-x-1.5">
                    <span class="material-symbols-outlined text-base text-stone-500">info</span>
                    <span>System-Status</span>
                </div>
                <p>Raspberry Pi 5 &bull; MariaDB 11.8 &bull; Port 8449 (SSL)</p>
                <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-stone-500">
                    <span>Angemeldet als:</span>
                    <span class="font-medium text-stone-800">{{ $user->name }}</span>
                </div>
            </div>
        </aside>

        <!-- Rechte Spalte: Inhalt des gewählten Reiters -->
        <div class="lg:col-span-8 bg-white rounded-xl shadow-xs border border-stone-200 p-6 sm:p-8">

            <!-- TAB 1: Account-Informationen -->
            @if ($tab === 'account')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Account-Informationen</h3>
                        <p class="text-sm text-stone-500">Übersicht über Ihr Benutzerkonto und Systemberechtigungen.</p>
                    </div>

                    <div class="bg-stone-50 rounded-xl p-5 border border-stone-200 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="block text-xs font-semibold text-stone-500 uppercase tracking-wider">Benutzername</span>
                                <span class="text-base font-semibold text-stone-900">{{ $user->name }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-stone-500 uppercase tracking-wider">E-Mail-Adresse</span>
                                <span class="text-base font-semibold text-stone-900">{{ $user->email }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-stone-500 uppercase tracking-wider">Benutzerrolle</span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $user->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-brand-100 text-brand-800' }}">
                                    {{ $user->role === 'admin' ? 'Administrator (Familienvorstand)' : 'Familienmitglied' }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-xs font-semibold text-stone-500 uppercase tracking-wider">Registriert am</span>
                                <span class="text-stone-700">{{ $user->created_at ? $user->created_at->format('d.m.Y H:i') : '–' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-stone-100 pt-5">
                        <h4 class="text-sm font-bold text-stone-800 mb-2">Synchronisation & Mobile App (Android)</h4>
                        <p class="text-xs text-stone-600 mb-3">
                            Über Ihr Konto können Sie die mobile Android-App verbinden. Daten werden automatisch 2-Wege synchronisiert (Push & Pull).
                        </p>
                        <div class="inline-flex items-center space-x-2 text-xs text-emerald-700 bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-200">
                            <span class="material-symbols-outlined text-base">cloud_done</span>
                            <span>REST-API v1 aktiv auf <code class="font-mono">https://rhz.internet-box.ch:8449/api/v1</code></span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 2: Persönliche Informationen -->
            @if ($tab === 'personal')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Persönliche Informationen</h3>
                        <p class="text-sm text-stone-500">Passen Sie Ihren Namen und Ihre E-Mail-Adresse an.</p>
                    </div>

                    <form wire:submit="updateProfile" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Name</label>
                            <input wire:model="name" type="text" required
                                   class="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                            @error('name') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">E-Mail-Adresse</label>
                            <input wire:model="email" type="email" required
                                   class="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                            @error('email') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-3">
                            <button type="submit" 
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                <span class="material-symbols-outlined text-lg">save</span>
                                <span>Änderungen speichern</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- TAB 3: Passwort ändern -->
            @if ($tab === 'password')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Passwort ändern</h3>
                        <p class="text-sm text-stone-500">Wählen Sie ein sicheres neues Passwort für Ihren Zugang.</p>
                    </div>

                    <form wire:submit="updatePassword" class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Aktuelles Passwort</label>
                            <input wire:model="current_password" type="password" required
                                   class="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                            @error('current_password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Neues Passwort (mindestens 8 Zeichen)</label>
                            <input wire:model="new_password" type="password" required
                                   class="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                            @error('new_password') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Neues Passwort bestätigen</label>
                            <input wire:model="new_password_confirmation" type="password" required
                                   class="w-full px-3.5 py-2.5 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                        </div>

                        <div class="pt-3">
                            <button type="submit" 
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                <span class="material-symbols-outlined text-lg">lock_reset</span>
                                <span>Passwort aktualisieren</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- TAB 4: Credits & Systeminfo -->
            @if ($tab === 'credits')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Credits & System-Architektur</h3>
                        <p class="text-sm text-stone-500">Hintergrundinformationen zur privaten Rezeptschatz-Plattform.</p>
                    </div>

                    <div class="prose prose-stone text-sm space-y-4">
                        <div class="bg-brand-50 border border-brand-200 rounded-xl p-5 space-y-3">
                            <div class="flex items-center space-x-2 text-brand-800 font-bold text-base">
                                <span class="material-symbols-outlined">restaurant</span>
                                <span>Rezeptschatz (Rezeptbuch) &bull; Version 1.0</span>
                            </div>
                            <p class="text-xs text-stone-700 leading-relaxed">
                                Speziell konzipiert und entwickelt für Robert Hofer (Zürich) als private, hochperformante Alternative zu kommerziellen Cloud-Diensten.
                                Inspiriert von den Stärken von COOKmate, jedoch 100 % in eigenem Besitz, datenschutzkonform und ohne externe Abhängigkeiten.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div class="p-4 rounded-lg border border-stone-200 bg-stone-50">
                                <h4 class="font-bold text-stone-800 text-sm mb-1">Web-Applikation & Backend</h4>
                                <ul class="text-xs text-stone-600 space-y-1">
                                    <li>&bull; Laravel 12 + Livewire 4</li>
                                    <li>&bull; Tailwind CSS & Blade</li>
                                    <li>&bull; Laravel Sanctum Token Auth</li>
                                    <li>&bull; Idempotenter 2-Wege Sync (UUIDv4)</li>
                                </ul>
                            </div>
                            <div class="p-4 rounded-lg border border-stone-200 bg-stone-50">
                                <h4 class="font-bold text-stone-800 text-sm mb-1">Infrastruktur & Server</h4>
                                <ul class="text-xs text-stone-600 space-y-1">
                                    <li>&bull; Raspberry Pi 5 (Debian Linux)</li>
                                    <li>&bull; Nginx Webserver mit SSL (Let's Encrypt)</li>
                                    <li>&bull; MariaDB 11.8 Datenbank</li>
                                    <li>&bull; Port 8449 SSL mit Port-Forwarding</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 5: E-Mail-Benachrichtigungen -->
            @if ($tab === 'notifications')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">E-Mail-Benachrichtigungen</h3>
                        <p class="text-sm text-stone-500">Legen Sie fest, wann Sie per E-Mail informiert werden möchten.</p>
                    </div>

                    <div class="space-y-4">
                        <label class="flex items-start space-x-3 p-4 rounded-xl border border-stone-200 hover:bg-stone-50 cursor-pointer">
                            <input wire:model="notify_shared_recipes" type="checkbox" class="mt-1 w-4 h-4 text-[#8ea92c] rounded border-stone-300 focus:ring-[#8ea92c]">
                            <div>
                                <span class="block text-sm font-semibold text-stone-800">Neue geteilte Rezepte</span>
                                <span class="block text-xs text-stone-500">Benachrichtigung erhalten, wenn ein Familienmitglied ein neues Rezept freigibt.</span>
                            </div>
                        </label>

                        <label class="flex items-start space-x-3 p-4 rounded-xl border border-stone-200 hover:bg-stone-50 cursor-pointer">
                            <input wire:model="notify_sync_status" type="checkbox" class="mt-1 w-4 h-4 text-[#8ea92c] rounded border-stone-300 focus:ring-[#8ea92c]">
                            <div>
                                <span class="block text-sm font-semibold text-stone-800">Sicherheits- und Synchronisationsmeldungen</span>
                                <span class="block text-xs text-stone-500">Informationen bei neuen App-Anmeldungen oder Synchronisationskonflikten.</span>
                            </div>
                        </label>

                        <div class="pt-2">
                            <button wire:click="saveNotifications" type="button"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                <span class="material-symbols-outlined text-lg">check</span>
                                <span>Einstellungen speichern</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 6: Rezeptschatz-Einstellungen -->
            @if ($tab === 'settings')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Rezeptschatz-Einstellungen</h3>
                        <p class="text-sm text-stone-500">Standardwerte für Rezepte und Benutzeroberfläche.</p>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Standard-Portionen für neue Rezepte</label>
                            <input wire:model="default_servings" type="number" min="1" max="20"
                                   class="w-32 px-3.5 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                            <p class="text-xs text-stone-500 mt-1">Dieser Wert wird beim Erstellen neuer Rezepte voreingestellt.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1">Standard-Ansicht der Rezeptübersicht</label>
                            <select wire:model="default_view"
                                    class="px-3.5 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-[#8ea92c] focus:outline-none">
                                <option value="grid">Kachelansicht (Bilder im Fokus)</option>
                                <option value="list">Listenansicht (Kompakt)</option>
                            </select>
                        </div>

                        <div class="pt-2">
                            <button wire:click="saveSettings" type="button"
                                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-[#8ea92c] hover:bg-[#7e9626] text-white font-semibold rounded-lg text-sm shadow-sm transition">
                                <span class="material-symbols-outlined text-lg">check</span>
                                <span>Präferenzen speichern</span>
                            </button>
                        </div>

                        <!-- Tablet App Anleitung -->
                        <div class="pt-6 border-t border-stone-200 space-y-3">
                            <h4 class="text-sm font-bold text-stone-900 flex items-center space-x-2">
                                <span class="material-symbols-outlined text-[#8ea92c]">tablet_android</span>
                                <span>Als App auf dem Samsung Tablet einrichten</span>
                            </h4>
                            <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 text-xs text-stone-700 space-y-2">
                                <p class="font-semibold text-stone-800">In 3 einfachen Schritten auf dem Startbildschirm installieren:</p>
                                <ol class="list-decimal list-inside space-y-1.5 text-stone-600">
                                    <li>Öffnen Sie auf dem Tablet im Browser Ihre Adresse: <span class="font-bold text-[#8ea92c]">https://rhz.internet-box.ch:8449</span></li>
                                    <li>Melden Sie sich einmalig an (das Häkchen bei <strong>„Angemeldet bleiben“</strong> ist automatisch gesetzt).</li>
                                    <li>Tippen Sie im Browser oben rechts auf die <strong>drei Punkte (⋮)</strong> und wählen Sie:  
                                        👉 <strong>„App installieren“</strong> bzw. <strong>„Zum Startbildschirm hinzufügen“</strong>.</li>
                                </ol>
                                <p class="text-[11px] text-stone-500 pt-1">
                                    ✨ Danach erscheint das Rezeptschatz-App-Icon auf Ihrem Startbildschirm und startet die App im Vollbildmodus – genau wie eine native Android-App aus dem Play Store!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 7: Ändere das Kopfzeilenbild -->
            @if ($tab === 'header-image')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Ändere das Kopfzeilenbild</h3>
                        <p class="text-sm text-stone-500">Passen Sie das Erscheinungsbild der Navigationsleiste an.</p>
                    </div>

                    <div class="p-6 rounded-xl border border-dashed border-stone-300 text-center space-y-3 bg-stone-50">
                        <span class="material-symbols-outlined text-4xl text-stone-400">palette</span>
                        <div>
                            <h4 class="text-sm font-semibold text-stone-800">Aktuelles Design: COOKmate Clean White</h4>
                            <p class="text-xs text-stone-500 mt-1 max-w-md mx-auto">
                                Der Header verwendet das originale, minimalistisch weiße Farbschema mit der typischen olivgrünen Akzentfarbe (<code class="font-mono text-brand-700">#8ea92c</code>).
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 8: Schließe deinen Account -->
            @if ($tab === 'close-account')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-rose-800">Schließe deinen Account</h3>
                        <p class="text-sm text-stone-500">Sicherheitsbereich für Ihr Benutzerkonto.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm space-y-3">
                        <div class="flex items-center space-x-2 font-bold">
                            <span class="material-symbols-outlined">warning</span>
                            <span>Achtung: Dies ist eine private Familien-Installation</span>
                        </div>
                        <p class="text-xs text-rose-800 leading-relaxed">
                            Da dies Ihre private Installation auf dem Raspberry Pi ist, kann das Haupt-Administratorkonto ({{ $user->email }}) nicht gelöscht werden, um den Datenzugriff nicht zu blockieren.
                        </p>
                    </div>
                </div>
            @endif

            <!-- WEITERE TABS (aus dem Menü "Mehr") -->
            @if ($tab === 'help')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Hilfe und Support</h3>
                        <p class="text-sm text-stone-500">Tipps zur Bedienung und Synchronisation.</p>
                    </div>

                    <div class="p-5 rounded-xl bg-emerald-50/70 border border-emerald-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <h4 class="font-bold text-emerald-950 flex items-center space-x-1.5">
                                <span class="material-symbols-outlined text-base text-[#8ea92c]">menu_book</span>
                                <span>Vollständiges Benutzerhandbuch</span>
                            </h4>
                            <p class="text-xs text-emerald-800">Umfassende Anleitung für alle Funktionen (Web, PWA, Raspberry Pi, KI-Nährwertrechner, Flutter-App & Sync).</p>
                        </div>
                        <a href="{{ route('manual') }}" class="inline-flex items-center space-x-1.5 px-4 py-2 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs font-bold rounded-lg shadow-xs transition shrink-0">
                            <span>Handbuch öffnen</span>
                            <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                    <div class="space-y-4 text-sm text-stone-700">
                        <div class="p-4 rounded-lg bg-stone-50 border border-stone-200">
                            <h4 class="font-bold text-stone-900 mb-1">Rezepte erfassen & bearbeiten</h4>
                            <p class="text-xs text-stone-600">Nutzen Sie den Button "Rezept erstellen", um Titel, Portionen, Kochzeiten, Zutaten mit 4-stufiger Mengenangabe und strukturierte Zubereitungsschritte zu erfassen.</p>
                        </div>
                        <div class="p-4 rounded-lg bg-stone-50 border border-stone-200">
                            <h4 class="font-bold text-stone-900 mb-1">Teilen mit Familienmitgliedern</h4>
                            <p class="text-xs text-stone-600">Aktivieren Sie beim Erstellen oder Bearbeiten die Option "In Familie teilen". Andere Familienmitglieder können das Rezept einsehen und mit einem Klick in ihre eigene Sammlung kopieren.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($tab === 'news')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-stone-900">Neuigkeiten</h3>
                        <p class="text-sm text-stone-500">Die neuesten Updates und Verbesserungen in Rezeptschatz.</p>
                    </div>
                    <div class="border-l-2 border-[#8ea92c] pl-4 space-y-2">
                        <span class="text-xs font-semibold text-brand-700">Version 1.1 &bull; November 2026</span>
                        <h4 class="font-bold text-stone-900 text-sm">Neue Header-Navigation & "Dein Konto"-Menü</h4>
                        <p class="text-xs text-stone-600">Authentisches COOKmate-Design mit direktem Zugriff auf Rezepte, Lesezeichen, Kategorien und Benutzerverwaltung integriert.</p>
                    </div>
                </div>
            @endif

            @if ($tab === 'websites')
                <div class="space-y-6">
                    <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                        <div>
                            <h3 class="text-lg font-bold text-stone-900">Unterstützte Webseiten</h3>
                            <p class="text-sm text-stone-500">Rezeptschatz unterstützt durch Schema.org (JSON-LD) hunderte Rezeptportale weltweit.</p>
                        </div>
                        <a href="{{ route('recipes.import.web') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-[#8ea92c] hover:bg-[#7c9526] text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            <span class="material-symbols-outlined text-sm">public</span>
                            <span>Web-Import öffnen</span>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <!-- Schweiz -->
                        <div class="p-4 bg-white rounded-xl border border-stone-200 shadow-2xs space-y-2">
                            <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-1">🇨🇭 Schweiz</h4>
                            <ul class="space-y-1.5 text-stone-600">
                                <li><a href="https://migusto.migros.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Migusto (Migros) ↗</a></li>
                                <li><a href="https://www.bettybossi.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Betty Bossi ↗</a></li>
                                <li><a href="https://www.swissmilk.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Swissmilk ↗</a></li>
                                <li><a href="https://fooby.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Fooby (Coop) ↗</a></li>
                                <li><a href="https://www.gutekueche.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">GuteKüche Schweiz ↗</a></li>
                                <li><a href="https://www.wildeisen.ch" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Annemarie Wildeisen ↗</a></li>
                                <li><a href="https://www.srf.ch/sendungen/landfrauenkueche" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">SRF Landfrauenküche ↗</a></li>
                            </ul>
                        </div>

                        <!-- Deutschland & Österreich -->
                        <div class="p-4 bg-white rounded-xl border border-stone-200 shadow-2xs space-y-2">
                            <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-1">🇩🇪 / 🇦🇹 DE & AT</h4>
                            <ul class="space-y-1.5 text-stone-600">
                                <li><a href="https://www.chefkoch.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Chefkoch.de ↗</a></li>
                                <li><a href="https://www.essen-und-trinken.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Essen & Trinken ↗</a></li>
                                <li><a href="https://www.lecker.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Lecker.de ↗</a></li>
                                <li><a href="https://www.kuechengoetter.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Küchengötter ↗</a></li>
                                <li><a href="https://www.einfachbacken.de" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Einfach Backen ↗</a></li>
                                <li><a href="https://www.gutekueche.at" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">GuteKüche Österreich ↗</a></li>
                            </ul>
                        </div>

                        <!-- International & Food Blogs -->
                        <div class="p-4 bg-white rounded-xl border border-stone-200 shadow-2xs space-y-2">
                            <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-1">🌍 International & Blogs</h4>
                            <ul class="space-y-1.5 text-stone-600">
                                <li><a href="https://www.bbcgoodfood.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">BBC Good Food ↗</a></li>
                                <li><a href="https://www.allrecipes.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Allrecipes ↗</a></li>
                                <li><a href="https://www.jamieoliver.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Jamie Oliver ↗</a></li>
                                <li><a href="https://www.seriouseats.com" target="_blank" rel="noopener" class="hover:text-[#8ea92c] transition hover:underline">Serious Eats ↗</a></li>
                                <li class="pt-1 text-[11px] text-stone-500 italic">Sowie unzählige Food-Blogs weltweit.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

