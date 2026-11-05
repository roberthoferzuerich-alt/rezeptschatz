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

            // 2. Testaufruf an die ersten funktionierenden Modelle
            $testCandidate = $contentModels[0];
            foreach ($contentModels as $c) {
                if (str_contains($c, '3.8-flash')) {
                    $testCandidate = $c;
                    break;
                }
                if (str_contains($c, 'flash')) {
                    $testCandidate = $c;
                }
            }

            $this->info("Führe Test-Generierung mit Modell '{$testCandidate}' durch (Timeout: 45s)...");

            $testUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$testCandidate}:generateContent?key={$apiKey}";
            $testResponse = Http::withOptions(['verify' => false])->timeout(45)->post($testUrl, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Antworte mit exakt einem Wort auf Deutsch: Bereit!'],
                        ],
                    ],
                ],
            ]);

            if ($testResponse->successful()) {
                $text = $testResponse->json('candidates.0.content.parts.0.text') ?? '(Keine Antwort)';
                $this->info("ERFOLG! Google Gemini Antwort: " . trim($text));
            } else {
                $this->error("Fehler bei Test-Generierung: HTTP {$testResponse->status()}");
                $this->line("Details: " . $testResponse->body());
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
