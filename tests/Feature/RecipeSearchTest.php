<?php

namespace Tests\Feature;

use App\Livewire\RecipeForm;
use App\Livewire\RecipeSearch;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class RecipeSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function getCompleteRecipeHtml(): string
    {
        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => 'Klassisches Kartoffelgratin',
            'description' => 'Cremiges Schweizer Kartoffelgratin mit feinem Rahm und Gruyère.',
            'image' => 'https://example.com/kartoffelgratin.jpg',
            'recipeYield' => '4 Portionen',
            'prepTime' => 'PT20M',
            'cookTime' => 'PT45M',
            'recipeIngredient' => [
                '800 g festkochende Kartoffeln',
                '250 ml Rahm',
                '1 Knoblauchzehe',
                '100 g geriebener Gruyère',
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
        ]);

        return <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Klassisches Kartoffelgratin</title>
    <script type="application/ld+json">{$jsonLd}</script>
</head>
<body>
    <h1>Klassisches Kartoffelgratin</h1>
</body>
</html>
HTML;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/recipes/search');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_search_page_with_cookmate_pattern(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/recipes/search');
        $response->assertStatus(200);
        $response->assertSee('Suche Rezepte auf kompatiblen');
        $response->assertSee('Suche oder URL-Eingabe');
        $response->assertSee('Sprache');
        $response->assertSee('Seite');
        $response->assertSee('Suchen');
        $response->assertSee('Eigener Rezeptschatz');
        $response->assertSee('Chefkoch.de');
        $response->assertSee('Betty Bossi');
    }

    public function test_can_search_local_recipes_in_rezeptschatz(): void
    {
        $user = User::factory()->create();

        Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Zürcher Geschnetzeltes Original',
            'portions' => 4,
            'prep_time_minutes' => 30,
            'server_version' => 1,
        ]);

        Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Spaghetti Bolognese',
            'portions' => 2,
            'prep_time_minutes' => 20,
            'server_version' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(RecipeSearch::class)
            ->set('site', 'rezeptschatz')
            ->set('query', 'Zürcher')
            ->call('search')
            ->assertSet('hasSearched', true)
            ->assertSee('Zürcher Geschnetzeltes Original')
            ->assertDontSee('Spaghetti Bolognese');
    }

    public function test_pasting_url_fetches_and_shows_recipe_preview(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.bettybossi.ch/rezept/kartoffelgratin' => Http::response($this->getCompleteRecipeHtml(), 200),
            'https://example.com/kartoffelgratin.jpg' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Livewire::actingAs($user)
            ->test(RecipeSearch::class)
            ->set('query', 'https://www.bettybossi.ch/rezept/kartoffelgratin')
            ->call('search')
            ->assertSet('parsedRecipe.is_complete', true)
            ->assertSet('parsedRecipe.title', 'Klassisches Kartoffelgratin')
            ->assertSet('parsedRecipe.portions', 4)
            ->assertSee('Rezept vollständig erkannt')
            ->assertSee('800 g festkochende Kartoffeln')
            ->assertSee('In Rezeptschatz speichern');
    }

    public function test_can_save_parsed_recipe_directly(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.bettybossi.ch/rezept/kartoffelgratin' => Http::response($this->getCompleteRecipeHtml(), 200),
            'https://example.com/kartoffelgratin.jpg' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Livewire::actingAs($user)
            ->test(RecipeSearch::class)
            ->set('query', 'https://www.bettybossi.ch/rezept/kartoffelgratin')
            ->call('search')
            ->call('saveParsedRecipe')
            ->assertRedirect();

        $saved = Recipe::where('user_id', $user->id)->first();
        $this->assertNotNull($saved);
        $this->assertEquals('Klassisches Kartoffelgratin', $saved->title);
        $this->assertEquals(4, $saved->portions);
        $this->assertEquals(20, $saved->prep_time_minutes);
        $this->assertEquals(45, $saved->cook_time_minutes);
        $this->assertEquals(480, $saved->calories);
    }

    public function test_can_forward_to_recipe_form_for_editing(): void
    {
        $user = User::factory()->create();

        Http::fake([
            'https://www.bettybossi.ch/rezept/kartoffelgratin' => Http::response($this->getCompleteRecipeHtml(), 200),
            'https://example.com/kartoffelgratin.jpg' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        Livewire::actingAs($user)
            ->test(RecipeSearch::class)
            ->set('query', 'https://www.bettybossi.ch/rezept/kartoffelgratin')
            ->call('search')
            ->call('editBeforeSave')
            ->assertRedirect(route('recipes.create'));

        $this->assertTrue(session()->has('imported_web_recipe'));
    }

    public function test_displays_external_search_link_for_swiss_sites(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(RecipeSearch::class)
            ->set('site', 'bettybossi')
            ->set('query', 'Wähe')
            ->call('search')
            ->assertSee('Direktsuche auf Betty Bossi')
            ->assertSee('Auf Betty Bossi nach „Wähe“ suchen');
    }
}

