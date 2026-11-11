# 📖 Rezeptschatz

> **Das moderne, Schweizer Taschenmesser für Rezeptverwaltung, smartes Kochen, KI-Nährwertanalyse und Menüplanung.**  
> Self-hosted auf dem Heimserver (z. B. Raspberry Pi 5) oder im lokalen Netzwerk – mit nativer mobiler Begleiter-App (Flutter) für Android und iOS.

---

## 🌟 Übersicht & Philosophie

**Rezeptschatz** ist eine massgeschneiderte, webbasierte Plattform zur Verwaltung der persönlichen Rezeptsammlung, kombiniert mit intelligenter Küchenassistenz. Das System befreit Sie von werbeüberladenen Koch-Websites, verstreuten Notizen und unübersichtlichen Rezeptbüchern.

Egal ob selbst kreiertes Familienrezept, ein schneller Web-Import von beliebten Portalen, ein Foto aus einem Kochbuch oder der spontane Blick in den Kühlschrank: Rezeptschatz strukturiert alle Rezepte einheitlich, berechnet auf Wunsch Nährwerte per KI, skaliert Zutatenmengen dynamisch und synchronisiert sich mit dem Smartphone für den Offline-Einsatz im Supermarkt oder in der Ferienwohnung.

---

## ✨ Die Kernfunktionen

### 1. 🔍 Rezeptverwaltung, Suche & Sichtbarkeitssteuerung
- **Echtzeit-Volltextsuche:** Durchsucht blitzschnell Titel, Notizen und Zutaten während des Tippens.
- **Kategorien & Tags:** Filterung nach Hauptzutaten (*Fleisch, Geflügel, Fisch, Teigwaren, Reis, Gemüse, Käse, Desserts* etc.) sowie situativen Schlagwörtern (`#Schnell`, `#Gäste & Fest`, `#Backofen`, `#Low-Carb`, `#Schweizer Küche`).
- **Autoren-Reiter & Sichtbarkeit (`is_shared`):**
  - **„Alle Rezepte“:** Zeigt ausschliesslich freigegebene Rezepte (eigene private Rezepte sind geschützt und hier ausgeblendet).
  - **„Meine“:** Zeigt sämtliche eigene Rezepte (sowohl geteilte als auch ungeteilte mit Kennzeichnung `🔒 Privat`).
  - **„Familie / Geteilt“:** Zeigt Rezepte, die andere Benutzer oder Familienmitglieder freigegeben haben.
- **1-Klick-Freigabe:** Direkt in der Rezeptansicht lässt sich ein Rezept per Klick auf `🌐 Freigegeben` bzw. `🔒 Privat` augenblicklich umschalten.
- **Favoriten-Funktion (⭐):** Lieblingsrezepte mit einem Stern hervorheben und mit einem Klick filtern.

---

### 2. 🍳 Ergonomische Kochansicht (Auge-freundlich)
- **Strukturierte Mengenspalte:**
  - **Ganz links:** Alle Mengenangaben und Einheiten (`400 g`, `2 Stück`, `1 TL`) stehen in einer fixen, linksbündigen Spalte sauber untereinander.
  - **Rechts daneben:** Der Zutatennamen und spezifische Zubereitungshinweise (*„fein gehackt“*, *„mittelgross“*). So erfasst das Auge beim Hantieren am Herd die Mengen sekundenschnell.
- **Dynamischer Portionsrechner:** Über die Tasten `+` und `-` passen sich alle Zutatenmengen in Echtzeit mathematisch exakt an die gewünschte Gästezahl an.
- **Interaktives Kochen:** Zutaten können per Checkbox direkt auf dem Bildschirm abgehakt werden.
- **1-Klick-Einkaufsliste:** Mit einem Klick auf **„Auf Einkaufsliste“** werden die Zutaten in der berechneten Portionsmenge sofort übernommen.
- **Druck- & PDF-Ansicht (🖨️):** Klare, druckoptimierte Seite ohne Navigationselemente für die analoge Küchenarbeit.

---

### 3. 📥 Die 4 Wege zu neuen Rezepten

Rezeptschatz bietet maximale Flexibilität bei der Erfassung neuer Gerichte:

1. **Weg 1: Manuelle Erfassung (`/recipes/create`)**
   - Eigene Rezepte mit Titel, Zeiten, Portionen, Freigabestatus (`[✓] Freigegeben`), Lieblingsmarkierung, hochgeladenen Bildern, flexiblen Zutatenzeilen und Markdown-Zubereitungsschritten erfassen.
