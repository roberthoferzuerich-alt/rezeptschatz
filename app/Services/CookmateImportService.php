<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class CookmateImportService
{
    public function import(string $filePath, User $user): array
    {
        $stats = [
            'recipes_count' => 0,
            'images_count' => 0,
            'categories_count' => 0,
            'skipped_count' => 0,
            'errors' => [],
        ];

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'xml') {
            $xmlContent = file_get_contents($filePath);
            return $this->parseXml($xmlContent, $user, null, $stats);
        }

        // Bei .mcb oder .zip: Archiv öffnen
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Das Archiv (.mcb oder .zip) konnte nicht geöffnet werden.');
        }

        try {
            // Finde die XML-Datei im Archiv
            $xmlIndex = -1;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (str_ends_with(strtolower($filename), '.xml')) {
                    $xmlIndex = $i;
                    break;
                }
            }

            if ($xmlIndex === -1) {
                throw new \RuntimeException('Im Archiv wurde keine Rezept-XML-Datei gefunden.');
            }

            $xmlContent = $zip->getFromIndex($xmlIndex);
            return $this->parseXml($xmlContent, $user, $zip, $stats);
        } finally {
            $zip->close();
        }
    }

    protected function parseXml(string $xmlContent, User $user, ?ZipArchive $zip, array &$stats): array
    {
        // SimpleXML lädt die Datei
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent);

        if (!$xml) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            $msg = !empty($errors) ? $errors[0]->message : 'Ungültiges XML-Format';
            throw new \RuntimeException('XML-Parsing fehlgeschlagen: ' . trim($msg));
        }

        // Rezepte ermitteln (können direkt unter <cookbook> oder <recipes> liegen)
        $recipeNodes = $xml->xpath('//recipe');
        if (empty($recipeNodes)) {
            return $stats;
        }

        DB::beginTransaction();
        try {
            foreach ($recipeNodes as $node) {
                $title = trim((string)$node->title);
                if (empty($title)) {
                    $stats['skipped_count']++;
                    continue;
                }

                // Zeiten parsen (z. B. "15 min" oder "1 h 20 min" oder "45")
                $prepTime = $this->parseDurationMinutes((string)$node->preptime);
                $cookTime = $this->parseDurationMinutes((string)$node->cooktime);
                $restTime = $this->parseDurationMinutes((string)$node->resttime);

                // Portionen parsen (z. B. "4" oder "4 Portionen" oder "4 servings")
                $portions = $this->parsePortions((string)$node->quantity);

                // Beschreibung zusammenfügen
                $description = $this->extractListText($node->description);

                // Notizen / Kommentare
                $notes = trim((string)$node->comments);

                // Quelle
                $sourceUrl = trim((string)$node->url);

                $recipeId = (string)Str::uuid();
                $imagePath = null;
                $imageHash = null;

                // Bild importieren, falls Zip vorhanden
                if ($zip) {
                    $imagePathNode = trim((string)$node->imagepath);
                    $imageUrlNode = trim((string)$node->imageurl);
                    
                    $imageFilename = basename($imagePathNode ?: $imageUrlNode);
                    if ($imageFilename) {
                        $imageBytes = $this->findImageInZip($zip, $imageFilename);
                        if ($imageBytes) {
                            $targetFilename = "recipes/{$recipeId}.jpg";
                            Storage::disk('local')->put($targetFilename, $imageBytes);
                            $imagePath = $targetFilename;
                            $imageHash = hash('sha256', $imageBytes);
                            $stats['images_count']++;
                        }
                    }
                }

                // Rezept anlegen
                $recipe = Recipe::create([
                    'id' => $recipeId,
                    'user_id' => $user->id,
                    'title' => mb_substr($title, 0, 255),
                    'description' => $description ?: null,
                    'portions' => $portions ?: 4,
                    'prep_time_minutes' => $prepTime,
                    'cook_time_minutes' => $cookTime,
                    'rest_time_minutes' => $restTime,
                    'notes' => $notes ?: null,
                    'source_url' => $sourceUrl ? mb_substr($sourceUrl, 0, 1000) : null,
                    'is_favorite' => ((int)$node->rating >= 4),
                    'is_shared' => true, // Standardmäßig für die Familie geteilt
                    'image_path' => $imagePath,
                    'image_hash' => $imageHash,
                    'server_version' => 1,
                ]);

                // Kategorien zuweisen
                if (isset($node->category)) {
                    foreach ($node->category as $catNode) {
                        $catName = trim((string)$catNode);
                        if (!empty($catName)) {
                            $catName = mb_substr($catName, 0, 100);
                            $category = Category::firstOrCreate(
                                ['user_id' => $user->id, 'name' => $catName],
                                ['color' => '#8ea92c']
                            );
                            if ($category->wasRecentlyCreated) {
                                $stats['categories_count']++;
                            }
                            $recipe->categories()->syncWithoutDetaching([$category->id]);
                        }
                    }
                }

                // Zutaten anlegen
                if (isset($node->ingredient)) {
                    $ingOrder = 1;
                    $ingredientItems = $this->extractListItems($node->ingredient);
                    foreach ($ingredientItems as $rawIngredient) {
                        $parsed = $this->parseIngredientLine($rawIngredient);
                        RecipeIngredient::create([
                            'id' => (string)Str::uuid(),
                            'recipe_id' => $recipe->id,
                            'name' => $parsed['name'],
                            'amount_numeric' => $parsed['amount_numeric'],
                            'unit' => $parsed['unit'] ? mb_substr($parsed['unit'], 0, 50) : null,
                            'amount_text' => $parsed['amount_text'] ? mb_substr($parsed['amount_text'], 0, 100) : null,
                            'sort_order' => $ingOrder++,
                        ]);
                    }
                }

                // Zubereitungsschritte anlegen
                if (isset($node->recipetext)) {
                    $stepOrder = 1;
                    $stepItems = $this->extractListItems($node->recipetext);
                    foreach ($stepItems as $rawStep) {
                        $instruction = trim($rawStep);
                        // Führende Nummerierung wie "1. ", "1) " entfernen falls vorhanden
                        $instruction = preg_replace('/^\d+[\.\)]\s*/', '', $instruction);
                        if (!empty($instruction)) {
                            RecipeStep::create([
                                'id' => (string)Str::uuid(),
                                'recipe_id' => $recipe->id,
                                'step_number' => $stepOrder++,
                                'instruction' => $instruction,
                            ]);
                        }
                    }
                }

                $stats['recipes_count']++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return $stats;
    }

    protected function findImageInZip(ZipArchive $zip, string $filename): ?string
    {
        // 1. Direkter Treffer
        $bytes = $zip->getFromName($filename);
        if ($bytes !== false) {
            return $bytes;
        }

        // 2. Im Unterordner images/
        $bytes = $zip->getFromName('images/' . $filename);
        if ($bytes !== false) {
            return $bytes;
        }

        // 3. Fallback: Suche über Dateinamen
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === $filename) {
                return $zip->getFromIndex($i);
            }
        }

        return null;
    }

    protected function extractListText(\SimpleXMLElement $node): string
    {
        $items = $this->extractListItems($node);
        return implode("\n\n", $items);
    }

    protected function extractListItems(\SimpleXMLElement $node): array
    {
        $items = [];
        if (isset($node->li)) {
            foreach ($node->li as $li) {
                $t = trim((string)$li);
                if (!empty($t)) {
                    $items[] = $t;
                }
            }
        } else {
            $raw = trim((string)$node);
            if (!empty($raw)) {
                $lines = preg_split('/\r\n|\r|\n/', $raw);
                foreach ($lines as $line) {
                    $l = trim($line);
                    if (!empty($l)) {
                        $items[] = $l;
                    }
                }
            }
        }
        return $items;
    }

    protected function parseDurationMinutes(string $raw): ?int
    {
        $raw = strtolower(trim($raw));
        if (empty($raw)) {
            return null;
        }

        $minutes = 0;

        // Stunden (z. B. "1 h", "1 Std", "1 Stunde")
        if (preg_match('/(\d+)\s*(?:h|std|stunde|stunden)/i', $raw, $m)) {
            $minutes += (int)$m[1] * 60;
        }

        // Minuten (z. B. "20 min", "20 m", "20")
        if (preg_match('/(\d+)\s*(?:min|m|minute|minuten)?(?!\s*(?:h|std))/i', $raw, $m)) {
            $val = (int)$m[1];
            // Falls Stunden schon gematcht wurden, nur Minuten addieren
            if ($minutes > 0 && !str_contains($raw, (string)$val . ' h')) {
                $minutes += $val;
            } elseif ($minutes === 0) {
                $minutes = $val;
            }
        }

        return $minutes > 0 ? $minutes : null;
    }

    protected function parsePortions(string $raw): int
    {
        if (preg_match('/(\d+)/', $raw, $m)) {
            $val = (int)$m[1];
            return ($val > 0 && $val < 100) ? $val : 4;
        }
        return 4;
    }

    public function parseIngredientLine(string $line): array
    {
        $line = trim($line);

        // Bekannte deutsche & internationale Einheiten
        $units = [
            'g', 'kg', 'ml', 'cl', 'dl', 'l', 'liter',
            'tl', 'el', 'msp', 'prise', 'prisen', 'zehe', 'zehen',
            'bund', 'dose', 'dosen', 'packung', 'packungen', 'pck',
            'becher', 'tasse', 'tassen', 'glas', 'gläser', 'stk', 'stück',
            'scheibe', 'scheiben', 'spritzer', 'tropfen', 'bl', 'blatt'
        ];

        // Regex: Versuche Zahl am Anfang zu erkennen (auch Brüche wie 1/2 oder Komma wie 1,5)
        $pattern = '/^([\d\/\,\.\s\-½¼¾]+)?\s*([a-zA-ZäöüÄÖÜß]+)?\s+(.*)$/u';

        if (preg_match($pattern, $line, $matches)) {
            $rawAmount = trim($matches[1] ?? '');
            $rawUnit = strtolower(trim($matches[2] ?? ''));
            $restName = trim($matches[3] ?? '');

            // Prüfen ob $rawUnit eine echte Einheit ist
            if (in_array($rawUnit, $units, true)) {
                $numeric = $this->parseFraction($rawAmount);
                return [
                    'amount_numeric' => $numeric,
                    'unit' => $rawUnit,
                    'name' => $restName ?: $line,
                    'amount_text' => $rawAmount ?: null,
                ];
            }
        }

        // Wenn keine Einheit erkannt wurde, aber Zahl am Anfang steht (z. B. "2 Eier")
        if (preg_match('/^([\d\/\,\.\s\-½¼¾]+)\s+(.*)$/u', $line, $matches)) {
            $rawAmount = trim($matches[1]);
            $restName = trim($matches[2]);
            $numeric = $this->parseFraction($rawAmount);

            return [
                'amount_numeric' => $numeric,
                'unit' => null,
                'name' => $restName,
                'amount_text' => $rawAmount,
            ];
        }

        // Reiner Name (z. B. "Salz und Pfeffer nach Geschmack")
        return [
            'amount_numeric' => null,
            'unit' => null,
            'name' => $line,
            'amount_text' => null,
        ];
    }

    protected function parseFraction(string $str): ?float
    {
        $str = str_replace(['½', '¼', '¾', ','], ['0.5', '0.25', '0.75', '.'], $str);
        $str = trim($str);

        if (empty($str)) {
            return null;
        }

        if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $str, $m)) {
            return ($m[2] > 0) ? round((float)$m[1] / (float)$m[2], 2) : null;
        }

        if (preg_match('/^(\d+)\s+(\d+)\/(\d+)$/', $str, $m)) {
            return ($m[3] > 0) ? round((float)$m[1] + ((float)$m[2] / (float)$m[3]), 2) : null;
        }

        if (is_numeric($str)) {
            return (float)$str;
        }

        return null;
    }
}

