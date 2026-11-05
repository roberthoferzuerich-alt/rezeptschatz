<?php

namespace Tests\Feature;

use App\Livewire\RecipeScanner;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeScanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecipeScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_scanner_page_is_accessible_for_authenticated_users(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('recipes.scan'));

        $response->assertStatus(200);
        $response->assertSee('Scann ein Rezept');
    }

    public function test_scan_service_parses_german_recipe_text_accurately(): void
    {
        $scanService = app(RecipeScanService::class);

        $text = <<<TEXT
Zitronen-Poulet mit Rosmarinkartoffeln
Ein herrlich frisches Sonntagsgericht.
Für 4 Personen
Vorbereitungszeit: 25 min
Kochzeit: 40 min

Zutaten:
- 600 g Pouletbrüstli
- 800 g Kartoffeln festkochend
- 2 EL Olivenöl
- 1 Bio-Zitrone
- 2 Zweige Rosmarin
- 1 TL Salz
- Pfeffer nach Bedarf

Zubereitung:
1. Den Backofen auf 200 Grad vorheizen.
2. Kartoffeln waschen und vierteln, mit Olivenöl und Rosmarin mischen.
3. Pouletbrüstli mit Zitronensaft beträufeln und würzen.
4. Alles auf ein Blech geben und 40 Minuten goldbraun backen.
TEXT;

        $parsed = $scanService->parseScannedText($text);

        $this->assertEquals('Zitronen-Poulet mit Rosmarinkartoffeln', $parsed['title']);
        $this->assertEquals(4, $parsed['portions']);
        $this->assertEquals(25, $parsed['prep_time_minutes']);
        $this->assertEquals(40, $parsed['cook_time_minutes']);
        $this->assertCount(7, $parsed['ingredients']);
        $this->assertCount(4, $parsed['steps']);

        // Erste Zutat prüfen
        $this->assertEquals('Pouletbrüstli', $parsed['ingredients'][0]['name']);
        $this->assertEquals(600, $parsed['ingredients'][0]['amount_numeric']);
        $this->assertEquals('g', $parsed['ingredients'][0]['unit']);

        // Ersten Schritt prüfen
        $this->assertEquals('Den Backofen auf 200 Grad vorheizen.', $parsed['steps'][0]['instruction']);
    }

    public function test_livewire_scanner_can_parse_and_save_recipe(): void
    {
        $user = User::factory()->create();

        $text = <<<TEXT
Bündner Gerstensuppe
Klassische wärmende Suppe aus Graubünden
Für 4 Personen

Zutaten:
- 100 g Rollgerste
- 150 g Bündnerfleisch
- 2 Rüebli
- 1 Lauch
- 1 Liter Gemüsebouillon

Zubereitung:
1. Das Gemüse und Fleisch klein schneiden.
2. Alles in der Bouillon ca. 60 Minuten weich köcheln.
TEXT;

        Livewire::actingAs($user)
            ->test(RecipeScanner::class)
            ->set('rawText', $text)
            ->call('parseText')
            ->assertSet('isParsed', true)
            ->assertSet('title', 'Bündner Gerstensuppe')
            ->assertCount('ingredients', 5)
            ->assertCount('steps', 2)
            ->call('saveRecipe')
            ->assertHasNoErrors();

        $recipe = Recipe::where('title', 'Bündner Gerstensuppe')->first();
        $this->assertNotNull($recipe);
        $this->assertEquals($user->id, $recipe->user_id);
        $this->assertEquals(4, $recipe->portions);
        $this->assertCount(5, $recipe->ingredients);
        $this->assertCount(2, $recipe->steps);

        // Fleisch- und Gemüse-Kategorien sollten automatisch zugewiesen sein
        $catNames = $recipe->categories->pluck('name')->toArray();
        $this->assertContains('Fleisch', $catNames);
        $this->assertContains('Gemüse', $catNames);
    }
}