2. **Weg 2: Universeller Web-Import & Rezeptsuche (`/recipes/search` & `/recipes/import-web`)**
   - Unterstützt den weltweiten **Schema.org (JSON-LD)** Standard für Kochportale.
   - Kompatibel mit **Betty Bossi, Swissmilk, Migusto, Fooby, GuteKüche.ch, Chefkoch, Leckerschmecker, Lecker.de, Essen & Trinken** u. v. m.
   - Entweder gezielt per Stichwort suchen (COOKmate-Prinzip) oder einfach eine Rezept-URL einfügen – Bild, Zutaten, Zeiten und Schritte werden vollautomatisch strukturiert importiert.
3. **Weg 3: 📷 Kochbuch-Scan per OCR (`/recipes/scan`)**
   - Gedruckte Seiten aus Kochbüchern, Zeitschriften oder handschriftliche Rezeptkarten fotografieren.
   - Die integrierte Texterkennung (**Tesseract OCR**) trennt Titel, Zutatenmengen und Zubereitungstexte automatisch auf.
4. **Weg 4: 🤖 „Mit KI & Kühlschrank kochen“ (`/recipes/chatgpt`)**
   - Kühlschrank- oder Vorratsfoto hochladen, gewünschte Kriterien wählen (Zeitaufwand, Portionen, Ernährung wie Vegetarisch oder Low-Carb).
   - **Google Gemini** analysiert das Foto, erkennt vorhandene Vorräte, kreiert ein passendes Rezept und berechnet automatisch realistische Nährwerte pro Portion.

---

### 4. 🥑 Intelligenter Nährwert-Rechner & Kategorie-Zuweisung

- **Web-Assistent (`/recipes/nutrition-calculator`):**
  - Rezept aus Dropdown auswählen, per UUID oder Rezept-URL ansteuern.
  - Ermittelt über **Google Gemini** präzise Werte für:
    - **Kalorien (kcal)**
    - **Eiweiss / Protein (g)**
    - **Fett (g)**
    - **Kohlenhydrate (g)**
  - **Automatische Kategorienerkennung:** Weist Rezepten anhand der Zutaten im Hintergrund selbstständig die zutreffenden Kategorien zu (*Fleisch, Teigwaren, Reis, Gemüse* etc.).
  - **Live-Vorschau & Feinanpassung:** Werte können vor dem Speichern beliebig von Hand justiert werden.
  - **Commit:** Ein Klick auf **„In Datenbank speichern (Commit)“** schreibt die Werte dauerhaft in die Datenbank und erhöht den Versionszähler für den mobilen Datenabgleich.
- **Konsolen-Power (`php artisan get:nutritional-val`):**
  - Direkte Analyse und Speicherung von Nährwerten über das Server-Terminal.

---

### 5. 🛒 Interaktive Einkaufsliste (`/shopping-list`)
- Zutatenübernahme direkt aus Rezepten in der exakt berechneten Portionsmenge.
- Schnelleingabe für freie Notizen und Spontankäufe (*„Milch“*, *„Mineralwasser“*).
- Interaktives Abhaken im Geschäft (abgehakte Artikel werden durchgestrichen und ans Ende sortiert).
- Ein-Klick-Bereinigung erledigter Einträge.

---

### 6. 📅 Wochen-Menüplaner (`/meal-planner`)
- Wochenansicht von Montag bis Sonntag mit bequemer Kalenderwochen-Navigation.
- Direkte Zuweisung von Rezepten aus Ihrer Sammlung auf Wochentage.
- **„Zutaten der Woche auf Einkaufsliste“:** Überträgt alle benötigten Lebensmittel der gesamten Woche auf einen Klick auf die Einkaufsliste.

---

### 7. 📱 Mobile Begleiter-App (Flutter für Android & iOS)
- Native mobile App mit flüssiger Performance.
- **Modernes Design:** Randloses hellblaues App-Icon mit Unterstützung für **Android Adaptive Icons**.
- **Zwei-Wege-Synchronisation:** Blitzschneller Abgleich mit der MariaDB-Datenbank über eine optimierte REST-Schnittstelle.
- **Vollständige Offline-Fähigkeit:** Einmal synchronisiert stehen alle Rezepte, Zubereitungsschritte und Zutatenlisten ohne Internetverbindung zur Verfügung.

---

## 🛠️ Technologie-Stack

