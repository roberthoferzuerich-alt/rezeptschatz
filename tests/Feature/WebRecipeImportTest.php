<?php

namespace Tests\Feature;

use App\Livewire\RecipeForm;
use App\Livewire\WebRecipeImport;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Services\WebRecipeImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class WebRecipeImportTest extends TestCase
{
    use RefreshDatabase;

    protected function getCompleteRecipeHtml(): string
    {
        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => 'Klassisches Kartoffelgratin',
            'description' => 'Cremiges Schweizer Kartoffelgratin mit feinem Rahm und Käse überbacken.',
            'image' => 'https://example.com/kartoffelgratin.jpg',
            'recipeYield' => '4 Portionen',
            'prepTime' => 'PT20M',
            'cookTime' => 'PT45M',
            'recipeIngredient' => [
                '800 g festkochende Kartoffeln',
                '250 ml Rahm',
                '1 Knoblauchzehe',
                '100 g geriebener Gruyère',
                '1 Prise Muskatnuss',
            ],
            'recipeInstructions' => [
                ['@type' => 'HowToStep', 'text' => 'Kartoffeln schälen und in feine Scheiben hobeln.'],
                ['@type' => 'HowToStep', 'text' => 'Auflaufform mit Knoblauch einreiben und Kartoffeln einschichten.'],
                ['@type' => 'HowToStep', 'text' => 'Rahm würzen, darübergießen und mit Käse bestreuen.'],
                ['@type' => 'HowToStep', 'text' => 'Bei 180 Grad 45 Minuten goldbraun backen.'],
            ],
            'nutrition' => [
                '@type' => 'NutritionInformation',
                'calories' => '480 kcal',
                'carbohydrateContent' => '32 g',
                'fatContent' => '28 g',
                'proteinContent' => '14 g',
            ],
            'recipeCategory' => 'Auflauf, Schweizer Klassiker',
        ]);

        return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Klassisches Kartoffelgratin | Betty Bossi</title>
    <script type="application/ld+json">{$jsonLd}</script>
</head>
<body>
    <h1>Klassisches Kartoffelgratin</h1>
</body>
</html>
HTML;
    }

    protected function getIncompleteRecipeHtml(): string
    {
        // Nur OpenGraph Title, keine Zutaten und keine Zubereitungsschritte
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Geschützter Artikel ohne strukturierte Daten</title>
    <meta property="og:title" content="Geheimes Schokoladendessert" />
    <meta property="og:description" content="Ein leckeres Dessert, aber ohne offene Zutaten." />
</head>
<body>
    <p>Bitte registrieren Sie sich, um das Rezept zu sehen.</p>
</body>
</html>
HTML;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/recipes/import-web');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_import_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/recipes/import-web');
        $response->assertStatus(200);
        $response->assertSee('Rezept aus dem Internet importieren');
        $response->assertSee('Webseite (URL)');
        $response->assertSee('COOKmate-Backup (.mcb)');
    }

    public function test_parses_complete_recipe_from_web_url(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.bettybossi.ch/rezept/kartoffelgratin' => Http::response($this->getCompleteRecipeHtml(), 200),
            'https://example.com/kartoffelgratin.jpg' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Livewire::actingAs($user)
            ->test(WebRecipeImport::class)
            ->set('url', 'https://www.bettybossi.ch/rezept/kartoffelgratin')
            ->call('analyzeUrl')
            ->assertSet('parsedData.is_complete', true)
            ->assertSet('parsedData.title', 'Klassisches Kartoffelgratin')
            ->assertSet('parsedData.portions', 4)
            ->assertSet('parsedData.prep_time_minutes', 20)
            ->assertSet('parsedData.cook_time_minutes', 45)
            ->assertSee('Rezept vollständig erkannt!')
            ->assertSee('800 g festkochende Kartoffeln')
            ->assertSee('Kartoffeln schälen und in feine Scheiben hobeln.')
            ->assertSee('Rezept direkt speichern');
    }

    public function test_warns_on_incomplete_recipe_and_allows_cancelling(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://example.com/incomplete' => Http::response($this->getIncompleteRecipeHtml(), 200),
        ]);

        $component = Livewire::actingAs($user)
            ->test(WebRecipeImport::class)
            ->set('url', 'https://example.com/incomplete')
            ->call('analyzeUrl')
            ->assertSet('parsedData.is_complete', false)
            ->assertSee('Achtung: Das Rezept konnte nicht vollständig importiert werden!')
            ->assertSee('Keine Zutatenliste erkannt')
            ->assertSee('Keine Zubereitungsschritte erkannt')
            ->assertSee('Vorgang abbrechen')
            ->assertSee('Trotzdem übernehmen');

        // Benutzer wählt "Vorgang abbrechen"
        $component->call('cancel')
            ->assertSet('parsedData', null)
            ->assertSet('url', '')
            ->assertDontSee('Achtung: Das Rezept konnte nicht vollständig importiert werden!');
    }

    public function test_can_save_complete_recipe_directly(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.bettybossi.ch/rezept/kartoffelgratin' => Http::response($this->getCompleteRecipeHtml(), 200),
            'https://example.com/kartoffelgratin.jpg' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Livewire::actingAs($user)
            ->test(WebRecipeImport::class)
            ->set('url', 'https://www.bettybossi.ch/rezept/kartoffelgratin')
            ->call('analyzeUrl')
            ->call('saveDirectly')
            ->assertRedirect();

        $recipe = Recipe::where('user_id', $user->id)->first();
        $this->assertNotNull($recipe);
        $this->assertEquals('Klassisches Kartoffelgratin', $recipe->title);
        $this->assertEquals(4, $recipe->portions);
        $this->assertEquals(20, $recipe->prep_time_minutes);
        $this->assertEquals(45, $recipe->cook_time_minutes);
        $this->assertEquals(480, $recipe->calories);
        $this->assertCount(5, $recipe->ingredients);
        $this->assertCount(4, $recipe->steps);
    }

    public function test_can_forward_incomplete_recipe_to_form_to_complete_manually(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://example.com/incomplete' => Http::response($this->getIncompleteRecipeHtml(), 200),
        ]);

        Livewire::actingAs($user)
            ->test(WebRecipeImport::class)
            ->set('url', 'https://example.com/incomplete')
            ->call('analyzeUrl')
            ->call('editBeforeSave')
            ->assertRedirect(route('recipes.create'));

        $this->assertTrue(session()->has('imported_web_recipe'));

        // RecipeForm ruft mount() auf und übernimmt die Daten
        Livewire::actingAs($user)
            ->test(RecipeForm::class)
            ->assertSet('title', 'Geheimes Schokoladendessert')
            ->assertSet('source_url', 'https://example.com/incomplete');
    }
}
