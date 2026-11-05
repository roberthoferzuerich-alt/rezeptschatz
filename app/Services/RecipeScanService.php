<?php

namespace App\Services;

class RecipeScanService
{
    public function __construct(
        protected CookmateImportService $cookmateService
    ) {}

    /**
     * Parst unstrukturierten Text (z.B. aus Foto-Scan / OCR) in strukturierte Rezeptdaten.
     *
     * @param string $rawText
     * @return array{
     *     title: string,
     *     description: ?string,
     *     portions: int,
     *     prep_time_minutes: ?int,
     *     cook_time_minutes: ?int,
     *     ingredients: array<array{name: string, amount_numeric: ?float, unit: ?string, amount_text: ?string, sort_order: int}>,
     *     steps: array<array{instruction: string, step_number: int}>
     * }
     */
    public function parseScannedText(string $rawText): array
    {
        $rawText = trim($rawText);
        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        
        $cleanedLines = [];
        foreach ($lines as $line) {
            $l = trim($line);
            // OCR-Rauschen wie vereinzelte Sonderzeichen ignorieren
            if ($l !== '' && !preg_match('/^[_\-=\*\~]{3,}$/', $l)) {
                $cleanedLines[] = $l;
            }
        }

        $title = '';
        $description = '';
        $portions = 4;
        $prepTime = null;
        $cookTime = null;

        $ingredientsLines = [];
        $stepsLines = [];

        $currentSection = 'header'; // 'header', 'ingredients', 'steps'

        foreach ($cleanedLines as $line) {
            // Sektionswechsel erkennen
            if (preg_match('/^(?:zutaten|zutatenliste|für\s+die\s+zutaten|was\s+du\s+brauchst|ingredients)\s*:?$/iu', $line)
                || preg_match('/^zutaten\s*\(.*?\)\s*:?$/iu', $line)) {
                $currentSection = 'ingredients';
                continue;
            }

            if (preg_match('/^(?:zubereitung|anleitung|zubereitungsschritte|schritte|so\s+gehts|so\s+wirds\s+gemacht|instructions|directions)\s*:?$/iu', $line)) {
                $currentSection = 'steps';
                continue;
            }

            // Je nach aktueller Sektion zuordnen
            if ($currentSection === 'header') {
                // Prüfen ob Portionen enthalten sind
                if (preg_match('/(?:für|portionen?)\s*:?\s*(\d+)\s*(?:personen|portionen|teller)?/iu', $line, $m)) {
                    $portions = (int) $m[1];
                    continue;
                } elseif (preg_match('/^(\d+)\s*(?:personen|portionen)\b/iu', $line, $m)) {
                    $portions = (int) $m[1];
                    continue;
                }

                // Prüfen auf Vorbereitungszeit / Zubereitungszeit
                if (preg_match('/(?:vorbereitungszeit|vorbereitung|aktiv)\s*:?\s*(\d+)\s*(?:min|minuten|h|std)/iu', $line, $m)) {
                    $prepTime = (int) $m[1];
                    continue;
                }

                // Prüfen auf Kochzeit / Backzeit
                if (preg_match('/(?:kochzeit|backzeit|ruhezeit|gesamtzeit|zeit)\s*:?\s*(\d+)\s*(?:min|minuten|h|std)/iu', $line, $m)) {
                    $cookTime = (int) $m[1];
                    continue;
                }

                // Erste signifikante Zeile ist der Titel
                if (empty($title)) {
                    // Titel bereinigen von "Titel:"
                    $cleanedTitle = preg_replace('/^titel\s*:\s*/i', '', $line);
                    $title = mb_substr(trim($cleanedTitle), 0, 255);
                } else {
                    // Weitere Zeilen im Header als Beschreibung werten
                    if (empty($description)) {
                        $description = $line;
                    } else {
                        $description .= ' ' . $line;
                    }
                }
            } elseif ($currentSection === 'ingredients') {
                $ingredientsLines[] = $line;
            } elseif ($currentSection === 'steps') {
                $stepsLines[] = $line;
            }
        }

        // Fallback: Falls keine Sektionen erkannt wurden (Text ohne explizite Überschriften)
        if (empty($ingredientsLines) && empty($stepsLines) && !empty($cleanedLines)) {
            $parsed = $this->fallbackPartition($cleanedLines);
            $title = $title ?: $parsed['title'];
            $ingredientsLines = $parsed['ingredients'];
            $stepsLines = $parsed['steps'];
        }

        // Titel-Fallback falls immer noch leer
        if (empty($title) && !empty($cleanedLines)) {
            $title = mb_substr($cleanedLines[0], 0, 100);
        }
        if (empty($title)) {
            $title = 'Gescannter Rezeptentwurf vom ' . date('d.m.Y');
        }

        // Zutaten parsen
        $structuredIngredients = [];
        $order = 1;
        foreach ($ingredientsLines as $ingLine) {
            // Führende Aufzählungszeichen entfernen
            $cleaned = preg_replace('/^[•\-\*\+\#]\s*/u', '', $ingLine);
            $cleaned = trim($cleaned);
            if (empty($cleaned)) {
                continue;
            }

            // Zwischenüberschriften erkennen (z.B. "Für die Sauce:")
            if (preg_match('/^für\s+.*:$/i', $cleaned) || str_ends_with($cleaned, ':')) {
                $groupName = rtrim($cleaned, ':');
                continue;
            }

            $parsedIng = $this->cookmateService->parseIngredientLine($cleaned);
            $structuredIngredients[] = [
                'name' => $parsedIng['name'] ?? $cleaned,
                'amount_numeric' => $parsedIng['amount_numeric'] ?? null,
                'unit' => $parsedIng['unit'] ?? null,
                'amount_text' => $parsedIng['amount_text'] ?? null,
                'sort_order' => $order++,
            ];
        }

        // Zubereitungsschritte strukturieren
        $structuredSteps = [];
        $stepNumber = 1;
        $currentStepText = '';

        foreach ($stepsLines as $stepLine) {
            $l = trim($stepLine);
            if (empty($l)) {
                continue;
            }

            // Startet ein neuer nummerierter Schritt (z.B. "1.", "1)", "Schritt 1:")
            if (preg_match('/^(?:schritt\s*)?(\d+)[\.\)\:]\s*(.*)$/iu', $l, $m)) {
                if (!empty($currentStepText)) {
                    $structuredSteps[] = [
                        'instruction' => trim($currentStepText),
                        'step_number' => $stepNumber++,
                    ];
                }
                $currentStepText = $m[2];
            } else {
                if (empty($currentStepText)) {
                    $currentStepText = $l;
                } else {
                    $currentStepText .= ' ' . $l;
                }
            }
        }

        if (!empty($currentStepText)) {
            $structuredSteps[] = [
                'instruction' => trim($currentStepText),
                'step_number' => $stepNumber++,
            ];
        }

        return [
            'title' => $title,
            'description' => $description ?: null,
            'portions' => $portions,
            'prep_time_minutes' => $prepTime,
            'cook_time_minutes' => $cookTime,
            'ingredients' => $structuredIngredients,
            'steps' => $structuredSteps,
        ];
    }

