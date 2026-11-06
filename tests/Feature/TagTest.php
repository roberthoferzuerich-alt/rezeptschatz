<?php

namespace Tests\Feature;

use App\Livewire\RecipeForm;
use App\Livewire\RecipeList;
use App\Livewire\TagManager;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/tags');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_tags_page(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/tags');
        $response->assertStatus(200);
        $response->assertSee('Schlagwörter verwalten');
    }

    public function test_admin_can_create_update_and_delete_tag(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($user)
            ->test(TagManager::class)
            ->set('name', 'Schnell & Einfach')
            ->set('color', '#10b981')
            ->call('create')
            ->assertHasNoErrors()
            ->assertSee('Schnell & Einfach');

        $tag = Tag::where('name', 'Schnell & Einfach')->first();
        $this->assertNotNull($tag);
        $this->assertEquals('#10b981', $tag->color);

        // Bearbeiten
        Livewire::actingAs($user)
            ->test(TagManager::class)
            ->call('startEdit', $tag->id)
            ->set('editName', 'Blitzschnell')
            ->set('editColor', '#f59e0b')
            ->call('update')
            ->assertHasNoErrors();

        $tag->refresh();
        $this->assertEquals('Blitzschnell', $tag->name);
        $this->assertEquals('#f59e0b', $tag->color);

        // Löschen
        Livewire::actingAs($user)
            ->test(TagManager::class)
            ->call('delete', $tag->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('tags', ['id' => $tag->id]);
    }

    public function test_recipe_can_be_filtered_by_tag_on_recipe_list(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $tagA = Tag::create([
            'user_id' => $user->id,
            'name' => 'Sommergericht',
            'color' => '#ec4899',
        ]);

        $tagB = Tag::create([
            'user_id' => $user->id,
            'name' => 'Wintergericht',
            'color' => '#8b5cf6',
        ]);

        $recipeA = Recipe::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Griechischer Salat',
            'portions' => 2,
        ]);
        $recipeA->tags()->attach($tagA->id);

        $recipeB = Recipe::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Käsefondue',
            'portions' => 4,
        ]);
        $recipeB->tags()->attach($tagB->id);

        Livewire::actingAs($user)
            ->test(RecipeList::class)
            ->set('selectedTag', $tagA->id)
            ->assertSee('Griechischer Salat')
            ->assertDontSee('Käsefondue');

        Livewire::actingAs($user)
            ->test(RecipeList::class)
            ->set('selectedTag', $tagB->id)
            ->assertSee('Käsefondue')
            ->assertDontSee('Griechischer Salat');
    }

    public function test_recipe_form_attaches_tags(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $tag = Tag::create([
            'user_id' => $user->id,
            'name' => 'Klassiker',
            'color' => '#6366f1',
        ]);

        Livewire::actingAs($user)
            ->test(RecipeForm::class)
            ->set('title', 'Zürcher Geschnetzeltes')
            ->set('portions', 4)
            ->set('selectedTagIds', [$tag->id])
            ->call('save')
            ->assertHasNoErrors();

        $recipe = Recipe::where('title', 'Zürcher Geschnetzeltes')->first();
        $this->assertNotNull($recipe);
        $this->assertTrue($recipe->tags->contains('id', $tag->id));
    }
}
