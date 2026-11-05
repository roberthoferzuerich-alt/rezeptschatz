<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use DateInterval;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WebRecipeImportService
{
    public function __construct(
        protected RecipeService $recipeService,
        protected CookmateImportService $cookmateImportService
    ) {}

    /**
     * Ruft eine URL ab und extrahiert standardisierte Rezeptdaten (JSON-LD / Schema.org).
     */
    public function fetchAndParse(string $url): array
    {
        $url = trim($url);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return [
                'success' => false,
                'error' => 'Bitte geben Sie eine gültige Internet-Adresse (URL) ein.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'de-CH,de-DE,de;q=0.9,en;q=0.8',
            ])->timeout(15)->get($url);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => "Die Webseite konnte nicht geladen werden (HTTP Status {$response->status()}).",
                ];
            }

            $html = $response->body();
            return $this->parseHtml($html, $url);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Verbindungsfehler beim Abrufen der Webseite: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Analysiert HTML-Inhalte auf Schema.org / JSON-LD Rezeptdaten.
     */
    public function parseHtml(string $html, string $sourceUrl = ''): array
    {
        $domain = $sourceUrl ? parse_url($sourceUrl, PHP_URL_HOST) : '';

        // 1. Suche nach JSON-LD Skripten
        $recipeData = $this->extractJsonLdRecipe($html);

        // 2. Falls kein JSON-LD, Fallback auf OpenGraph / Meta-Tags
        if (!$recipeData) {
            $recipeData = $this->extractOpenGraphRecipe($html);
        }

        if (!$recipeData) {
            return [
                'success' => false,
                'error' => 'Auf dieser Webseite konnten keine strukturierten Rezeptdaten erkannt werden.',
            ];
        }

        // Strukturierte Daten extrahieren
        $title = trim($recipeData['name'] ?? $recipeData['headline'] ?? '');
        $description = trim($recipeData['description'] ?? '');
        $imageUrl = $this->extractImageUrl($recipeData['image'] ?? null);
        $portions = $this->extractPortions($recipeData['recipeYield'] ?? null);
        $prepTime = $this->parseDuration($recipeData['prepTime'] ?? null);
        $cookTime = $this->parseDuration($recipeData['cookTime'] ?? $recipeData['performTime'] ?? null);
        $totalTime = $this->parseDuration($recipeData['totalTime'] ?? null);

        // Wenn Gesamtzeit da ist, aber keine Kochen/Vorbereiten getrennt
        if (!$cookTime && !$prepTime && $totalTime) {
            $cookTime = $totalTime;
        }

        // Zutaten parsen
        $rawIngredients = $recipeData['recipeIngredient'] ?? [];
        if (is_string($rawIngredients)) {
            $rawIngredients = preg_split('/\r\n|\r|\n/', $rawIngredients);
        }
        $ingredients = [];
        $order = 1;
        foreach ((array) $rawIngredients as $rawIng) {
            $line = trim(strip_tags((string) $rawIng));
            if ($line === '') {
                continue;
            }
            $parsed = $this->cookmateImportService->parseIngredientLine($line);
            $ingredients[] = [
                'sort_order' => $order++,
                'name' => $parsed['name'],
                'amount_numeric' => $parsed['amount_numeric'],
                'unit' => $parsed['unit'],
                'amount_text' => $parsed['amount_text'],
                'raw' => $line,
            ];
        }

        // Zubereitungsschritte parsen
        $steps = $this->extractSteps($recipeData['recipeInstructions'] ?? []);

        // Nährwerte parsen
        $nutrition = $this->extractNutrition($recipeData['nutrition'] ?? null);

        // Kategorien
        $categories = $this->extractCategories($recipeData['recipeCategory'] ?? $recipeData['keywords'] ?? null);

        // Vollständigkeitsprüfung
        $missingFields = [];
        if (empty($title)) {
            $missingFields[] = 'Kein Rezepttitel gefunden';
        }
        if (empty($ingredients)) {
            $missingFields[] = 'Keine Zutatenliste erkannt';
        }
        if (empty($steps)) {
            $missingFields[] = 'Keine Zubereitungsschritte erkannt';
        }
        if (empty($imageUrl)) {
            $missingFields[] = 'Kein Rezeptbild gefunden';
        }

        $isComplete = count($missingFields) === 0;

        return [
            'success' => true,
            'url' => $sourceUrl,
            'domain' => $domain,
            'title' => $title,
            'description' => $description,
            'image_url' => $imageUrl,
            'portions' => $portions ?: 4,
            'prep_time_minutes' => $prepTime,
            'cook_time_minutes' => $cookTime,
            'ingredients' => $ingredients,
            'steps' => $steps,
            'nutrition' => $nutrition,
            'categories' => $categories,
            'is_complete' => $isComplete,
            'missing_fields' => $missingFields,
            'warning' => !$isComplete ? 'Achtung: Einige Rezeptbestandteile konnten nicht automatisch ausgelesen werden.' : null,
        ];
    }

    /**
     * Speichert das analysierte Rezept für einen Benutzer in die Datenbank.
     */
    public function saveToDatabase(User $user, array $parsedData): Recipe
    {
        // Kategorien anlegen oder verknüpfen
        $categoryIds = [];
        if (!empty($parsedData['categories'])) {
            foreach ($parsedData['categories'] as $catName) {
                $catName = trim($catName);
                if (mb_strlen($catName) > 1 && mb_strlen($catName) <= 50) {
                    $cat = Category::firstOrCreate(
                        ['user_id' => $user->id, 'name' => $catName],
                        ['color' => '#8ea92c']
                    );
                    $categoryIds[] = $cat->id;
                }
            }
        }

        $data = [
            'title' => $parsedData['title'] ?: 'Importiertes Web-Rezept',
            'description' => $parsedData['description'] ?? null,
            'portions' => (int) ($parsedData['portions'] ?? 4),
            'prep_time_minutes' => $parsedData['prep_time_minutes'] ?? null,
            'cook_time_minutes' => $parsedData['cook_time_minutes'] ?? null,
            'source_url' => $parsedData['url'] ?? null,
            'calories' => $parsedData['nutrition']['calories'] ?? null,
            'carbohydrates' => $parsedData['nutrition']['carbohydrates'] ?? null,
            'fat' => $parsedData['nutrition']['fat'] ?? null,
            'protein' => $parsedData['nutrition']['protein'] ?? null,
            'nutrition_raw' => $parsedData['nutrition']['raw'] ?? null,
            'category_ids' => $categoryIds,
            'ingredients' => $parsedData['ingredients'] ?? [],
            'steps' => $parsedData['steps'] ?? [],
            'is_shared' => true,
        ];

        $recipe = $this->recipeService->createRecipe($user, $data);

        // Bild herunterladen, falls vorhanden
        if (!empty($parsedData['image_url'])) {
            $this->recipeService->saveRecipeImageFromUrl($recipe, $parsedData['image_url']);
        }

        return $recipe;
    }

    /**
     * Durchsucht HTML nach <script type="application/ld+json"> und findet Recipe-Objekte.
     */
    protected function extractJsonLdRecipe(string $html): ?array
    {
        preg_match_all('/<script\b[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches);

        if (empty($matches[1])) {
            return null;
        }

        foreach ($matches[1] as $jsonString) {
            $jsonString = trim($jsonString);
            $data = json_decode($jsonString, true);

            if (!$data) {
                continue;
            }

            $recipe = $this->findRecipeInJsonLdNode($data);
            if ($recipe) {
                return $recipe;
            }
        }

        return null;
    }

    /**
     * Rekursiver Finder für Recipe-Objekte in JSON-LD Bäumen (auch @graph).
     */
    protected function findRecipeInJsonLdNode(mixed $node): ?array
    {
        if (!is_array($node)) {
            return null;
        }

        // Prüfen, ob der Knoten selbst ein Recipe ist
        if (isset($node['@type'])) {
            $type = $node['@type'];
            if ($type === 'Recipe' || (is_array($type) && in_array('Recipe', $type, true)) || str_ends_with((string)$type, '/Recipe')) {
                return $node;
            }
        }

        // Falls @graph vorhanden ist
        if (isset($node['@graph']) && is_array($node['@graph'])) {
            foreach ($node['@graph'] as $child) {
                $found = $this->findRecipeInJsonLdNode($child);
                if ($found) {
                    return $found;
                }
            }
        }

        // Falls Liste von Objekten
        foreach ($node as $key => $child) {
            if (is_array($child)) {
                $found = $this->findRecipeInJsonLdNode($child);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Fallback: Extrahiert OpenGraph Metadaten.
     */
    protected function extractOpenGraphRecipe(string $html): ?array
    {
        preg_match('/<meta\b[^>]*property=["\']og:title["\'][^>]*content=["\']([^"\']*)["\']/i', $html, $titleMatch);
        if (empty($titleMatch[1])) {
            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
        }

        if (empty($titleMatch[1])) {
            return null;
        }

        $title = trim(html_entity_decode(strip_tags($titleMatch[1])));
        // Webseitennamen wie " | Betty Bossi" oder " - Chefkoch.de" entfernen
        $title = preg_replace('/\s*[-|–]\s*(Betty Bossi|Chefkoch|Swissmilk|GuteKueche|Lecker).*$/i', '', $title);

        preg_match('/<meta\b[^>]*property=["\']og:description["\'][^>]*content=["\']([^"\']*)["\']/i', $html, $descMatch);
        $description = !empty($descMatch[1]) ? trim(html_entity_decode($descMatch[1])) : '';

        preg_match('/<meta\b[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']*)["\']/i', $html, $imgMatch);
        $imageUrl = !empty($imgMatch[1]) ? trim($imgMatch[1]) : '';

        return [
            'name' => $title,
            'description' => $description,
            'image' => $imageUrl,
            'recipeIngredient' => [],
            'recipeInstructions' => [],
        ];
    }

    /**
     * Extrahiert die Bild-URL aus verschiedenen Formaten.
     */
    protected function extractImageUrl(mixed $image): ?string
    {
        if (empty($image)) {
            return null;
        }

        if (is_string($image)) {
            return $image;
        }

        if (is_array($image)) {
            if (isset($image['url']) && is_string($image['url'])) {
                return $image['url'];
            }
            // Erstes Element
            $first = reset($image);
            if (is_string($first)) {
                return $first;
            }
            if (is_array($first) && isset($first['url'])) {
                return $first['url'];
            }
        }

        return null;
    }

    /**
     * Extrahiert die Anzahl der Portionen.
     */
    protected function extractPortions(mixed $yield): ?int
    {
        if (empty($yield)) {
            return null;
        }

        if (is_array($yield)) {
            $yield = reset($yield);
        }

        if (preg_match('/(\d+)/', (string) $yield, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Konvertiert ISO 8601 Durations (z. B. PT1H30M, PT45M) in Minuten.
     */
    public function parseDuration(mixed $duration): ?int
    {
        if (empty($duration)) {
            return null;
        }

        $duration = trim((string) $duration);

        // Fall 1: Bereits eine Zahl in Minuten
        if (is_numeric($duration)) {
            return (int) $duration;
        }

        // Fall 2: ISO 8601 (z. B. PT20M, PT1H15M, P0DT0H45M)
        if (str_starts_with($duration, 'P')) {
            try {
                $interval = new DateInterval($duration);
                return ($interval->d * 1440) + ($interval->h * 60) + $interval->i;
            } catch (Exception) {
                // Manuelles Regex-Parsing als Fallback
                $hours = 0;
                $mins = 0;
                if (preg_match('/(\d+)\s*H/i', $duration, $hMatch)) {
                    $hours = (int) $hMatch[1];
                }
                if (preg_match('/(\d+)\s*M/i', $duration, $mMatch)) {
                    $mins = (int) $mMatch[1];
                }
                $total = ($hours * 60) + $mins;
                return $total > 0 ? $total : null;
            }
        }

        // Fall 3: Text wie "45 Minuten", "1 Stunde 15 Min"
        $hours = 0;
        $mins = 0;
        if (preg_match('/(\d+)\s*(?:std|stunde|stunden|h)/i', $duration, $hMatch)) {
            $hours = (int) $hMatch[1];
        }
        if (preg_match('/(\d+)\s*(?:min|minute|minuten|m)/i', $duration, $mMatch)) {
            $mins = (int) $mMatch[1];
        }
        $total = ($hours * 60) + $mins;

        return $total > 0 ? $total : null;
    }

    /**
     * Extrahiert Zubereitungsschritte aus verschiedenen Schema.org Strukturen.
     */
    protected function extractSteps(mixed $instructions): array
    {
        if (empty($instructions)) {
            return [];
        }

        $rawSteps = [];

        if (is_string($instructions)) {
            $lines = preg_split('/\r\n|\r|\n/', $instructions);
            foreach ($lines as $line) {
                $t = trim(strip_tags($line));
                if ($t !== '') {
                    $rawSteps[] = $t;
                }
            }
        } elseif (is_array($instructions)) {
            foreach ($instructions as $item) {
                if (is_string($item)) {
                    $t = trim(strip_tags($item));
                    if ($t !== '') {
                        $rawSteps[] = $t;
                    }
                } elseif (is_array($item)) {
                    // HowToStep
                    if (isset($item['text'])) {
                        $t = trim(strip_tags((string) $item['text']));
                        if ($t !== '') {
                            $rawSteps[] = $t;
                        }
                    } elseif (isset($item['name']) && !isset($item['itemListElement'])) {
                        $t = trim(strip_tags((string) $item['name']));
                        if ($t !== '') {
                            $rawSteps[] = $t;
                        }
                    }

                    // HowToSection mit Unterpunkten
                    if (isset($item['itemListElement']) && is_array($item['itemListElement'])) {
                        $sectionName = trim((string) ($item['name'] ?? ''));
                        foreach ($item['itemListElement'] as $subItem) {
                            $subText = is_array($subItem) ? ($subItem['text'] ?? $subItem['name'] ?? '') : (string) $subItem;
                            $t = trim(strip_tags((string) $subText));
                            if ($t !== '') {
                                $rawSteps[] = $sectionName ? "[$sectionName] $t" : $t;
                            }
                        }
                    }
                }
            }
        }

        $steps = [];
        $order = 1;
        foreach ($rawSteps as $raw) {
            // Führende Nummerierung "1. ", "Schritt 1: " entfernen
            $cleaned = preg_replace('/^(?:schritt\s*\d+[\s\:\.\-]+|\d+[\.\)]\s*)/iu', '', $raw);
            $cleaned = trim($cleaned);
            if ($cleaned !== '') {
                $steps[] = [
                    'step_number' => $order++,
                    'instruction' => $cleaned,
                ];
            }
        }

        return $steps;
    }

    /**
     * Extrahiert Nährwerte aus dem Schema.org nutrition Knoten.
     */
    protected function extractNutrition(mixed $nutrition): array
    {
        if (empty($nutrition) || !is_array($nutrition)) {
            return [
                'calories' => null,
                'carbohydrates' => null,
                'fat' => null,
                'protein' => null,
                'raw' => null,
            ];
        }

        $calories = null;
        if (isset($nutrition['calories'])) {
            if (preg_match('/(\d+)/', (string) $nutrition['calories'], $m)) {
                $calories = (int) $m[1];
            }
        }

        $carbohydrates = null;
        if (isset($nutrition['carbohydrateContent'])) {
            if (preg_match('/([\d\.,]+)/', (string) $nutrition['carbohydrateContent'], $m)) {
                $carbohydrates = (float) str_replace(',', '.', $m[1]);
            }
        }

        $fat = null;
        if (isset($nutrition['fatContent'])) {
            if (preg_match('/([\d\.,]+)/', (string) $nutrition['fatContent'], $m)) {
                $fat = (float) str_replace(',', '.', $m[1]);
            }
        }

        $protein = null;
        if (isset($nutrition['proteinContent'])) {
            if (preg_match('/([\d\.,]+)/', (string) $nutrition['proteinContent'], $m)) {
                $protein = (float) str_replace(',', '.', $m[1]);
            }
        }

        // Formatierter String für Rohanzeige
        $parts = [];
        if ($calories) $parts[] = "{$calories} kcal";
        if ($carbohydrates) $parts[] = "KH: {$carbohydrates}g";
        if ($fat) $parts[] = "Fett: {$fat}g";
        if ($protein) $parts[] = "Eiweiss: {$protein}g";
        $raw = !empty($parts) ? implode(' | ', $parts) : null;

        return [
            'calories' => $calories,
            'carbohydrates' => $carbohydrates,
            'fat' => $fat,
            'protein' => $protein,
            'raw' => $raw,
        ];
    }

    /**
     * Extrahiert Kategorien oder Tags.
     */
    protected function extractCategories(mixed $cats): array
    {
        if (empty($cats)) {
            return [];
        }

        $list = [];
        if (is_string($cats)) {
            $parts = preg_split('/[,;]+/', $cats);
            foreach ($parts as $p) {
                $t = trim($p);
                if ($t !== '' && mb_strlen($t) <= 50) {
                    $list[] = $t;
                }
            }
        } elseif (is_array($cats)) {
            foreach ($cats as $c) {
                if (is_string($c)) {
                    $t = trim($c);
                    if ($t !== '' && mb_strlen($t) <= 50) {
                        $list[] = $t;
                    }
                }
            }
        }

        return array_values(array_unique($list));
    }
}
