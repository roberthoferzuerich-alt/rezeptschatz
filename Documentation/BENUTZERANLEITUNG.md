# 📖 Rezeptschatz – Das umfassende Benutzerhandbuch

Willkommen beim offiziellen Benutzerhandbuch für **Rezeptschatz**, Ihrem privaten, selbstgehosteten Rezeptverwaltungs- und Kochsystem.

Rezeptschatz wurde für den modernen Kochalltag entwickelt: Ob auf dem Laptop am Schreibtisch, auf dem Tablet in der Küche oder auf dem Smartphone unterwegs – alle Ihre Rezepte, Menüpläne und Einkäufe sind jederzeit blitzschnell verfügbar.

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
5. [Die Wege für neue Rezepte](#5-die-wege-für-neue-rezepte)
   - [Weg 1: Rezept manuell erfassen](#weg-1-rezept-manuell-erfassen)
   - [Weg 2: Web-Rezept-Import & Suche (inkl. Leckerschmecker.de)](#weg-2-neues-rezept-suchen--im-rezeptschatz--web-suchen)
   - [Weg 3: 📷 Kochbuch-Scan (OCR)](#weg-3--kochbuch-scan-ocr)
   - [Weg 4: 🤖 Mit KI & Kühlschrank kochen (Foto-Scan)](#weg-4--mit-ki--kühlschrank-kochen-foto-scan)
6. [🥑 Nährwerte ermitteln & Kategorien zuweisen (KI-Rechner)](#6--nährwerte-ermitteln--kategorien-zuweisen-ki-rechner)
   - [Aufruf & Eingabe](#aufruf--eingabe)
   - [Automatische KI-Berechnung & Zutaten-Kategorien](#automatische-ki-berechnung--zutaten-kategorien)
   - [Nährwerte überprüfen, anpassen & Commit](#nährwerte-überprüfen-anpassen--commit)
7. [Einkaufsliste](#7-einkaufsliste)
8. [Menüplaner](#8-menüplaner)
9. [Kategorien & Schlagwörter verwalten](#9-kategorien--schlagwörter-verwalten)
10. [📱 Mobile Flutter-App: Synchronisation & Offline-Nutzung](#10--mobile-flutter-app-synchronisation--offline-nutzung)
    - [Neues hellblaues App-Icon](#neues-hellblaues-app-icon)
    - [Rezepte aus der Datenbank aktualisieren (Sync)](#rezepte-aus-der-datenbank-aktualisieren-sync)
    - [Offline-Fähigkeit in der Küche](#offline-fähigkeit-in-der-küche)
11. [Administration & Server-Befehle (Raspberry Pi)](#11-administration--server-befehle-raspberry-pi)
    - [Software-Aktualisierung (Git Pull)](#software-aktualisierung-git-pull)
    - [Neuer Nährwert-Konsolenbefehl (`get:nutritional-val`)](#neuer-nährwert-konsolenbefehl-getnutritional-val)
    - [Dateirechte & Berechtigungen (`www-data`)](#dateirechte--berechtigungen-www-data)
    - [Caches leeren & Wartung](#caches-leeren--wartung)

---

## 1. Überblick & Architektur

Rezeptschatz kombiniert ein modernes Web-Frontend (Laravel, Livewire, Tailwind CSS) mit einer robusten MySQL/MariaDB-Datenbank und einer nativen Flutter-App.

- **Entwicklungsumgebung:** Windows-Laptop mit Laragon (`http://Rezeptschatz.test:8081`).
- **Produktionsserver:** Raspberry Pi zu Hause im 24/7-Betrieb (`https://rhz.internet-box.ch:8449`).
- **Bedienung:** Optimiert für Touchscreens (Samsung Galaxy Tablet), Smartphones (Flutter & PWA) und Desktop-Browser.
- **Künstliche Intelligenz:** Integrierte multimodale Bild- und Texterkennung sowie Nährwertanalyse über Google Gemini (`gemini-3-flash-preview`).

---

## 2. Erste Schritte & PWA-Installation (Tablet & Smartphone)

Rezeptschatz ist als **Progressive Web App (PWA)** konzipiert. Sie können die Web-Anwendung wie eine native App auf Ihrem Tablet installieren – ohne störende Browser-Adressleiste und im Vollbildmodus.

### Installation auf dem Samsung / Android Tablet:
1. Öffnen Sie Google Chrome auf Ihrem Tablet.
2. Rufen Sie Ihre Adresse auf: `https://rhz.internet-box.ch:8449`.
3. Melden Sie sich mit Ihrem Benutzerkonto an.
4. Tippen Sie oben rechts im Browser auf das **3-Punkte-Menü** (⋮).
5. Wählen Sie **„App installieren“** oder **„Zum Startbildschirm hinzufügen“**.
6. Bestätigen Sie mit **„Installieren“**.

Ab sofort finden Sie das Rezeptschatz-Icon auf Ihrem Startbildschirm und öffnen die Anwendung mit einem Fingertipp.

---

## 3. Navigation & Benutzeroberfläche

Die Navigation ist übersichtlich aufgeteilt:

```
┌────────────────────────────────────────────────────────────────────────┐
│ [Logo] Rezeptschatz                                   [Robert Hofer >] │
├────────────────────────────────────────────────────────────────────────┤
│ Rezepte >  |  Neues Rezept suchen >  |  Einkaufsliste  |  Menüplaner   │
│            |                         |                 |  Mehr >       │
└────────────────────────────────────────────────────────────────────────┘
```

- **Logo / Titel:** Führt immer zurück zur Rezeptübersicht.
- **Benutzerkonto (oben rechts):** Zeigt den angemeldeten Benutzer; erlaubt Passwortänderungen, KI-Einstellungen und Abmeldung.
- **Rezepte > (Ausklappmenü):**
  - **Liste der Rezepte:** Ihre gesamte Rezeptsammlung mit Schnellfiltern und Suchfunktion.
  - **Rezepte meiner Freunde:** Geteilte Rezepte anderer Benutzer.
  - **Rezept erstellen:** Ein neues Rezept von Hand erfassen.
  - **🥑 Nährwerte ermitteln (KI):** URL oder ID eingeben, Nährwerte berechnen und Zutatenkategorien automatisch zuweisen.
  - **Importiere Rezepte:** Webseiten-Import oder COOKmate-Backup einlesen.
  - **Kategorien & Schlagwörter:** Verwaltung der Hauptzutaten und Tag-Filter.
- **Neues Rezept suchen >:**
  - **Im Rezeptschatz suchen:** Schnelle Volltextsuche in Ihren eigenen Rezepten, Zutaten und Notizen.
  - **Aus dem Internet (URL) & Webseiten:** Rezepte im Web suchen (z. B. auf Chefkoch, Betty Bossi, Swissmilk, Leckerschmecker) oder eine direkte Internet-Adresse eingeben und mit 1 Klick importieren.
  - **Lesezeichen:** Schneller Zugriff auf Ihre Lieblingsrezepte.
- **Einkaufsliste:** Ihre aktuellen Zutaten und Einkäufe mit Checkboxen zum Abhaken.
- **Menüplaner:** Wochenplaner für Mahlzeiten von Montag bis Sonntag.

---

## 4. Rezepte finden & kochen

### Suche, Filter & Sichtbarkeit
Auf der Hauptseite (`/recipes`) finden Sie jedes Gericht in Sekundenschnelle:
- **Autoren-Reiter (Sichtbarkeit):**
  - **„Alle Rezepte“ (Gesamtansicht):** Zeigt ausschliesslich freigegebene Rezepte. Eigene Rezepte, die Sie als privat markiert haben, sind hier zum Schutz Ihrer Privatsphäre ausgeblendet.
  - **„Meine“:** Zeigt alle Ihre persönlichen Rezepte (sowohl freigegebene als auch private). Private Rezepte erkennen Sie am dunklen Badge `🔒 Privat`.
  - **„Familie / Geteilt“:** Zeigt freigegebene Rezepte anderer Benutzer oder Familienmitglieder.
- **Echtzeit-Suchfeld:** Tippen Sie beliebige Wörter ein (z. B. *„Hackfleisch“*, *„Risotto“*, *„Zitronen“*). Das System durchsucht Titel, Beschreibung und alle Zutaten live beim Tippen.
- **Kategorie-Buttons:** Ein Klick auf *„Fleisch“*, *„Geflügel“*, *„Fisch“*, *„Teigwaren“*, *„Gemüse“*, *„Käse“* etc. filtert die Liste sofort.
- **Schlagwörter / Tags:** Filter-Chips wie `#Schnell`, `#Gäste & Fest`, `#Backofen` grenzen die Treffer weiter ein.
- **Favoriten-Filter (⭐):** Zeigt ausschliesslich Ihre Lieblingsrezepte an.
- **Sortierung:** Wechseln Sie zwischen *„Neueste zuerst“*, *„Titel (A–Z)“* oder *„Zubereitungszeit“*.
- **Ansichtsumschalter:** Wechseln Sie per Klick zwischen grosser Kachel- und kompakter Listenansicht.

### Die Rezeptansicht & ergonomisches Zutatenlayout
Tippen Sie auf eine Rezeptkarte, um die Kochansicht zu öffnen:
- **Grosses Rezeptbild:** Appetitliche Darstellung des fertigen Gerichts.
- **Freigabestatus (1-Klick-Umschalter):** Direkt oben in der Aktionsleiste sehen Sie, ob Ihr Rezept `🌐 Freigegeben` oder `🔒 Privat` ist. Ein einfacher Klick schaltet den Status sofort um!
- **Zeiten:** Vorbereitungszeit, Kochzeit und Ruhezeit.
- **Nährwerte-Leiste pro Portion:**
  - Kalorien (kcal), Eiweiss (g), Fett (g) und Kohlenhydrate (g).
  - Über den Button **„Anpassen“** bzw. **„Nährwerte berechnen“** können fehlende Nährwerte per KI kalkuliert oder korrigiert werden.
- **Ergonomisches Zutatenlayout (Auge-freundlich):**
  - **Ganz links:** Alle Mengenangaben und Einheiten (z. B. `400 g`, `2 Stück`, `1 TL`) stehen in einer festen, linksbündigen Spalte sauber untereinander ausgerichtet.
  - **Rechts daneben:** Der Zutatennamen und darunter die Detailbeschreibung (z. B. *„fein gehackt“*, *„mittelgross“*). So erfasst das Auge beim Kochen die Mengen blitzschnell ohne langes Suchen.
- **Dynamischer Portionsrechner:** Mit `+` und `-` Portionen ändern – alle Mengenangaben rechnen sich in Echtzeit mathematisch exakt um!
- **Zutaten zur Einkaufsliste:** Mit einem Klick auf **„Auf Einkaufsliste“** landen alle Zutaten direkt in der Einkaufsliste.
- **Zubereitungsschritte:** Gut lesbare Nummerierung der Einzelschritte mit Markdown-Unterstützung.
- **Druckansicht & PDF-Export (🖨️):** Druckoptimierte Seite ohne störende Menüleisten für die Küche.

---

## 5. Die Wege für neue Rezepte

Rezeptschatz bietet vier flexible Möglichkeiten, um neue Gerichte in Ihre Sammlung aufzunehmen:

---

### Weg 1: Rezept manuell erfassen
*Für eigene Familienrezepte und freie Kreationen.*

1. Wählen Sie im Menü **Rezepte > ➔ Rezept erstellen** (`/recipes/create`).
2. Füllen Sie die Grunddaten aus (Titel, Beschreibung, Portionen, Zeiten).
3. **Freigabe & Favorit:** 
   - Über das Kontrollkästchen **„Freigegeben“** (standardmässig aktiviert) steuern Sie, ob das Rezept in der Gesamtansicht für andere sichtbar ist oder als `🔒 Privat` nur für Sie selbst.
   - Mit dem **⭐ Favorit**-Kästchen kennzeichnen Sie persönliche Lieblingsrezepte.
4. **Rezeptbild:** Foto hochladen (vom Tablet, Smartphone oder PC).
5. **Zutaten erfassen:** Zeilenweise Menge, Einheit und Zutat eingeben.
6. **Zubereitungsschritte:** Text eingeben und beliebig viele Schritte hinzufügen.
7. **Kategorien & Schlagwörter:** Häkchen bei passenden Kategorien setzen.
8. Auf **„Rezept speichern“** tippen.

---

### Weg 2: Neues Rezept suchen / Im Rezeptschatz & Web suchen
*Die universelle Rezeptsuche nach dem bewährten COOKmate-Muster (`/recipes/search` & `/recipes/import-web`).*

Rezeptschatz unterstützt das universelle **Schema.org (JSON-LD)** Format und funktioniert mit hunderten Kochportalen weltweit, darunter:
- **Schweiz:** Betty Bossi, Swissmilk, Migusto, Fooby, GuteKüche.ch
- **Deutschland / International:** Chefkoch.de, **Leckerschmecker.de**, Lecker.de, Essen & Trinken

1. **Option A – Stichwortsuche:**
   - Suchbegriff eingeben (z. B. *„Tomaten-Clafoutis“*, *„Couscous-Pfanne“*).
   - Trefferliste durchsehen und bei einem Rezept auf **„📥 In Rezeptschatz importieren“** tippen.
   - Bild, Zutaten, Schritte und Nährwerte werden automatisch übernommen.

2. **Option B – Direkte Internet-Adresse (URL):**
   - Kopieren Sie einen beliebigen Rezept-Link aus dem Browser (z. B. von Leckerschmecker oder Betty Bossi).
   - Fügen Sie ihn in das URL-Feld ein und klicken Sie auf **„Rezept analysieren“**.
   - Die Daten werden sekundenschnell strukturiert ausgelesen.
   - Klicken Sie auf **„In Rezeptschatz speichern“** – fertig!

---

### Weg 3: 📷 Kochbuch-Scan (OCR)
*Für Rezepte aus gedruckten Büchern, Zeitschriften oder handschriftlichen Notizen.*

1. Wählen Sie im Menü **Rezepte > ➔ Scann ein Rezept** (`/recipes/scan`).
2. Fotografieren Sie die Rezeptseite gut ausgeleuchtet ab.
3. Die integrierte Texterkennung (**Tesseract OCR**) liest den Text ein.
4. Titel, Zutatenmengen und Zubereitungsschritte werden automatisch getrennt und strukturiert.
5. In der Vorschau kurz prüfen und auf **„Rezept speichern“** tippen.

---

### Weg 4: 🤖 Mit KI & Kühlschrank kochen (Foto-Scan)
*Wenn Sie vor dem geöffneten Kühlschrank stehen und Inspiration aus Resten brauchen.*

1. Wählen Sie im Menü **Rezepte > ➔ Mit KI & Kühlschrank kochen** (`/recipes/chatgpt`).
2. Fotografieren Sie das Innere Ihres Kühlschranks oder Vorratsschranks.
3. Kriterien wählen (Zeitaufwand, Portionen, Ernährungsweise wie Normal, Vegetarisch, Low-Carb).
4. Tippen Sie auf **„✨ Rezept aus Kühlschrankfoto zaubern“**.
5. **Google Gemini** erkennt alle sichtbaren Zutaten, kreiert ein massgeschneidertes Rezept und berechnet automatisch realistische Nährwerte pro Portion.
6. Mit einem Klick auf **„Rezept in Rezeptschatz speichern“** dauerhaft übernehmen.

---

## 6. 🥑 Nährwerte ermitteln & Kategorien zuweisen (KI-Rechner)

*Der intelligente Assistent zum automatischen Kalkulieren von Nährwerten und Ordnen von Rezepten (`/recipes/nutrition-calculator`).*

Haben Sie Rezepte ohne Nährwerte oder möchten Sie ein Gericht auf Kalorien und Makronährstoffe analysieren? Mit dem Nährwert-Rechner erledigen Sie das in wenigen Sekunden:

### Aufruf & Eingabe
1. Wählen Sie im Menü **Rezepte > ➔ Nährwerte ermitteln** (oder klicken Sie in einer Rezeptansicht auf **„Nährwerte berechnen / Anpassen“**).
2. Geben Sie die **Rezept-URL** ein (z. B. `https://rhz.internet-box.ch:8449/recipes/...`), eine UUID, oder wählen Sie einfach ein Rezept aus dem **Dropdown-Menü Ihrer Rezeptsammlung** aus.
3. Auch **externe Rezept-Links** (z. B. von Chefkoch oder Leckerschmecker) können direkt eingegeben werden.

### Automatische KI-Berechnung & Zutaten-Kategorien
1. Klicken Sie auf **„Nährwerte berechnen“**.
2. **Was im Hintergrund geschieht:**
   - **Google Gemini** analysiert alle Zutaten und Mengenangaben des Rezepts.
   - Präzise Nährwerte pro Portion werden berechnet:
     - **Kalorien (Energie in kcal)**
     - **Kohlenhydrate (in g)**
     - **Fett (in g)**
     - **Eiweiss / Protein (in g)**
   - **Automatische Kategoriezuordnung:** Sofern die Checkbox aktiviert ist, weist das System dem Rezept im Hintergrund automatisch passende Hauptkategorien zu (z. B. *Fleisch, Geflügel, Fisch, Teigwaren, Reis, Gemüse, Käse* etc.).

### Nährwerte überprüfen, anpassen & Commit
- Das Ergebnis wird in übersichtlichen Eingabefeldern und einer Live-Gesamttabelle (pro Portion & Gesamtrezept) dargestellt.
- **Freie Anpassung:** Sie können jeden Nährwertwert im Formular manuell überschreiben; die Gesamttabelle rechnet sich sofort in Echtzeit mit.
- **Commit:** Klicken Sie auf **„In Datenbank speichern (Commit)“**:
  - Die Nährwerte werden dauerhaft in der Datenbank beim Rezept gespeichert.
  - Die Synchronisations-Version wird erhöht (sodass auch die Flutter-App die neuen Werte erhält).
  - Sie werden sofort mit grünem Erfolgsbanner auf Ihre **Rezept-Hauptseite** weitergeleitet.
- **Abbrechen:** Ein Klick auf **„Abbrechen“** verwirft die Berechnung und führt Sie direkt zurück zur Hauptseite.

---

## 7. Einkaufsliste

Die Einkaufsliste (`/shopping-list`) hilft beim organisierten Einkauf:

- **Zutaten übernehmen:** Aus jeder Rezeptansicht mit einem Klick auf *„Zur Einkaufsliste hinzufügen“* (in der gewünschten Portionsmenge!).
- **Eigene Artikel hinzufügen:** Schnelleingabe für spontane Einkäufe (z. B. *„Milch“*, *„Mineralwasser“*).
- **Abhaken im Laden:** Tippen Sie auf die Checkbox eines Artikels. Der Eintrag wird durchgestrichen und rückt nach unten.
- **Bereinigen:** *„Erledigte entfernen“* löscht alle abgehakten Artikel mit einem Klick.
- **Liste leeren:** Setzt die gesamte Liste nach dem Grosseinkauf zurück.

---

## 8. Menüplaner

Der Menüplaner (`/meal-planner`) strukturiert Ihre Kochwoche von Montag bis Sonntag:

- **Wochennavigation:** Bequem zwischen der aktuellen, nächsten oder vergangenen Kalenderwoche blättern.
- **Gericht zuweisen:** Wählen Sie bei einem Wochentag einfach das gewünschte Rezept aus Ihrer Sammlung aus.
- **Gesamteinkauf planen:** Über *„Zutaten der Woche auf Einkaufsliste“* landen alle benötigten Lebensmittel der gesamten Woche mit einem Klick auf Ihrer Einkaufsliste.

---

## 9. Kategorien & Schlagwörter verwalten

### Kategorien (`/categories`)
Bringen eine klare Grundstruktur nach Hauptzutaten und Gängen:
- **Hauptzutaten:** Fleisch, Geflügel, Fisch, Teigwaren, Reis, Kartoffel, Gemüse, Pilz, Käse.
- **Mahlzeitenarten:** Suppe, Salat, Dessert, Backen.
- **Individuelle Farben:** Jede Kategorie kann eine eigene Erkennungsfarbe tragen.
- **Automatische Erkennung:** Anhand der Zutaten werden Rezepte automatisch den passenden Kategorien zugewiesen.

### Schlagwörter / Tags (`/tags`)
Ergänzen die Kategorien um Eigenschaften wie Anlässe, Zubereitungsarten oder Saisons:
- **Beispiele:** `#Schnell (< 30 Min.)`, `#Gäste & Fest`, `#Einfach & Alltag`, `#Klassiker`, `#Sommer`, `#Winter & Deftig`, `#Low-Carb`, `#Vegetarisch`, `#Resteverwertung`, `#Apéro`, `#Backofen`, `#Schweizer Küche`.
- **Eigene Schlagwörter anlegen:** In der Tag-Verwaltung (`Rezepte > ➔ Schlagwörter`) oder direkt im Rezept-Editor.
- **Filter-Chips:** In der Rezeptübersicht genügt ein Tipp auf einen Tag-Chip, um z. B. alle `#Schnell`-Gerichte zu filtern.

---

## 10. 📱 Mobile Flutter-App: Synchronisation & Offline-Nutzung

Neben der Web-Applikation steht die native mobile **Rezeptschatz Flutter-App** für Android und iOS bereit.

### Neues hellblaues App-Icon
- Die App präsentiert sich mit einem modernen, randlosen App-Icon (goldenes Rezeptbuch in einer Holz-Schatztruhe auf harmonischem hellblauem Hintergrund).
- Unterstützt moderne **Android Adaptive Icons** (z. B. auf Samsung One UI), sodass das Symbol randlos und ohne störenden weissen Rahmen dargestellt wird.

### Rezepte aus der Datenbank aktualisieren (Sync)
Haben Sie auf dem PC oder Tablet neue Rezepte hinzugefügt oder Nährwerte berechnet?
1. Öffnen Sie die Rezeptschatz-App auf Ihrem Smartphone.
2. Tippen Sie oben in der Navigationsleiste auf das **Cloud-Download- / Sync-Symbol** (🔄).
3. Bestätigen Sie im Dialog **„Rezepte synchronisieren“**.
4. Die App gleicht alle neuen und bearbeiteten Rezepte, Bilder, Nährwerte und Kategorien blitzschnell mit der MariaDB-Datenbank auf Ihrem Raspberry Pi ab.

### Offline-Fähigkeit in der Küche
- Einmal synchronisiert, sind alle Rezepte, Zutaten und Zubereitungsschritte lokal auf dem Smartphone gespeichert.
- Sie können auch im Keller, im Supermarkt ohne Empfang oder in der Ferienwohnung offline auf Ihre gesamte Rezeptsammlung zugreifen.

---

## 11. Administration & Server-Befehle (Raspberry Pi)

Für Wartungsarbeiten auf Ihrem Raspberry Pi (`pironman:/var/www/rezeptschatz`) stehen Ihnen folgende Konsolenbefehle zur Verfügung:

### Software-Aktualisierung (Git Pull)
Um die neuesten Funktionen und Fehlerbehebungen auf den Server zu laden:
```bash
cd /var/www/rezeptschatz
sudo git reset --hard origin/main
sudo git pull
php artisan optimize:clear
sudo systemctl restart php*-fpm
```

> **Tipp bei Git-Fehlermeldungen („dubiose Besitzverhältnisse“ oder Permission denied bei FETCH_HEAD):**
> Sollte Git die Aktualisierung verweigern, liegt das daran, dass der Server-Ordner dem Benutzer `pi` gehört, aber Webserver-Prozesse darauf zugreifen. Einmalig ausführen:
> ```bash
> git config --global --add safe.directory /var/www/rezeptschatz
> sudo chown -R pi:www-data /var/www/rezeptschatz/.git
> ```

### Neuer Nährwert-Konsolenbefehl (`get:nutritional-val`)
Ermittelt, berechnet und speichert Nährwerte für jedes Rezept direkt im Terminal:
```bash
# Nährwerte anhand einer URL oder ID analysieren und als formatierte Tabelle anzeigen:
php artisan get:nutritional-val "https://rhz.internet-box.ch:8449/recipes/b7d9e63b-06dd-4855-a981-b8f150220482"

# Nährwerte berechnen und direkt in der Datenbank beim Rezept speichern:
php artisan get:nutritional-val "https://rhz.internet-box.ch:8449/recipes/b7d9e63b-06dd-4855-a981-b8f150220482" --save

# Neuberechnung für ein Rezept nach Titel erzwingen:
php artisan recipe:nutritional-val "Tomaten-Clafoutis" --recalculate --save
```

### Automatische Kategorienerkennung über alle Rezepte laufen lassen
Analysiert alle Zutaten in der Datenbank und ordnet Fleisch, Geflügel, Gemüse, Teigwaren etc. zu:
```bash
php artisan recipes:assign-ingredient-categories
```

### Dateirechte & Berechtigungen (`www-data`)
Sollte das Speichern im Webbrowser hängen bleiben, fehlen dem Webserver oft Schreibrechte auf die Cache- und Logverzeichnisse. Führen Sie in diesem Fall einmal Folgendes aus:
```bash
sudo chown -R www-data:www-data /var/www/rezeptschatz/storage /var/www/rezeptschatz/bootstrap/cache
sudo chmod -R 775 /var/www/rezeptschatz/storage /var/www/rezeptschatz/bootstrap/cache
```

### KI-Verbindung und Modelle testen
Prüft die Erreichbarkeit von Google Gemini und zeigt alle freigeschalteten Modelle:
```bash
php artisan ai:test
```

### Caches leeren & Wartung
```bash
php artisan optimize:clear
```

---

## Zusammenfassung & Tipps für die Küche

1. **Tablet-Halterung:** Stellen Sie Ihr Tablet mit einer abwischbaren Hülle in die Küche. Im Vollbildmodus bleibt der Bildschirm an und Sie können Zutaten mit nassen Fingern mühelos abhaken.
2. **Nährwert-Ampel im Blick:** Berechnen Sie bei neuen Rezepten kurz die Nährwerte über den neuen Nährwert-Rechner – so sehen Sie auf einen Blick, wie proteinreich oder kalorienarm das Gericht ist.
3. **Kühlschrank-Scan:** Achten Sie beim Fotografieren des Kühlschranks darauf, dass das Innenlicht gut leuchtet – so erkennt Google Gemini noch mehr passende Zutaten für spontane Menüs.
4. **Portionen umrechnen:** Vor dem Kochen kurz auf `+` oder `-` tippen, um Fleisch- und Flüssigkeitsmengen exakt an die Gästezahl anzupassen.
5. **Mobile Synchronisation:** Vor dem Einkaufen kurz in der Smartphone-App auf den Sync-Button tippen, damit alle neuen Rezepte und Zutaten für den Einkauf offline parat sind.

*Viel Freude beim Kochen und Geniessen mit Ihrem persönlichen Rezeptschatz!* 🍳🥗🥖
