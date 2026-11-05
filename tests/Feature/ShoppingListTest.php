<?php

namespace Tests\Feature;

use App\Livewire\ShoppingList;
use App\Models\Recipe;
use App\Models\ShoppingListItem;
use App\Models\User;
use App\Services\ShoppingListService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/shopping-list');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_empty_shopping_list(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/shopping-list');
        $response->assertStatus(200);
        $response->assertSee('Einkaufsliste');
        $response->assertSee('Ihre Einkaufsliste ist leer');
    }

    public function test_user_can_add_custom_item(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ShoppingList::class)
            ->set('newItemName', 'Bio-Vollmilch')
            ->set('newItemAmount', 2)
            ->set('newItemUnit', 'l')
            ->call('addItem')
            ->assertSee('Bio-Vollmilch')
            ->assertSee('2 l');

        $this->assertDatabaseHas('shopping_list_items', [
            'user_id' => $user->id,
            'name' => 'Bio-Vollmilch',
            'amount_numeric' => 2,
            'unit' => 'l',
            'is_completed' => false,
        ]);
    }

    public function test_smart_merging_combines_same_ingredients_with_matching_units(): void
    {
        $user = User::factory()->create();
        $service = app(ShoppingListService::class);

        // Erste Zutat: 500 g Kartoffeln
        $service->addIngredient($user, [
            'name' => 'Festkochende Kartoffeln',
            'amount_numeric' => 500,
            'unit' => 'g',
        ]);

        // Zweite Zutat: 300 g Kartoffeln (gleicher Name, gleiche Einheit)
        $service->addIngredient($user, [
            'name' => 'Festkochende Kartoffeln',
            'amount_numeric' => 300,
            'unit' => 'g',
        ]);

        $this->assertDatabaseCount('shopping_list_items', 1);
        $item = ShoppingListItem::where('user_id', $user->id)->first();
        $this->assertEquals(800, $item->amount_numeric);
        $this->assertEquals('g', $item->unit);
    }

    public function test_user_can_toggle_item_completed_status(): void
    {
        $user = User::factory()->create();
        $item = ShoppingListItem::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Butter',
            'is_completed' => false,
        ]);

        Livewire::actingAs($user)
            ->test(ShoppingList::class)
            ->call('toggleItem', $item->id);

        $this->assertTrue($item->fresh()->is_completed);

        Livewire::actingAs($user)
            ->test(ShoppingList::class)
            ->call('toggleItem', $item->id);

        $this->assertFalse($item->fresh()->is_completed);
    }

    public function test_user_can_add_recipe_ingredients_scaled_to_portions(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Rüeblisuppe',
            'portions' => 2,
            'is_shared' => true,
        ]);

        $recipe->ingredients()->create([
            'name' => 'Rüebli',
            'amount_numeric' => 400,
            'unit' => 'g',
            'sort_order' => 1,
        ]);

        // Für 4 Portionen hinzufügen (doppelte Menge = 800g)
        $service = app(ShoppingListService::class);
        $count = $service->addRecipeIngredients($user, $recipe, 4);

        $this->assertEquals(1, $count);
        $item = ShoppingListItem::where('user_id', $user->id)->first();
        $this->assertEquals('Rüebli', $item->name);
        $this->assertEquals(800, $item->amount_numeric);
        $this->assertEquals('g', $item->unit);
        $this->assertEquals('Gemüse & Früchte', $item->category);
    }

    public function test_user_can_clear_completed_items(): void
    {
        $user = User::factory()->create();
        ShoppingListItem::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Offener Artikel',
            'is_completed' => false,
        ]);
        ShoppingListItem::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => 'Erledigter Artikel',
            'is_completed' => true,
        ]);

        Livewire::actingAs($user)
            ->test(ShoppingList::class)
            ->call('clearCompleted');

        $this->assertDatabaseCount('shopping_list_items', 1);
        $this->assertDatabaseHas('shopping_list_items', ['name' => 'Offener Artikel']);
        $this->assertDatabaseMissing('shopping_list_items', ['name' => 'Erledigter Artikel']);
    }
}
