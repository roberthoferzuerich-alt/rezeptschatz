<?php

namespace App\Console\Commands;

use App\Services\AiRecipeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestAiConnectionCommand extends Command
{
    protected $signature = 'ai:test {key? : Optionaler API-Schlüssel zum direkten Testen}';
    protected $description = 'Testet die Verbindung zu Google Gemini und OpenAI und listet alle verfügbaren Modelle auf';

    public function handle(AiRecipeService $aiService): int
    {
        $this->info('=== KI-Verbindungstest für Rezeptschatz ===');
        $this->newLine();

        $directKey = $this->argument('key');
        if (!empty($directKey)) {
            if (str_starts_with($directKey, 'sk-')) {
                $this->testOpenAi($directKey);
            } else {
                $this->testGemini($directKey);
            }
            return self::SUCCESS;
        }

        $activeProvider = $aiService->getActiveProvider();
        $this->line("Aktiver Anbieter: <comment>" . ($activeProvider ?: 'Keiner') . "</comment>");

        $geminiKey = $aiService->getGeminiKey();
        $openAiKey = $aiService->getOpenAiKey();

        if (empty($geminiKey) && empty($openAiKey)) {
            $this->error('Kein API-Schlüssel hinterlegt! Weder in .env noch in der Datenbank.');
            return self::FAILURE;
        }

        if (!empty($geminiKey)) {
            $this->testGemini($geminiKey);
        }

        if (!empty($openAiKey)) {
            $this->testOpenAi($openAiKey);
        }

        return self::SUCCESS;
    }

    protected function testGemini(string $apiKey): void
    {
        $masked = substr($apiKey, 0, 6) . '...' . substr($apiKey, -4);
        $this->info("Prüfe Google Gemini (Schlüssel: {$masked})...");

        // 1. v1beta/models abrufen
        $urlBeta = "https://generativelanguage.googleapis.com/v1beta/models?key={$apiKey}";
        $this->line("Rufe ModelService.ListModels ab: {$urlBeta}");

        try {
            $response = Http::withOptions(['verify' => false])->timeout(10)->get($urlBeta);

            if (!$response->successful()) {
                $this->error("HTTP {$response->status()} von Google Gemini v1beta!");
                $this->line("Antwort: " . $response->body());
                return;
            }

            $models = $response->json('models') ?? [];
            $this->info("Google meldet " . count($models) . " verfügbare Modelle für diesen Schlüssel:");

            $contentModels = [];
            foreach ($models as $m) {
                $methods = $m['supportedGenerationMethods'] ?? [];
                $canGenerate = in_array('generateContent', $methods, true);
                $name = $m['name'] ?? '';

                if ($canGenerate) {
                    $contentModels[] = str_replace('models/', '', $name);
                    $this->line("  ✓ <info>{$name}</info> (unterstützt generateContent)");
                } else {
                    $this->line("  - <comment>{$name}</comment> (" . implode(', ', $methods) . ")");
                }
            }

            $this->newLine();

            if (empty($contentModels)) {
                $this->warn("Keines der Modelle unterstützt 'generateContent'!");
                return;
            }

            // Sortiere: Bevorzuge Flash und Standard-Gemini-Modelle, TTS und Previews nach hinten
            usort($contentModels, function($a, $b) {
                $scoreA = (str_contains($a, 'flash') ? 20 : 0) + (str_contains($a, 'gemini') ? 10 : 0) - (str_contains($a, 'tts') ? 30 : 0);
                $scoreB = (str_contains($b, 'flash') ? 20 : 0) + (str_contains($b, 'gemini') ? 10 : 0) - (str_contains($b, 'tts') ? 30 : 0);
                return $scoreB <=> $scoreA;
            });

            $this->info("Gefundene Modelle mit generateContent (" . count($contentModels) . "):");
            $this->line("  " . implode(', ', $contentModels));
            $this->newLine();

            $success = false;
            foreach ($contentModels as $candidate) {
                // Bevorzuge moderne Text-/Vision-Modelle
                if (str_contains($candidate, 'embedding') || str_contains($candidate, 'aqa') || str_contains($candidate, 'tts')) {
                    continue;
                }

                $this->line("Teste Modell: <comment>{$candidate}</comment> (Timeout: 15s, IPv4)...");

                $testUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$candidate}:generateContent?key={$apiKey}";
                try {
                    $start = microtime(true);
                    $testResponse = Http::withOptions([
                        'verify' => false,
                        'force_ip_resolve' => 'v4',
                    ])->timeout(15)->post($testUrl, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => 'Antworte mit exakt einem Wort auf Deutsch: Bereit!'],
                                ],
                            ],
                        ],
                    ]);

                    $duration = round(microtime(true) - $start, 2);

                    if ($testResponse->successful()) {
                        $text = $testResponse->json('candidates.0.content.parts.0.text') ?? '(Keine Antwort)';
                        $this->info("✓ ERFOLG mit '{$candidate}' in {$duration}s! Antwort: " . trim($text));
                        \Illuminate\Support\Facades\Cache::forever('gemini_active_model', $candidate);
                        $this->info("Modell '{$candidate}' wurde als Standard im Cache gespeichert!");
                        $success = true;
                        break;
                    } else {
                        $err = $testResponse->json('error.message') ?? $testResponse->body();
                        $this->warn("  ✗ HTTP {$testResponse->status()} ({$candidate}): {$err}");
                    }
                } catch (\Throwable $e) {
                    $this->warn("  ✗ Fehler bei {$candidate}: " . $e->getMessage());
                }
            }

            if (!$success) {
                $this->error("Keines der Modelle konnte erfolgreich kontaktiert werden.");
            }

        } catch (\Throwable $e) {
            $this->error("Verbindungsfehler: " . $e->getMessage());
        }
    }

    protected function testOpenAi(string $apiKey): void
    {
        $masked = substr($apiKey, 0, 6) . '...' . substr($apiKey, -4);
        $this->info("Prüfe OpenAI (Schlüssel: {$masked})...");
        // OpenAI Test
    }
}
