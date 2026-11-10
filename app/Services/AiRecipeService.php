<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiRecipeService
{
    /**
     * Prüft, ob mindestens ein KI-API-Schlüssel konfiguriert ist.
     */
    public function isConfigured(): bool
    {
        return !empty($this->getGeminiKey()) || !empty($this->getOpenAiKey());
    }

    /**
     * Gibt den aktiven Anbieter zurück ('gemini', 'openai' oder null).
     */
    public function getActiveProvider(): ?string
    {
        $preferred = cache()->get('ai_provider') 
            ?: config('services.ai.provider', env('AI_PROVIDER', 'gemini'));

        if ($preferred === 'gemini' && !empty($this->getGeminiKey())) {
            return 'gemini';
        }
        if ($preferred === 'openai' && !empty($this->getOpenAiKey())) {
            return 'openai';
        }
        if (!empty($this->getGeminiKey())) {
            return 'gemini';
        }
        if (!empty($this->getOpenAiKey())) {
            return 'openai';
        }
        return null;
    }

    public function getGeminiKey(): ?string
    {
        $key = Cache::get('ai_gemini_api_key') 
            ?: config('services.gemini.api_key') 
            ?: env('GEMINI_API_KEY');

        return !empty($key) ? trim((string)$key) : null;
    }

    public function getOpenAiKey(): ?string
    {
        $key = Cache::get('ai_openai_api_key') 
            ?: config('services.openai.api_key') 
            ?: env('OPENAI_API_KEY');

        return !empty($key) ? trim((string)$key) : null;
    }

    /**
     * Speichert einen API-Schlüssel persistent in Datenbank-Cache und .env.
     */
    public function saveApiKey(string $provider, string $key): void
    {
        $key = trim($key);
        if ($provider === 'openai') {
            Cache::forever('ai_openai_api_key', $key);
            Cache::forever('ai_provider', 'openai');
            config(['services.openai.api_key' => $key, 'services.ai.provider' => 'openai']);
            $this->updateEnvKey('OPENAI_API_KEY', $key);
            $this->updateEnvKey('AI_PROVIDER', 'openai');
        } else {
            Cache::forever('ai_gemini_api_key', $key);
            Cache::forever('ai_provider', 'gemini');
            config(['services.gemini.api_key' => $key, 'services.ai.provider' => 'gemini']);
            $this->updateEnvKey('GEMINI_API_KEY', $key);
            $this->updateEnvKey('AI_PROVIDER', 'gemini');
        }
    }

    /**
     * Entfernt alle gespeicherten KI-API-Schlüssel.
     */
    public function removeApiKey(): void
    {
        Cache::forget('ai_gemini_api_key');
        Cache::forget('ai_openai_api_key');
        Cache::forget('ai_provider');
        config(['services.gemini.api_key' => null, 'services.openai.api_key' => null]);
        $this->updateEnvKey('GEMINI_API_KEY', '');
        $this->updateEnvKey('OPENAI_API_KEY', '');
    }

    /**
     * Analysiert ein Foto des Kühlschranks und generiert ein passendes Rezept.
     *
     * @param string $imageBytes Rohe Bilddaten
     * @param string $mimeType z.B. 'image/jpeg' oder 'image/png'
     * @param array $options ['portions' => 4, 'time_limit' => 30, 'diet' => 'normal', 'extra_wishes' => '']
     * @return array
     */
    public function generateFromFridgeImage(string $imageBytes, string $mimeType = 'image/jpeg', array $options = []): array
    {
        $provider = $this->getActiveProvider();
        if (!$provider) {
            throw new \Exception('Kein KI-API-Schlüssel (Google Gemini oder OpenAI) in der .env-Datei konfiguriert.');
        }

        $prompt = $this->buildPrompt(true, $options);

        if ($provider === 'gemini') {
            return $this->callGeminiVision($prompt, $imageBytes, $mimeType);
        } else {
            return $this->callOpenAiVision($prompt, $imageBytes, $mimeType);
        }
    }

    /**
     * Generiert ein Rezept aus einer Textliste von Zutaten (ohne Bild).
     */
    public function generateFromText(string $ingredientsText, array $options = []): array
    {
        $provider = $this->getActiveProvider();
        if (!$provider) {
            throw new \Exception('Kein KI-API-Schlüssel konfiguriert.');
        }

        $prompt = $this->buildPrompt(false, array_merge($options, ['ingredients_text' => $ingredientsText]));

        if ($provider === 'gemini') {
            return $this->callGeminiText($prompt);
        } else {
            return $this->callOpenAiText($prompt);
        }
    }

    /**
     * Baut die präzise Prompt-Anweisung für die KI zusammen.
     */
    protected function buildPrompt(bool $withImage, array $options): string
    {
        $portions = $options['portions'] ?? 4;
        $timeLimit = $options['time_limit'] ?? 30;
        $diet = $options['diet'] ?? 'normal';
        $extraWishes = $options['extra_wishes'] ?? '';
        $ingredientsText = $options['ingredients_text'] ?? '';

        $dietInstruction = match ($diet) {
            'vegetarian' => 'Das Rezept muss streng vegetarisch sein.',
            'vegan' => 'Das Rezept muss streng vegan sein.',
            'lowcarb' => 'Das Rezept muss kohlenhydratarm (Low-Carb) sein.',
            'quick' => 'Das Rezept muss besonders schnell und einfach sein.',
            default => 'Ausgewogene Alltagsküche.',
        };

        $base = "Du bist ein erfahrener Schweizer Chefkoch und Food-Waste-Experte.\n";
        if ($withImage) {
            $base .= "1. Analysiere das beigefügte Foto des Kühlschranks oder Vorrats sorgfältig.\n";
            $base .= "2. Erkenne alle sichtbaren Lebensmittel und Zutaten und liste sie im Feld 'detected_ingredients' auf.\n";
            $base .= "3. Erstelle aus diesen erkannten Lebensmitteln ein schmackhaftes, alltagstaugliches Rezept auf Deutsch.\n";
        } else {
            $base .= "Der Benutzer hat folgende Zutaten im Kühlschrank:\n{$ingredientsText}\n";
            $base .= "Erstelle daraus ein schmackhaftes, alltagstaugliches Rezept auf Deutsch.\n";
        }

        $base .= "Kriterien:\n";
        $base .= "- Portionen: {$portions}\n";
        $base .= "- Maximaler Zeitaufwand: {$timeLimit} Minuten\n";
        $base .= "- Ernährungsweise: {$dietInstruction}\n";
        if (!empty($extraWishes)) {
            $base .= "- Zusatzwunsch des Benutzers: {$extraWishes}\n";
        }
        $base .= "- Nährwerte: Schätze realistische Nährwerte pro Portion (Kalorien in kcal, Kohlenhydrate in g, Fett in g, Eiweiss in g).\n";

        $base .= "\nAntworte AUSSCHLIESSLICH als valides JSON in exakt diesem Format:\n";
        $base .= <<<'JSON'
{
  "detected_ingredients": ["Zutat 1", "Zutat 2", "Zutat 3"],
  "title": "Kreativer Rezeptname",
  "description": "Kurze, appetitliche Beschreibung (1-2 Sätze)",
  "portions": 4,
  "prep_time_minutes": 15,
  "cook_time_minutes": 20,
  "calories": 480,
  "carbohydrates": 42.0,
  "fat": 16.5,
  "protein": 24.0,
  "ingredients": [
    {
      "name": "Zutatenname",
      "amount_numeric": 200,
      "unit": "g",
      "amount_text": "200 g"
    }
  ],
  "steps": [
    {
      "instruction": "Erster Zubereitungsschritt.",
      "step_number": 1
    }
  ]
}
JSON;

        return $base;
    }

    /**
     * Ermittelt das aktive, unterstützte Gemini-Modell für den API-Schlüssel.
     */
    public function resolveGeminiModel(string $apiKey): string
    {
        // 1. Falls im Cache bereits ein funktionierendes Modell hinterlegt ist (ausser veralteten Modellen)
        if ($cached = Cache::get('gemini_active_model')) {
            if (!str_contains($cached, '1.5') && !str_contains($cached, '2.5')) {
                return str_replace('models/', '', $cached);
            }
            Cache::forget('gemini_active_model');
        }

        // 2. Dynamische Live-Abfrage der tatsächlich für diesen API-Schlüssel freigeschalteten Modelle
        try {
            $resp = Http::withOptions([
                'verify' => false,
                'force_ip_resolve' => 'v4',
            ])
                ->timeout(8)
                ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}");

            if ($resp->successful()) {
                $models = $resp->json('models') ?? [];
                $candidates = [];

                foreach ($models as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods, true)) {
                        $name = str_replace('models/', '', $m['name'] ?? '');
                        if (!empty($name) && !str_contains($name, '1.5') && !str_contains($name, 'embedding')) {
                            $candidates[] = $name;
                        }
                    }
                }

                // Bevorzuge moderne schnelle Flash-Modelle (insbesondere das funktionierende gemini-3-flash-preview)
                $priorities = ['3-flash-preview', '3.8-flash', '3.7-flash', '3.5-flash', 'flash', 'pro'];
                foreach ($priorities as $prio) {
                    foreach ($candidates as $cand) {
                        if (str_contains(strtolower($cand), $prio) && !str_contains($cand, 'tts') && !str_contains($cand, 'image')) {
                            Cache::forever('gemini_active_model', $cand);
                            return $cand;
                        }
                    }
                }

                if (!empty($candidates[0])) {
                    Cache::forever('gemini_active_model', $candidates[0]);
                    return $candidates[0];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gemini ListModels fehlgeschlagen: " . $e->getMessage());
        }

        // 3. Fallback auf konfigurierte oder bewährte Standardmodelle
        $configured = env('GEMINI_MODEL') ?: config('services.gemini.model');
        if (!empty($configured) && !str_contains($configured, '1.5') && !str_contains($configured, '2.5')) {
            return str_replace('models/', '', $configured);
        }

        return 'gemini-3-flash-preview';
    }

    /**
     * Ruft die Google Gemini API mit Bild auf (mit automatischem Modell-Fallback).
     */
    protected function callGeminiVision(string $prompt, string $imageBytes, string $mimeType): array
    {
        $apiKey = $this->getGeminiKey();
        $primaryModel = $this->resolveGeminiModel($apiKey);

        $modelsToTry = array_values(array_unique(array_filter([
            $primaryModel,
            'gemini-3-flash-preview',
            'gemini-3.8-flash',
            'gemini-3.7-flash',
            'gemini-3.5-flash',
        ])));

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => base64_encode($imageBytes),
                            ],
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.4,
            ],
        ];

        $lastError = null;
        foreach ($modelsToTry as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::withOptions([
                'verify' => false,
                'force_ip_resolve' => 'v4',
            ])
                ->timeout(90)
                ->post($url, $payload);

            if ($response->successful()) {
                Cache::forever('gemini_active_model', $model);
                $data = $response->json();
                $rawJson = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                return $this->decodeAndValidateJson($rawJson);
            }

            $errBody = $response->json()['error']['message'] ?? $response->body();
            $lastError = "Google Gemini Fehler ({$model}): {$errBody}";
            Log::warning("Gemini Vision Versuch mit '{$model}' fehlgeschlagen: {$errBody}");

            // Wenn es kein 404/Modell-Fehler ist (z.B. falscher API-Key), sofort abbrechen
            $isModelIssue = $response->status() === 404
                || str_contains($errBody, 'not found')
                || str_contains($errBody, 'not supported');

            if ($isModelIssue && Cache::get('gemini_active_model') === $model) {
                Cache::forget('gemini_active_model');
            }

            if (!$isModelIssue) {
                throw new \Exception("Google Gemini Fehler: {$errBody}");
            }
        }

        throw new \Exception($lastError ?: 'Google Gemini API-Aufruf fehlgeschlagen.');
    }

    /**
     * Ruft die Google Gemini API rein textbasiert auf (mit automatischem Modell-Fallback).
     */
    protected function callGeminiText(string $prompt): array
    {
        $apiKey = $this->getGeminiKey();
        $primaryModel = $this->resolveGeminiModel($apiKey);

        $modelsToTry = array_values(array_unique(array_filter([
            $primaryModel,
            'gemini-3-flash-preview',
            'gemini-3.8-flash',
            'gemini-3.7-flash',
            'gemini-3.5-flash',
        ])));

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json',
                'temperature' => 0.4,
            ],
        ];

        $lastError = null;
        foreach ($modelsToTry as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::withOptions([
                'verify' => false,
                'force_ip_resolve' => 'v4',
            ])
                ->timeout(90)
                ->post($url, $payload);

            if ($response->successful()) {
                Cache::forever('gemini_active_model', $model);
                $data = $response->json();
                $rawJson = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                return $this->decodeAndValidateJson($rawJson);
            }

            $errBody = $response->json()['error']['message'] ?? $response->body();
            $lastError = "Google Gemini Fehler ({$model}): {$errBody}";

            $isModelIssue = $response->status() === 404
                || str_contains($errBody, 'not found')
                || str_contains($errBody, 'not supported');

            if ($isModelIssue && Cache::get('gemini_active_model') === $model) {
                Cache::forget('gemini_active_model');
            }

            if (!$isModelIssue) {
                throw new \Exception("Google Gemini Fehler: {$errBody}");
            }
        }

        throw new \Exception($lastError ?: 'Google Gemini API-Aufruf fehlgeschlagen.');
    }

    /**
     * Ruft die OpenAI API mit Bild auf (GPT-4o / GPT-4o-mini).
     */
    protected function callOpenAiVision(string $prompt, string $imageBytes, string $mimeType): array
    {
        $apiKey = $this->getOpenAiKey();
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $url = 'https://api.openai.com/v1/chat/completions';

        $base64Image = "data:{$mimeType};base64," . base64_encode($imageBytes);

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        [
                            'type' => 'image_url',
                            'image_url' => ['url' => $base64Image],
                        ],
                    ],
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.4,
        ];

        $response = Http::withOptions(['verify' => false])
            ->withToken($apiKey)
            ->timeout(60)
            ->post($url, $payload);

        if (!$response->successful()) {
            $err = $response->json()['error']['message'] ?? $response->body();
            Log::error("OpenAI Vision Fehler: " . $err);
            throw new \Exception("OpenAI Fehler: {$err}");
        }

        $data = $response->json();
        $rawJson = $data['choices'][0]['message']['content'] ?? '{}';
        return $this->decodeAndValidateJson($rawJson);
    }

    /**
     * Ruft die OpenAI API rein textbasiert auf.
     */
    protected function callOpenAiText(string $prompt): array
    {
        $apiKey = $this->getOpenAiKey();
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.4,
        ];

        $response = Http::withOptions(['verify' => false])
            ->withToken($apiKey)
            ->timeout(60)
            ->post($url, $payload);

        if (!$response->successful()) {
            $err = $response->json()['error']['message'] ?? $response->body();
            throw new \Exception("OpenAI Fehler: {$err}");
        }

        $data = $response->json();
        $rawJson = $data['choices'][0]['message']['content'] ?? '{}';
        return $this->decodeAndValidateJson($rawJson);
    }

    /**
     * Dekodiert und validiert die JSON-Antwort.
     */
    protected function decodeAndValidateJson(string $rawJson): array
    {
        $rawJson = trim($rawJson);
        // Markdown Code-Blöcke ```json ... ``` entfernen, falls vorhanden
        $rawJson = preg_replace('/^```(?:json)?\s*/i', '', $rawJson);
        $rawJson = preg_replace('/\s*```$/', '', $rawJson);

        $decoded = json_decode($rawJson, true);
        if (!is_array($decoded) || empty($decoded['title'])) {
            throw new \Exception("Die KI hat keine gültige Rezeptstruktur zurückgegeben: " . mb_substr($rawJson, 0, 200));
        }

        return [
            'detected_ingredients' => $decoded['detected_ingredients'] ?? [],
            'title' => trim($decoded['title']),
            'description' => trim($decoded['description'] ?? ''),
            'portions' => (int) ($decoded['portions'] ?? 4),
            'prep_time_minutes' => isset($decoded['prep_time_minutes']) ? (int) $decoded['prep_time_minutes'] : null,
            'cook_time_minutes' => isset($decoded['cook_time_minutes']) ? (int) $decoded['cook_time_minutes'] : null,
            'calories' => isset($decoded['calories']) && is_numeric($decoded['calories']) ? (int) round((float) $decoded['calories']) : null,
            'carbohydrates' => isset($decoded['carbohydrates']) && is_numeric($decoded['carbohydrates']) ? round((float) $decoded['carbohydrates'], 1) : null,
            'fat' => isset($decoded['fat']) && is_numeric($decoded['fat']) ? round((float) $decoded['fat'], 1) : null,
            'protein' => isset($decoded['protein']) && is_numeric($decoded['protein']) ? round((float) $decoded['protein'], 1) : null,
            'ingredients' => $decoded['ingredients'] ?? [],
            'steps' => $decoded['steps'] ?? [],
        ];
    }

    /**
     * Schätzt oder berechnet die Nährwerte (Kalorien, Kohlenhydrate, Fett, Eiweiss) pro Portion
     * für ein Rezept anhand von Titel, Portionen und Zutaten.
     *
     * @param string $title
     * @param int $portions
     * @param array $ingredients Array von RecipeIngredient Models, Arrays oder Strings
     * @return array ['calories' => int, 'carbohydrates' => float, 'fat' => float, 'protein' => float, 'source' => string]
     */
    public function estimateNutrition(string $title, int $portions, array $ingredients): array
    {
        $portions = max(1, $portions);

        // 1. Zutaten formatieren
        $ingredientsList = [];
        foreach ($ingredients as $ing) {
            if (is_string($ing)) {
                $ingredientsList[] = "- {$ing}";
            } elseif (is_array($ing) || is_object($ing)) {
                $name = is_array($ing) ? ($ing['name'] ?? '') : ($ing->name ?? '');
                $amount = is_array($ing)
                    ? ($ing['amount_text'] ?? trim(($ing['amount_numeric'] ?? '') . ' ' . ($ing['unit'] ?? '')))
                    : ($ing->amount_text ?? trim(($ing->amount_numeric ?? '') . ' ' . ($ing->unit ?? '')));
                $ingredientsList[] = "- " . trim("{$amount} {$name}");
            }
        }
        $ingredientsText = implode("\n", $ingredientsList);

        // 2. Falls KI konfiguriert ist, per Gemini / OpenAI anfragen
        if ($this->isConfigured()) {
            try {
                $prompt = "Du bist ein erfahrener Ernährungsberater und Lebensmittelchemiker.\n"
                    . "Berechne bitte präzise und realistische Nährwerte PRO PORTION für folgendes Rezept:\n\n"
                    . "Rezepttitel: {$title}\n"
                    . "Anzahl Portionen: {$portions}\n"
                    . "Zutaten für das Gesamtrezept:\n{$ingredientsText}\n\n"
                    . "WICHTIGE ANWEISUNGEN:\n"
                    . "- Berechne die Nährwerte PRO PORTION (Gesamtnährwerte geteilt durch {$portions}).\n"
                    . "- Antworte AUSSCHLIESSLICH als valides JSON ohne Markdown oder Zusatztext:\n"
                    . "{\n"
                    . '  "calories": 450,' . "\n"
                    . '  "carbohydrates": 38.5,' . "\n"
                    . '  "fat": 14.2,' . "\n"
                    . '  "protein": 21.0' . "\n"
                    . "}";

                $provider = $this->getActiveProvider();
                $nutritionData = null;

                if ($provider === 'gemini') {
                    $apiKey = $this->getGeminiKey();
                    $primaryModel = $this->resolveGeminiModel($apiKey);
                    $modelsToTry = array_values(array_unique(array_filter([
                        $primaryModel,
                        'gemini-3-flash-preview',
                        'gemini-3.8-flash',
                        'gemini-3.7-flash',
                        'gemini-3.5-flash',
                    ])));

                    $payload = [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]],
                        ],
                        'generationConfig' => [
                            'response_mime_type' => 'application/json',
                            'temperature' => 0.2,
                        ],
                    ];

                    foreach ($modelsToTry as $model) {
                        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                        $response = Http::withOptions([
                            'verify' => false,
                            'force_ip_resolve' => 'v4',
                        ])->timeout(20)->post($url, $payload);

                        if ($response->successful()) {
                            $data = $response->json();
                            $rawJson = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                            $parsed = json_decode($rawJson, true);
                            if (is_array($parsed) && isset($parsed['calories'])) {
                                $nutritionData = $parsed;
                                break;
                            }
                        }
                    }
                } elseif ($provider === 'openai') {
                    $response = Http::withOptions([
                        'verify' => false,
                    ])->withToken($this->getOpenAiKey())
                      ->timeout(20)
                      ->post('https://api.openai.com/v1/chat/completions', [
                          'model' => 'gpt-4o-mini',
                          'messages' => [
                              ['role' => 'system', 'content' => 'Du bist ein präziser Nährwertrechner. Gib nur JSON zurück.'],
                              ['role' => 'user', 'content' => $prompt],
                          ],
                          'response_format' => ['type' => 'json_object'],
                          'temperature' => 0.2,
                      ]);

                    if ($response->successful()) {
                        $rawJson = $response->json('choices.0.message.content') ?? '{}';
                        $parsed = json_decode($rawJson, true);
                        if (is_array($parsed) && isset($parsed['calories'])) {
                            $nutritionData = $parsed;
                        }
                    }
                }

                if ($nutritionData && isset($nutritionData['calories'])) {
                    return [
                        'calories' => (int) round((float) $nutritionData['calories']),
                        'carbohydrates' => (float) round((float) ($nutritionData['carbohydrates'] ?? 0), 1),
                        'fat' => (float) round((float) ($nutritionData['fat'] ?? 0), 1),
                        'protein' => (float) round((float) ($nutritionData['protein'] ?? 0), 1),
                        'source' => "KI ({$provider})",
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("Fehler bei KI-Nährwertberechnung: " . $e->getMessage());
            }
        }

        // 3. Fallback: Heuristische Nährwertberechnung
        return $this->calculateHeuristicNutrition($portions, $ingredients);
    }

    /**
     * Heuristische Schätzung der Nährwerte anhand von Zutatenmengen und Referenztabelle.
     */
    public function calculateHeuristicNutrition(int $portions, array $ingredients): array
    {
        $portions = max(1, $portions);

        $totalCalories = 0.0;
        $totalCarbs = 0.0;
        $totalFat = 0.0;
        $totalProtein = 0.0;

        foreach ($ingredients as $ing) {
            $name = '';
            $amount = 0.0;
            $unit = '';

            if (is_string($ing)) {
                $name = $ing;
                if (preg_match('/^([\d\.,]+)\s*([a-zA-ZäöüÄÖÜß]+)?\s+(.+)$/u', trim($ing), $m)) {
                    $amount = (float) str_replace(',', '.', $m[1]);
                    $unit = strtolower($m[2] ?? '');
                    $name = $m[3];
                }
            } elseif (is_array($ing) || is_object($ing)) {
                $name = is_array($ing) ? ($ing['name'] ?? '') : ($ing->name ?? '');
                $amount = (float) (is_array($ing) ? ($ing['amount_numeric'] ?? 0) : ($ing->amount_numeric ?? 0));
                $unit = strtolower((string) (is_array($ing) ? ($ing['unit'] ?? '') : ($ing->unit ?? '')));

                if ($amount <= 0) {
                    $text = is_array($ing) ? ($ing['amount_text'] ?? '') : ($ing->amount_text ?? '');
                    if (preg_match('/([\d\.,]+)/', $text, $m)) {
                        $amount = (float) str_replace(',', '.', $m[1]);
                    }
                }
            }

            $nameLower = mb_strtolower($name);

            // Gewicht in Gramm schätzen
            $grams = 0.0;
            if (in_array($unit, ['g', 'gramm', 'gr'])) {
                $grams = $amount;
            } elseif (in_array($unit, ['kg', 'kilogramm'])) {
                $grams = $amount * 1000.0;
            } elseif (in_array($unit, ['ml', 'milliliter'])) {
                $grams = $amount; // Näherung 1ml ~ 1g
            } elseif (in_array($unit, ['dl', 'deziliter'])) {
                $grams = $amount * 100.0;
            } elseif (in_array($unit, ['l', 'liter'])) {
                $grams = $amount * 1000.0;
            } elseif (in_array($unit, ['el', 'esslöffel', 'tbsp'])) {
                $grams = $amount * 12.0;
            } elseif (in_array($unit, ['tl', 'teelöffel', 'tsp'])) {
                $grams = $amount * 5.0;
            } elseif (in_array($unit, ['prise', 'spritzer'])) {
                $grams = 1.0;
            } elseif (in_array($unit, ['stk', 'stück', 'stueck', 'stk.']) || empty($unit)) {
                if (str_contains($nameLower, 'ei')) {
                    $grams = $amount > 0 ? $amount * 55.0 : 55.0;
                } elseif (str_contains($nameLower, 'zwiebel')) {
                    $grams = $amount > 0 ? $amount * 90.0 : 90.0;
                } elseif (str_contains($nameLower, 'knoblauch')) {
                    $grams = $amount > 0 ? $amount * 4.0 : 4.0;
                } elseif (str_contains($nameLower, 'zucchini')) {
                    $grams = $amount > 0 ? $amount * 200.0 : 200.0;
                } elseif (str_contains($nameLower, 'paprika')) {
                    $grams = $amount > 0 ? $amount * 160.0 : 160.0;
                } elseif (str_contains($nameLower, 'karotte') || str_contains($nameLower, 'rüebli')) {
                    $grams = $amount > 0 ? $amount * 80.0 : 80.0;
                } else {
                    $grams = $amount > 0 ? $amount * 100.0 : 100.0;
                }
            } else {
                $grams = $amount > 0 ? $amount : 100.0;
            }

            // Nährwerte pro 100g [kcal, carbs, fat, protein]
            $profile = [50.0, 5.0, 1.0, 2.0]; // Standard Gemüse/neutral

            if (str_contains($nameLower, 'öl') || str_contains($nameLower, 'olivenöl') || str_contains($nameLower, 'rapsöl')) {
                $profile = [884.0, 0.0, 100.0, 0.0];
            } elseif (str_contains($nameLower, 'butter')) {
                $profile = [717.0, 0.7, 81.0, 0.8];
            } elseif (str_contains($nameLower, 'couscous') || str_contains($nameLower, 'bulgur')) {
                $profile = [350.0, 72.0, 1.5, 12.0];
            } elseif (str_contains($nameLower, 'reis')) {
                $profile = [355.0, 78.0, 0.7, 7.0];
            } elseif (str_contains($nameLower, 'nudel') || str_contains($nameLower, 'pasta') || str_contains($nameLower, 'spaghetti') || str_contains($nameLower, 'teigwaren')) {
                $profile = [360.0, 71.0, 1.5, 12.0];
            } elseif (str_contains($nameLower, 'mehl')) {
                $profile = [345.0, 70.0, 1.2, 10.0];
            } elseif (str_contains($nameLower, 'zucker') || str_contains($nameLower, 'honig')) {
                $profile = [390.0, 98.0, 0.0, 0.0];
            } elseif (str_contains($nameLower, 'hackfleisch') || str_contains($nameLower, 'rind')) {
                $profile = [240.0, 0.0, 16.0, 21.0];
            } elseif (str_contains($nameLower, 'hähnchen') || str_contains($nameLower, 'poulet') || str_contains($nameLower, 'hühner')) {
                $profile = [120.0, 0.0, 2.0, 24.0];
            } elseif (str_contains($nameLower, 'fisch') || str_contains($nameLower, 'lachs')) {
                $profile = [200.0, 0.0, 13.0, 20.0];
            } elseif (str_contains($nameLower, 'käse') || str_contains($nameLower, 'parmesan') || str_contains($nameLower, 'mozzarella')) {
                $profile = [330.0, 2.0, 26.0, 25.0];
            } elseif (str_contains($nameLower, 'sahne') || str_contains($nameLower, 'rahm')) {
                $profile = [300.0, 3.2, 30.0, 2.4];
            } elseif (str_contains($nameLower, 'milch')) {
                $profile = [64.0, 4.8, 3.5, 3.3];
            } elseif (str_contains($nameLower, 'ei') && !str_contains($nameLower, 'teig')) {
                $profile = [145.0, 0.7, 10.0, 13.0];
            } elseif (str_contains($nameLower, 'linse') || str_contains($nameLower, 'bohne') || str_contains($nameLower, 'kichererbse')) {
                $profile = [115.0, 17.0, 0.6, 8.0];
            } elseif (str_contains($nameLower, 'nuss') || str_contains($nameLower, 'mandel')) {
                $profile = [600.0, 10.0, 52.0, 20.0];
            } elseif (str_contains($nameLower, 'kartoffel')) {
                $profile = [77.0, 17.0, 0.1, 2.0];
            } elseif (str_contains($nameLower, 'tomate') || str_contains($nameLower, 'passata')) {
                $profile = [20.0, 3.9, 0.2, 0.9];
            } elseif (str_contains($nameLower, 'zucchini') || str_contains($nameLower, 'paprika') || str_contains($nameLower, 'karotte') || str_contains($nameLower, 'brokkoli')) {
                $profile = [28.0, 5.0, 0.3, 1.8];
            }

            $factor = $grams / 100.0;
            $totalCalories += $profile[0] * $factor;
            $totalCarbs += $profile[1] * $factor;
            $totalFat += $profile[2] * $factor;
            $totalProtein += $profile[3] * $factor;
        }

        return [
            'calories' => (int) round($totalCalories / $portions),
            'carbohydrates' => (float) round($totalCarbs / $portions, 1),
            'fat' => (float) round($totalFat / $portions, 1),
            'protein' => (float) round($totalProtein / $portions, 1),
            'source' => 'Heuristische Schätzung',
        ];
    }

    /**
     * Speichert einen API-Schlüssel in der lokalen .env-Datei.
     */
    public function updateEnvKey(string $keyName, string $keyValue): bool
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return false;
        }

        $content = file_get_contents($envPath);
        $pattern = "/^{$keyName}=.*$/m";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$keyName}=" . trim($keyValue), $content);
        } else {
            $content .= "\n{$keyName}=" . trim($keyValue);
        }

        return (bool) file_put_contents($envPath, $content);
    }
}