| Komponente | Technologie | Details |
|---|---|---|
| **Backend-Framework** | [Laravel 12](https://laravel.com) | Elegantes PHP-Framework mit Eloquent ORM & Sanctum |
| **Frontend & Reaktivität** | [Livewire 3/4](https://livewire.laravel.com) & [Alpine.js](https://alpinejs.dev) | Reaktive Benutzeroberfläche ohne schweres SPA-Framework |
| **Styling & Design** | [Tailwind CSS](https://tailwindcss.com) | Responsive Layouts für Smartphone, Tablet und Desktop |
| **Datenbank** | MariaDB / MySQL | Relationales Schema mit UUID-Primärschlüsseln & Sync-Versioning |
| **Künstliche Intelligenz** | [Google Gemini API](https://ai.google.dev) | Multimodale Bildanalyse, Rezeptgenerierung & Nährwertberechnung |
| **Texterkennung** | [Tesseract OCR](https://github.com/tesseract-ocr/tesseract) | Lokale Texterkennung für Rezeptbuch-Scans |
| **Mobile App** | [Flutter / Dart](https://flutter.dev) | Cross-Platform App für Android & iOS |
| **Ziel-Plattform** | Linux / Raspberry Pi 5 | Nginx/Apache, PHP 8.3+, MariaDB, SSL (`https://...`) |

---

## 🚀 Installation & Lokale Entwicklung

### Voraussetzungen
- PHP >= 8.3 (mit Extensions: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `gd` oder `imagick`)
- Composer >= 2.x
- Node.js >= 20.x & npm
- MariaDB oder MySQL
- Optional: Tesseract OCR (für die lokale Texterkennung von Buch-Scans)

### Schritt-für-Schritt Einrichtung

1. **Repository klonen:**
   ```bash
   git clone https://github.com/roberthoferzuerich-alt/rezeptschatz.git
   cd rezeptschatz
   ```

2. **PHP-Abhängigkeiten installieren:**
   ```bash
   composer install
   ```

3. **Umgebungskonfiguration anlegen:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Datenbank & Dienste konfigurieren (`.env`):**
   ```env
   APP_NAME="Rezeptschatz"
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=rezeptschatz
   DB_USERNAME=rezeptschatz_user
   DB_PASSWORD=ihr_passwort

   GEMINI_API_KEY=ihr_google_gemini_api_key
   ```

5. **Datenbank migrieren und initialisieren:**
   ```bash
   php artisan migrate --seed
   ```

6. **Frontend-Assets installieren und bauen:**
   ```bash
   npm install
   npm run build
   ```

7. **Lokalen Entwicklungsserver starten:**
   ```bash
   php artisan serve
   # oder npm run dev für paralleles Vite-Live-Reload
   ```

---

## 🖥️ Server-Betrieb (Raspberry Pi / Linux)

Für den reibungslosen Betrieb auf einem Raspberry Pi (`pironman:/var/www/rezeptschatz`):

### Aktualisierung auf den neuesten Git-Stand
```bash
cd /var/www/rezeptschatz
sudo git reset --hard origin/main
sudo git pull
php artisan optimize:clear
sudo systemctl restart php*-fpm
```

> **Wichtiger Hinweis zu Dateirechten & Git:**  
> Falls Git Meldungen über „dubiose Besitzverhältnisse“ ausgibt, einmalig ausführen:
> ```bash
> git config --global --add safe.directory /var/www/rezeptschatz
> sudo chown -R pi:www-data /var/www/rezeptschatz/.git
> sudo chown -R www-data:www-data /var/www/rezeptschatz/storage /var/www/rezeptschatz/bootstrap/cache
> sudo chmod -R 775 /var/www/rezeptschatz/storage /var/www/rezeptschatz/bootstrap/cache
> ```

---

## ⌨️ Nützliche Artisan-Konsolenbefehle

| Befehl | Beschreibung |
|---|---|
| `php artisan get:nutritional-val "<URL|UUID>"` | Berechnet Nährwerte per KI und gibt eine formatierte Tabelle aus |
| `php artisan get:nutritional-val "<URL|UUID>" --save` | Berechnet Nährwerte und speichert sie direkt in der Datenbank |
| `php artisan recipes:assign-ingredient-categories` | Analysiert Zutaten aller Rezepte und weist automatisch Kategorien zu |
| `php artisan ai:test` | Prüft die Verbindung zur Google Gemini API und listet verfügbare Modelle auf |
| `php artisan optimize:clear` | Leert sämtliche Framework-Caches (Route, View, Config, Events) |

---

## 📚 Weiterführende Dokumentation

Eine ausführliche Schritt-für-Schritt-Anleitung mit praktischen Kochempfehlungen finden Sie im Verzeichnis:
- [📖 Ausführliche Benutzeranleitung (BENUTZERANLEITUNG.md)](Documentation/BENUTZERANLEITUNG.md)

---

## 📄 Lizenz & Danksagung

Entwickelt mit Leidenschaft für gutes Essen und handwerklich saubere Software.  
*Viel Freude beim Kochen und Geniessen mit Ihrem persönlichen Rezeptschatz!* 🍳🥖🥗
