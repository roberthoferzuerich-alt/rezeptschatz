<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/recipes');
        $response->assertRedirect('/login');
    }

    public function test_user_can_view_login_page(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Rezeptbuch');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'robert.hofer.zuerich@bluewin.ch',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'robert.hofer.zuerich@bluewin.ch',
            'password' => 'password',
        ]);

        $response->assertRedirect('/recipes');
        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_view_recipes_list(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Test-Nudelpfanne',
            'portions' => 4,
            'server_version' => 1,
            'is_shared' => true,
        ]);

        $response = $this->actingAs($user)->get('/recipes');
        $response->assertStatus(200);
        $response->assertSee('Test-Nudelpfanne');
    }

    public function test_family_member_can_view_shared_recipe(): void
    {
        $father = User::factory()->create(['name' => 'Robert', 'role' => 'admin']);
        $daughter = User::factory()->create(['name' => 'Tochter', 'role' => 'member']);

        $recipe = Recipe::create([
            'user_id' => $father->id,
            'title' => 'Papas Spinat-Pfanne',
            'portions' => 4,
            'server_version' => 1,
            'is_shared' => true,
        ]);

        $response = $this->actingAs($daughter)->get('/recipes');
        $response->assertStatus(200);
        $response->assertSee('Papas Spinat-Pfanne');

        $detailResponse = $this->actingAs($daughter)->get("/recipes/{$recipe->id}");
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Papas Spinat-Pfanne');
        $detailResponse->assertSee('Als eigenes Rezept kopieren');
    }

    public function test_daughter_can_copy_recipe_as_her_own(): void
    {
        $father = User::factory()->create(['name' => 'Robert', 'role' => 'admin']);
        $daughter = User::factory()->create(['name' => 'Tochter', 'role' => 'member']);

        $service = new RecipeService();
        $source = $service->createRecipe($father, [
            'title' => 'Papas Original-Rösti',
            'portions' => 4,
            'ingredients' => [
                ['name' => 'Kartoffeln', 'amount_numeric' => 800, 'unit' => 'g'],
            ],
            'steps' => [
                ['instruction' => 'Raffeln und braten.'],
            ],
        ]);

        $copy = $service->copyRecipeForUser($daughter, $source);

        $this->assertEquals($daughter->id, $copy->user_id);
        $this->assertEquals('Papas Original-Rösti (Kopie)', $copy->title);
        $this->assertCount(1, $copy->ingredients);
        $this->assertCount(1, $copy->steps);
    }

    public function test_favorites_are_personal_per_user(): void
    {
        $father = User::factory()->create(['name' => 'Robert', 'role' => 'admin']);
        $daughter = User::factory()->create(['name' => 'Tochter', 'role' => 'member']);

        $service = new RecipeService();
        $recipe = $service->createRecipe($father, [
            'title' => 'Familien-Kuchen',
            'portions' => 8,
        ]);

        $service->toggleFavorite($father, $recipe);

        $this->assertTrue($recipe->isFavoritedBy($father));
        $this->assertFalse($recipe->isFavoritedBy($daughter));

        $service->toggleFavorite($daughter, $recipe);

        $this->assertTrue($recipe->isFavoritedBy($daughter));
    }

    public function test_policy_enforces_permissions(): void
    {
        $father = User::factory()->create(['name' => 'Robert', 'role' => 'admin']);
        $daughter = User::factory()->create(['name' => 'Tochter', 'role' => 'member']);

        $fatherRecipe = Recipe::create([
            'user_id' => $father->id,
            'title' => 'Papas Geheimrezept',
            'portions' => 4,
            'server_version' => 1,
            'is_shared' => true,
        ]);

        // Tochter darf Papas Rezept sehen, aber nicht bearbeiten oder löschen
        $this->assertTrue($daughter->can('view', $fatherRecipe));
        $this->assertFalse($daughter->can('update', $fatherRecipe));
        $this->assertFalse($daughter->can('delete', $fatherRecipe));

        // Vater (Admin) darf bearbeiten und löschen
        $this->assertTrue($father->can('update', $fatherRecipe));
        $this->assertTrue($father->can('delete', $fatherRecipe));

        // Nur Admin darf Kategorien verwalten
        $this->assertTrue($father->can('create', Category::class));
        $this->assertFalse($daughter->can('create', Category::class));
    }

    public function test_user_can_toggle_between_grid_and_list_view(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $recipe = Recipe::create([
            'user_id' => $user->id,
            'title' => 'Spinat-Nudelpfanne',
            'portions' => 4,
            'server_version' => 1,
            'is_shared' => true,
        ]);

        $recipe->ingredients()->create([
            'name' => 'TK-Blattspinat',
            'amount_numeric' => 400,
            'unit' => 'g',
            'sort_order' => 1,
        ]);

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\RecipeList::class)
            ->assertSet('viewMode', 'grid')
            ->assertSee('format_list_bulleted')
            ->call('toggleViewMode')
            ->assertSet('viewMode', 'list')
            ->assertSee('grid_view')
            ->assertSee('Spinat-Nudelpfanne')
            ->assertSee('400 g TK-Blattspinat');
    }
}
