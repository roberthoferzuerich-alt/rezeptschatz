<?php

namespace Tests\Feature;

use App\Livewire\ChatGptCooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatGptCookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/recipes/chatgpt');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_chatgpt_cooking_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/recipes/chatgpt');
        $response->assertStatus(200);
        $response->assertSee('Kühlschrank fotografieren');
        $response->assertSee('KI-API-Schlüssel');
    }

    public function test_can_import_chatgpt_response_text(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $sampleText = <<<TEXT
Titel: Mediterrane Hähnchenpfanne mit Zucchini
Portionen: 4
Vorbereitungszeit: 10
Kochzeit: 20
Beschreibung: Schnelle und aromatische Feierabend-Pfanne.

Zutaten:
- 500 g Hähnchenbrustfilet
- 2 Stk Zucchini
- 1 EL Olivenöl
- Salz und Pfeffer

Zubereitung:
1. Das Hähnchenbrustfilet in mundgerechte Stücke schneiden.
2. Olivenöl in der Pfanne erhitzen und Fleisch anbraten.
3. Zucchini beigeben und fertig braten.
TEXT;

        Livewire::actingAs($user)
            ->test(ChatGptCooking::class)
            ->set('pastedResponse', $sampleText)
            ->call('importPastedRecipe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'user_id' => $user->id,
            'title' => 'Mediterrane Hähnchenpfanne mit Zucchini',
            'portions' => 4,
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 20,
        ]);

        $this->assertDatabaseHas('recipe_ingredients', [
            'name' => 'Hähnchenbrustfilet',
            'amount_numeric' => 500,
            'unit' => 'g',
        ]);

        $this->assertDatabaseHas('recipe_steps', [
            'step_number' => 1,
            'instruction' => 'Das Hähnchenbrustfilet in mundgerechte Stücke schneiden.',
        ]);
    }

    public function test_can_save_generated_recipe_with_nutrition(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $generated = [
            'title' => 'Cremiges Pilzrisotto',
            'description' => 'Feines Risotto mit frischen Champignons.',
            'portions' => 3,
            'prep_time_minutes' => 10,
            'cook_time_minutes' => 25,
            'calories' => 520,
            'carbohydrates' => 68.5,
            'fat' => 16.0,
            'protein' => 12.5,
            'ingredients' => [
                ['name' => 'Risottoreis', 'amount_numeric' => 250, 'unit' => 'g', 'amount_text' => '250 g'],
                ['name' => 'Champignons', 'amount_numeric' => 200, 'unit' => 'g', 'amount_text' => '200 g'],
            ],
            'steps' => [
                ['instruction' => 'Zwiebeln andünsten, Reis zugeben.', 'step_number' => 1],
                ['instruction' => 'Mit Bouillon ablöschen.', 'step_number' => 2],
            ],
        ];

        Livewire::actingAs($user)
            ->test(ChatGptCooking::class)
            ->set('generatedRecipe', $generated)
            ->call('saveGeneratedRecipe')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'user_id' => $user->id,
            'title' => 'Cremiges Pilzrisotto',
            'portions' => 3,
            'calories' => 520,
            'carbohydrates' => 68.5,
            'fat' => 16.0,
            'protein' => 12.5,
        ]);
    }
}

