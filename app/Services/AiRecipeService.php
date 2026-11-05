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
        $preferred = Cache::get('ai_provider') 
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