    /**
     * Heuristisches Aufteilen, falls der Text keine expliziten "Zutaten:" oder "Zubereitung:" Marker hat.
     */
    protected function fallbackPartition(array $lines): array
    {
        $title = $lines[0] ?? '';
        $ingredients = [];
        $steps = [];

        // Eine Zeile ist meist eine Zutat, wenn sie mit Zahlen/Einheiten beginnt (z.B. 200g, 2 EL, 1 Prise)
        $unitsPattern = '/^(?:[\d\/\,\.\s½¼¾]+)\s*(?:g|kg|ml|cl|dl|l|tl|el|msp|prise|zehe|bund|dose|packung|stk|stück)\b/iu';

        $inSteps = false;

        for ($i = 1; $i < count($lines); $i++) {
            $line = $lines[$i];

            if ($inSteps) {
                $steps[] = $line;
                continue;
            }

            if (preg_match($unitsPattern, $line) || preg_match('/^[\d½¼¾]+\s+[a-zäöü]/iu', $line)) {
                $ingredients[] = $line;
            } else {
                // Wenn Zeile länger als 50 Zeichen ist oder ein Verb enthält, beginnt wahrscheinlich die Anleitung
                if (mb_strlen($line) > 50 || preg_match('/^(?:Die|Den|Das|In|Alles|Zusammen|Danach|Anschliessend)\b/iu', $line)) {
                    $inSteps = true;
                    $steps[] = $line;
                } else {
                    $ingredients[] = $line;
                }
            }
        }

        return [
            'title' => $title,
            'ingredients' => $ingredients,
            'steps' => $steps,
        ];
    }
}
