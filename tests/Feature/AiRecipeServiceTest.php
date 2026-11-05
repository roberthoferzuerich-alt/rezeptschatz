<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Services\AiRecipeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiRecipeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_gemini_vision_call_returns_structured_recipe(): void
    {
        config([
            'services.ai.provider' => 'gemini',
            'services.gemini.api_key' => 'test_key_123',
        ]);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'detected_ingredients' => ['Eier', 'Käse', 'Tomaten'],
                                        'title' => 'Schweizer Omelette mit Tomaten',
                                        'description' => 'Feines Omelette aus Kühlschrank-Resten.',
                                        'portions' => 2,
                                        'prep_time_minutes' => 5,
                                        'cook_time_minutes' => 10,
                                        'calories' => 380,
                                        'carbohydrates' => 2.5,
                                        'fat' => 28.0,
                                        'protein' => 22.0,
                                        'ingredients' => [
                                            ['name' => 'Eier', 'amount_numeric' => 4, 'unit' => 'stk', 'amount_text' => '4 Stk'],
                                            ['name' => 'Gruyère', 'amount_numeric' => 50, 'unit' => 'g', 'amount_text' => '50 g'],
                                        ],
                                        'steps' => [
                                            ['instruction' => 'Eier verquirlen.', 'step_number' => 1],
                                            ['instruction' => 'In der Pfanne mit Käse backen.', 'step_number' => 2],
                                        ],
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(AiRecipeService::class);
        $result = $service->generateFromFridgeImage('fake_image_bytes', 'image/jpeg', ['portions' => 2]);

        $this->assertEquals('Schweizer Omelette mit Tomaten', $result['title']);
        $this->assertEquals(2, $result['portions']);
        $this->assertEquals(380, $result['calories']);
        $this->assertEquals(2.5, $result['carbohydrates']);
        $this->assertEquals(28.0, $result['fat']);
        $this->assertEquals(22.0, $result['protein']);
        $this->assertContains('Eier', $result['detected_ingredients']);
        $this->assertCount(2, $result['ingredients']);
        $this->assertCount(2, $result['steps']);

        putenv('GEMINI_API_KEY');
    }

    public function test_openai_vision_call_returns_structured_recipe(): void
    {
        config([
            'services.ai.provider' => 'openai',
            'services.openai.api_key' => 'sk-test-key-456',
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'detected_ingredients' => ['Rüebli', 'Zwiebeln', 'Kartoffeln'],
                                'title' => 'Bunte Gemüsesuppe',
                                'description' => 'Wärmende Suppe aus Vorratsgemüse.',
                                'portions' => 4,
                                'prep_time_minutes' => 15,
                                'cook_time_minutes' => 25,
                                'ingredients' => [
                                    ['name' => 'Rüebli', 'amount_numeric' => 300, 'unit' => 'g', 'amount_text' => '300 g'],
                                ],
                                'steps' => [
                                    ['instruction' => 'Gemüse schneiden und kochen.', 'step_number' => 1],
                                ],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = app(AiRecipeService::class);
        $result = $service->generateFromFridgeImage('fake_image_bytes', 'image/jpeg', ['portions' => 4]);

        $this->assertEquals('Bunte Gemüsesuppe', $result['title']);
        $this->assertContains('Rüebli', $result['detected_ingredients']);

        putenv('OPENAI_API_KEY');
        putenv('AI_PROVIDER=gemini');
    }

    public function test_can_save_and_remove_api_key_via_service(): void
    {
        $service = app(AiRecipeService::class);
        $service->saveApiKey('gemini', 'my_saved_gemini_key');

        $this->assertTrue($service->isConfigured());
        $this->assertEquals('gemini', $service->getActiveProvider());
        $this->assertEquals('my_saved_gemini_key', $service->getGeminiKey());

        $service->removeApiKey();
        $this->assertNull($service->getGeminiKey());
    }
}
