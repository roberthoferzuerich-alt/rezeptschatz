<?php

namespace Tests\Feature;

use App\Livewire\RecipeList;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class RecipeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function makeRecipe(User $user, string $title, bool $shared): Recipe
    {
        return Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => $title,
            'portions' => 4,
            'is_shared' => $shared,
            'server_version' => 1,
        ]);
    }

    public function test_overall_view_hides_own_unshared_recipes(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->makeRecipe($me, 'Mein privates Rezept', false);
        $this->makeRecipe($me, 'Mein freigegebenes Rezept', true);
        $this->makeRecipe($other, 'Fremdes freigegebenes Rezept', true);
        $this->makeRecipe($other, 'Fremdes privates Rezept', false);

        Livewire::actingAs($me)
            ->test(RecipeList::class)
            ->assertSee('Mein freigegebenes Rezept')
            ->assertSee('Fremdes freigegebenes Rezept')
            ->assertDontSee('Mein privates Rezept')
            ->assertDontSee('Fremdes privates Rezept');
    }

    public function test_mine_view_still_shows_own_unshared_recipes(): void
    {
        $me = User::factory()->create();
        $other = User::factory()->create();

        $this->makeRecipe($me, 'Mein privates Rezept', false);
        $this->makeRecipe($other, 'Fremdes freigegebenes Rezept', true);

        Livewire::actingAs($me)
            ->test(RecipeList::class)
            ->set('authorFilter', 'mine')
            ->assertSee('Mein privates Rezept')
            ->assertDontSee('Fremdes freigegebenes Rezept');
    }

    public function test_owner_can_toggle_recipe_sharing_in_detail_view(): void
    {
        $user = User::factory()->create();
        $recipe = $this->makeRecipe($user, 'Testgericht', false);

        Livewire::actingAs($user)
            ->test(\App\Livewire\RecipeDetail::class, ['id' => $recipe->id])
            ->assertSee('Privat')
            ->call('toggleShared')
            ->assertSee('Freigegeben');

        $recipe->refresh();
        $this->assertTrue($recipe->is_shared);

        // Erneut umschalten
        Livewire::actingAs($user)
            ->test(\App\Livewire\RecipeDetail::class, ['id' => $recipe->id])
            ->call('toggleShared');

        $recipe->refresh();
        $this->assertFalse($recipe->is_shared);
    }

    public function test_recipe_form_saves_sharing_status(): void
    {
        $user = User::factory()->create();
        $recipe = $this->makeRecipe($user, 'Testgericht', true);

        Livewire::actingAs($user)
            ->test(\App\Livewire\RecipeForm::class, ['id' => $recipe->id])
            ->assertSet('is_shared', true)
            ->set('is_shared', false)
            ->call('save');

        $recipe->refresh();
        $this->assertFalse($recipe->is_shared);
    }
}

