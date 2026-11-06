# 📖 Rezeptschatz – Das umfassende Benutzerhandbuch

Willkommen beim offiziellen Benutzerhandbuch für **Rezeptschatz**, Ihrem privaten, selbstgehosteten Rezeptverwaltungs- und Kochsystem.

Rezeptschatz wurde für den modernen Kochalltag entwickelt: Ob auf dem Laptop am Schreibtisch oder auf dem Tablet in der Küche – alle Ihre Rezepte, Menüpläne und Einkäufe sind jederzeit blitzschnell verfügbar.

---

## Inhaltsverzeichnis

1. [Überblick & Architektur](#1-überblick--architektur)
2. [Erste Schritte & PWA-Installation](#2-erste-schritte--pwa-installation-tablet--smartphone)
3. [Navigation & Benutzeroberfläche](#3-navigation--benutzeroberfläche)
4. [Rezepte finden & kochen](#4-rezepte-finden--kochen)
   - [Suche & Filter](#suche--filter)
   - [Die Rezeptansicht](#die-rezeptansicht)
   - [Dynamischer Portionsrechner](#dynamischer-portionsrechner)
   - [Druckansicht & PDF-Export](#druckansicht--pdf-export)
5. [Die 4 Wege für neue Rezepte](#5-die-4-wege-für-neue-rezepte)
   - [Weg 1: Rezept manuell erfassen](#weg-1-rezept-manuell-erfassen)
   - [Weg 2: Web-Rezept-Import (URL)](#weg-2-web-rezept-import-url)
   - [Weg 3: 📷 Kochbuch-Scan (OCR)](#weg-3--kochbuch-scan-ocr)
   - [Weg 4: 🤖 Mit KI & Kühlschrank kochen](#weg-4--mit-ki--kühlschrank-kochen-foto-scan)
6. [Einkaufsliste](#6-einkaufsliste)
7. [Menüplaner](#7-menüplaner)
8. [Kategorien verwalten](#8-kategorien-verwalten)
9. [Administration & Server-Befehle (Raspberry Pi)](#9-administration--server-befehle-raspberry-pi)

---

## 1. Überblick & Architektur

Rezeptschatz kombiniert ein modernes Web-Frontend (Laravel, Livewire, Tailwind CSS) mit einer robusten MySQL-Datenbank.

- **Entwicklungsumgebung:** Windows-Laptop mit Laragon (`http://Rezeptschatz.test:8081`).
- **Produktionsserver:** Raspberry Pi 5 zu Hause im 24/7-Betrieb (`https://rhz.internet-box.ch:8449`).
- **Bedienung:** Optimiert für Touchscreens (Samsung Galaxy Tablet), Smartphones und Desktop-Browser.
- **Künstliche Intelligenz:** Integrierte multimodale Bild- und Texterkennung über Google Gemini (`gemini-3-flash-preview`).

---

## 2. Erste Schritte & PWA-Installation (Tablet & Smartphone)

Rezeptschatz ist als **Progressive Web App (PWA)** konzipiert. Sie können die Anwendung wie eine echte native App auf Ihrem Tablet installieren – ohne störende Browser-Adressleiste und im praktischen Vollbildmodus.

### Installation auf dem Samsung / Android Tablet:
1. Öffnen Sie Google Chrome auf Ihrem Tablet.
2. Rufen Sie Ihre Adresse auf: `https://rhz.internet-box.ch:8449`.
3. Melden Sie sich mit Ihrem Benutzerkonto an.
4. Tippen Sie oben rechts im Browser auf das **3-Punkte-Menü** (⋮).
5. Wählen Sie **„App installieren“** oder **„Zum Startbildschirm hinzufügen“**.
6. Bestätigen Sie mit **„Installieren“**.

Ab sofort finden Sie das grüne Rezeptschatz-Icon auf Ihrem Startbildschirm und öffnen die Anwendung mit einem einzigen Fingertipp.

---

## 3. Navigation & Benutzeroberfläche

Die Navigation ist speziell für Tablets zweizeilig und übersichtlich aufgeteilt:

```
┌────────────────────────────────────────────────────────────────────────┐
│ [Logo] Rezeptschatz                                   [Robert Hofer >] │
├────────────────────────────────────────────────────────────────────────┤
│ Rezepte >  |  Neues Rezept suchen >  |  Einkaufsliste  |  Menüplaner   │
│            |                         |                 |  Mehr >       │
└────────────────────────────────────────────────────────────────────────┘
```

- **Logo / Titel:** Führt immer zurück zur Rezeptübersicht.
- **Benutzerkonto (oben rechts):** Zeigt den angemeldeten Benutzer; erlaubt Passwortänderungen und die Abmeldung.
- **Rezepte >:** Ihre gesamte Rezeptsammlung mit Schnellfiltern und Suchfunktion.
- **Neues Rezept suchen >:** Die zentrale Such- und Importmaske im bewährten COOKmate-Design (`/recipes/search`):
  - **Im Rezeptschatz suchen:** Schnelle Volltextsuche in Ihren eigenen Rezepten, Zutaten und Notizen.
  - **Aus dem Internet (URL) & Webseiten:** Rezepte im Web suchen (z. B. auf Chefkoch, Betty Bossi, Swissmilk) oder eine direkte Internet-Adresse eingeben und mit 1 Klick importieren.
  - **Lesezeichen:** Schneller Zugriff auf Ihre Lieblingsrezepte.
- **Einkaufsliste:** Ihre aktuellen Zutaten und Einkäufe mit Checkboxen zum Abhaken.
- **Menüplaner:** Wochenplaner für Mahlzeiten von Montag bis Sonntag.
- **Mehr > (Ausklappmenü):**
  - **📷 Rezept scannen:** Gedruckte Rezepte per Foto/OCR digitalisieren.
  - **🤖 Mit KI & Kühlschrank kochen:** Foto des Kühlschranks analysieren und Menü kreieren.
  - **COOKmate-Import:** Vorhandene Sicherungsdateien importieren.
  - **Kategorien verwalten:** Farben und Schlagwörter für Rezeptkategorien anpassen.

---

## 4. Rezepte finden & kochen

### Suche & Filter
Auf der Hauptseite (`/recipes`) finden Sie jedes Gericht in Sekundenschnelle:
- **Echtzeit-Suchfeld:** Tippen Sie beliebige Wörter ein (z. B. *„Hackfleisch“*, *„Risotto“*, *„Zitronen“*). Das System durchsucht Titel, Beschreibung und alle Zutaten live beim Tippen.
- **Kategorie-Buttons:** Ein Klick auf *„Fleisch“*, *„Geflügel“*, *„Fisch“*, *„Teigwaren“*, *„Gemüse“*, *„Käse“* etc. filtert die Liste sofort.
- **Favoriten-Filter (⭐):** Zeigt ausschliesslich Ihre Lieblingsrezepte an.
- **Sortierung:** Wechseln Sie zwischen *„Neueste zuerst“*, *„Titel (A–Z)“* oder *„Zubereitungszeit“*.

### Die Rezeptansicht
Tippen Sie auf eine Rezeptkarte, um die Kochansicht zu öffnen:
- **Grosses Rezeptbild:** Appetitliche Darstellung des fertigen Gerichts.
- **Zeiten:** Übersicht über Vorbereitungszeit, Kochzeit und Ruhezeit.
- **Nährwerte pro Portion:** Kalorien (kcal), Kohlenhydrate (g), Fett (g) und Eiweiss (g).
- **Zutatenliste:** Mit interaktiven Checkboxen. Beim Kochen können Sie jede Zutat abhaken, sobald sie in der Schüssel oder im Topf gelandet ist.
- **Zubereitungsschritte:** Grosse, gut lesbare Nummerierung der Einzelschritte.
- **Notizen:** Persönliche Tipps, Weinempfehlungen oder Backofeneinstellungen.

### Dynamischer Portionsrechner
Kochen Sie heute für 2, 4 oder 8 Personen?
- Nutzen Sie die **`+`** und **`-`** Buttons direkt neben der Portionsangabe.
- **Alle Mengenangaben der Zutaten rechnen sich in Echtzeit exakt um!**
- Auch Bruch- und Dezimalmengen (z. B. `250 g` ➔ `500 g` oder `1.5 EL` ➔ `3 EL`) bleiben mathematisch präzise.

### Zutaten mit 1 Klick zur Einkaufsliste
- Tippen Sie in der Rezeptansicht auf **„Zur Einkaufsliste hinzufügen“**.
- Alle benötigten Zutaten (mit der aktuell eingestellten Portionsmenge!) werden automatisch auf Ihre Einkaufsliste übertragen.

### Druckansicht & PDF-Export
- Tippen Sie oben rechts auf das Drucker-Symbol (**🖨️ Drucken**).
- Es öffnet sich eine druckeroptimierte Seite ohne störende Menüs und ohne dunkle Hintergründe.
- Ideal zum Ausdrucken auf Papier oder zum Abspeichern als PDF.

---

## 5. Die 4 Wege für neue Rezepte

Rezeptschatz bietet vier flexible Möglichkeiten, um neue Gerichte in Ihre Sammlung aufzunehmen:

---

### Weg 1: Rezept manuell erfassen
*Für eigene Familienrezepte und freie Kreationen.*

1. Wählen Sie im Menü **Rezepte > ➔ Neues Rezept erfassen** (oder Button `+ Neues Rezept`).
2. Füllen Sie die Grunddaten aus:
   - **Titel & Beschreibung**
   - **Portionen & Zeiten** (Vorbereitungs-, Koch- und Ruhezeit)
   - **Nährwerte** (Kalorien in kcal, Kohlenhydrate, Fett, Eiweiss in Gramm)
3. **Rezeptbild:** Foto hochladen (vom Tablet oder PC).
4. **Zutaten erfassen:**
   - Zeilenweise Menge, Einheit (g, ml, EL, TL, Stk, etc.) und Zutatenname eingeben.
   - Beliebig viele Zeilen mit `+ Zutat hinzufügen` ergänzen.
   - Reihenfolge per Pfeilsymbolen anpassen.
5. **Zubereitungsschritte:**
   - Text eingeben und mit `+ Weiterer Schritt` gliedern.
6. **Kategorien:** Setzen Sie Häkchen bei passenden Kategorien.
7. Auf **„Rezept speichern“** tippen.

---

### Weg 2: Neues Rezept suchen / Im Rezeptschatz & Web suchen
*Die universelle Rezeptsuche nach dem bewährten COOKmate-Muster (`/recipes/search`).*

Die Suchmaske bietet das vertraute Design mit dem grünen Weltkugel-Symbol, praktischen Dropdown-Filtern mit grünem Akzentrand und dem lachsfarbenen **„Suchen“**-Button:

1. **Option A – Stichwortsuche (z. B. „Zürcher Geschnetzeltes“, „Kürbissuppe“):**
   - Geben Sie den Suchbegriff in das Feld **„Suche oder URL-Eingabe“** ein.
   - Wählen Sie die **Sprache** (Deutsch, Französisch, Italienisch, Englisch) und die gewünschte **Seite**:
     - **Eigener Rezeptschatz:** Durchsucht Ihre persönliche Sammlung blitzschnell nach Titeln, Zutaten und Notizen.
     - **Alle kompatiblen Webseiten / Chefkoch.de:** Durchsucht das Web und listet passende Rezepte als Karten mit Foto, Zubereitungszeit, Schwierigkeitsgrad und Bewertung auf.
     - **Schweizer Kochportale (Betty Bossi, Swissmilk, Migusto, Fooby, GuteKüche.ch):** Bietet einen 1-Klick-Direktlink zur Rezeptsuche des jeweiligen Portals.
   - Tippen Sie auf **„Suchen“**.
   - Bei Web-Treffern genügt ein Klick auf **„📥 In Rezeptschatz importieren“**: Das Rezept wird mit Bild, Zutaten, Zubereitungsschritten und Nährwerten geladen und in der Vorschau angezeigt.
   - Mit einem Klick auf **„In Rezeptschatz speichern“** ist es dauerhaft in Ihrer Sammlung gesichert!

2. **Option B – Direkte Internet-Adresse (URL):**
   - Kopieren Sie den Link eines beliebigen Rezepts aus dem Browser (z. B. von Betty Bossi, Swissmilk, Chefkoch, GuteKüche, etc.).
   - Fügen Sie ihn in das Feld **„Suche oder URL-Eingabe“** ein und klicken Sie auf **„Suchen“**.
   - Rezeptschatz liest automatisch die strukturierten Daten (`Schema.org / JSON-LD`) aus und zeigt Ihnen die vollständige Vorschau mit Nährwerten und Zutaten.
   - Klicken Sie auf **„In Rezeptschatz speichern“** oder passen Sie das Rezept vor dem Speichern noch an.

---

### Weg 3: 📷 Kochbuch-Scan (OCR)
*Für Rezepte aus gedruckten Büchern, Magazinen oder handgeschriebenen Zetteln.*

1. Wählen Sie im Menü **Mehr > ➔ 📷 Rezept scannen** (`/recipes/scan`).
2. Tippen Sie auf **„Foto aufnehmen / Bild wählen“**:
   - Auf dem Tablet öffnet sich direkt Ihre Gerätekamera.
   - Fotografieren Sie die Rezeptseite gut ausgeleuchtet von oben ab.
3. Die integrierte Texterkennung (**Tesseract OCR**) liest den Text lokal ein.
4. Rezeptschatz analysiert die Absätze mit einem intelligenten Textparser:
   - Der Titel wird extrahiert.
   - Zutatenmengen (z. B. *„250 g Mehl“*, *„2 Eier“*) werden erkannt und in Mengen/Einheiten getrennt.
   - Die Zubereitungsschritte werden in Einzelschritte aufgeteilt.
5. Sie können den erkannten Text in der Vorschau prüfen und Tippfehler sofort korrigieren.
6. Tippen Sie auf **„Rezept speichern“** – fertig!

---

### Weg 4: 🤖 Mit KI & Kühlschrank kochen (Foto-Scan)
*Wenn Sie vor dem geöffneten Kühlschrank stehen und Inspiration aus Ihren Resten brauchen.*

1. Wählen Sie im Menü **Mehr > ➔ 🤖 Mit KI & Kühlschrank kochen** (`/recipes/chatgpt`).
2. **Kühlschrank fotografieren:**
   - Tippen Sie auf das Kamerasymbol und fotografieren Sie das Innere Ihres Kühlschranks oder Vorratsschranks.
3. **Kriterien nach Wunsch wählen:**
   - **Maximale Zeit:** z. B. 15, 30 oder 45 Minuten.
   - **Portionen:** z. B. 2 oder 4 Personen.
   - **Ernährungsweise:** Normal, Vegetarisch, Vegan, Low-Carb oder Schnell & einfach.
   - **Zusatzwünsche (optional):** z. B. *„Gerne mit Pasta“*, *„Keine Zwiebeln“*, *„Nicht zu scharf“*.
4. Tippen Sie auf den grünen Zauber-Button:  
   👉 **„✨ Rezept aus Kühlschrankfoto zaubern“**.
5. **Was die KI macht:**
   - **Google Gemini** analysiert Ihr Foto im Hintergrund in Sekundenschnelle.
   - Alle sichtbaren Lebensmittel werden erkannt und oben als grüne Zutaten-Chips gelistet (z. B. *Hackfleisch, Karotten, Käse, Eier, Rahm*).
   - Ein schmackhaftes Schweizer Alltagsrezept wird komponiert.
   - **Realistische Nährwerte pro Portion** (Kalorien, Kohlenhydrate, Fett, Eiweiss) werden automatisch berechnet.
6. **Rezept übernehmen:**
   - Tippen Sie auf **„Rezept in Rezeptschatz speichern“**: Das Gericht wird mitsamt Ihrem Kühlschrankfoto, Nährwerten und automatischer Kategoriezuordnung in Ihrer Datenbank abgelegt.
   - Alternativ tippen Sie auf **„Im Rezept-Editor bearbeiten“**, falls Sie vor dem Speichern noch eine Zutat anpassen möchten.

---

## 6. Einkaufsliste

Die Einkaufsliste (`/shopping-list`) hilft beim organisierten Einkauf:

- **Zutaten übernehmen:** Aus jeder Rezeptansicht mit einem Klick auf *„Zur Einkaufsliste hinzufügen“*.
- **Eigene Artikel hinzufügen:** Schnelleingabe oben für spontane Einkäufe (z. B. *„Milch“*, *„Butter“*, *„Mineralwasser“*).
- **Abhaken im Laden:** Tippen Sie auf die Checkbox eines Artikels. Der Eintrag wird durchgestrichen und rückt nach unten.
- **Bereinigen:** Ein Klick auf *„Erledigte entfernen“* löscht alle abgehakten Artikel mit einem Klick.
- **Liste leeren:** Setzt die gesamte Liste zurück, sobald der Grosseinkauf abgeschlossen ist.

---

## 7. Menüplaner

Der Menüplaner (`/meal-planner`) strukturiert Ihre Kochwoche von Montag bis Sonntag:

- **Wochennavigation:** Mit den Pfeiltasten können Sie bequem zwischen der aktuellen, der nächsten oder vergangenen Kalenderwochen wechseln.
- **Gericht zuweisen:** Wählen Sie bei einem Wochentag einfach das gewünschte Rezept aus Ihrer Sammlung aus.
- **Gesamteinkauf planen:** Über die Schaltfläche *„Zutaten der Woche auf Einkaufsliste“* landen alle benötigten Lebensmittel der geplanten Tage direkt auf Ihrer Einkaufsliste.

---

## 8. Kategorien & Schlagwörter verwalten

### Kategorien (`/categories`)
Kategorien bringen eine klare Grundstruktur in Ihre Sammlung (nach Hauptzutaten & Gängen):
- **Hauptzutaten:** Fleisch, Geflügel, Fisch, Teigwaren, Reis, Kartoffel, Gemüse, Pilz, Käse.
- **Mahlzeitenarten:** Suppe, Salat, Dessert, Backen.
- **Individuelle Farben:** Jede Kategorie kann eine eigene Erkennungsfarbe tragen (z. B. saftiges Grün für Gemüse, warmes Gold für Teigwaren).
- **Automatische Erkennung:** Anhand der Zutaten werden Rezepte beim Erstellen oder Scannen automatisch den passenden Kategorien zugewiesen.

### Schlagwörter / Tags (`/tags`)
Schlagwörter ergänzen die Kategorien um flexible Eigenschaften wie Anlässe, Zubereitungsarten oder Saisons:
- **Beispiele:** `#Schnell (< 30 Min.)`, `#Gäste & Fest`, `#Einfach & Alltag`, `#Klassiker`, `#Sommer`, `#Winter & Deftig`, `#Low-Carb`, `#Vegetarisch`, `#Resteverwertung`, `#Apéro`, `#Backofen`, `#Schweizer Küche`.
- **Eigene Schlagwörter anlegen:** In der Schlagwörter-Verwaltung (`Rezepte > ➔ Schlagwörter`) oder direkt beim Erfassen eines Rezepts durch Eintippen und Enter-Drücken.
- **Schnellfilter in der Rezeptliste:** In der Rezeptübersicht finden Sie alle Schlagwörter als praktische Filter-Chips. Ein Fingertipp genügt, um z. B. nur alle Rezepte für *„Gäste & Fest“* oder *„Schnell“* anzuzeigen.
- **Verlinkung im Rezept:** In jeder Rezeptansicht sind die Schlagwörter klickbar und führen direkt zur gefilterten Liste.

---

## 9. Administration & Server-Befehle (Raspberry Pi)

Für Wartungsarbeiten auf Ihrem Raspberry Pi (`pironman`) stehen Ihnen folgende nützliche Konsolenbefehle zur Verfügung:

### Software-Aktualisierung (Git Pull)
Um die neuesten Funktionen und Anpassungen auf den Server zu laden:
```bash
cd /var/www/rezeptschatz
sudo git reset --hard origin/main
sudo git pull
php artisan optimize:clear
sudo systemctl restart php*-fpm
```

### KI-Verbindung und Modelle testen
Prüft die Erreichbarkeit von Google Gemini und zeigt alle freigeschalteten Modelle:
```bash
php artisan ai:test
```

### Nährwerte nachträglich eintragen / anpassen
Setzt oder korrigiert die Nährwerte eines bestehenden Rezepts über die Konsole:
```bash
# Automatisches Setzen für die Hackfleisch-Pfanne:
php artisan recipe:set-nutrition

# Beliebiges anderes Rezept gezielt anpassen:
php artisan recipe:set-nutrition "Risotto" --calories=480 --carbs=65 --fat=14 --protein=11
```

### Automatische Kategorienerkennung über alle Rezepte laufen lassen
Verknüpft alle bestehenden Rezepte anhand ihrer Zutaten mit den richtigen Hauptkategorien:
```bash
php artisan recipes:assign-ingredient-categories
```

### Caches leeren (bei Layout- oder Konfigurationsänderungen)
```bash
php artisan optimize:clear
```

---

## Zusammenfassung & Tipps für die Küche

1. **Tablet-Halterung:** Stellen Sie Ihr Tablet mit einer abwischbaren Hülle in die Küche. Im PWA-Vollbildmodus bleibt der Bildschirm übersichtlich und Sie können Zutaten mit nassen Fingern mühelos abhaken.
2. **Kühlschrank-Scan:** Achten Sie beim Fotografieren des Kühlschranks darauf, dass das Innenlicht gut leuchtet und auch die Gemüseschublade oder die Seitentür leicht sichtbar sind – so erkennt Google Gemini noch mehr passende Zutaten.
3. **Portionen umrechnen:** Vor dem Kochen kurz auf `+` oder `-` tippen, um Fleisch- und Flüssigkeitsmengen exakt an die Gästezahl anzupassen.

*Viel Freude beim Kochen und Geniessen mit Ihrem persönlichen Rezeptschatz!* 🍳🥗🥖

