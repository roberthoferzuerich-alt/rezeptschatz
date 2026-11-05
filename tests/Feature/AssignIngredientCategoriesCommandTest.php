<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssignIngredientCategoriesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_modify_database(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Rindsgeschnetzeltes Stroganoff',
            'instructions' => 'Kochen',
            'is_shared' => true,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'name' => '500g Rindfleisch',
            'order' => 1,
        ]);

        $this->artisan('recipes:assign-ingredient-categories', ['--dry-run' => true])
            ->expectsOutputToContain('DRY-RUN AKTIV')
            ->assertSuccessful();

        $this->assertDatabaseMissing('categories', ['name' => 'Fleisch']);
        $this->assertCount(0, $recipe->fresh()->categories);
    }

    public function test_assigns_multiple_ingredient_categories_accurately(): void
    {
        $user = User::factory()->create();

        // Recipe 1: Meat & Pasta (Spaghetti Bolognese)
        $recipe1 = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Spaghetti Bolognese',
            'instructions' => 'Kochen',
            'is_shared' => true,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe1->id,
            'name' => '400g Rinderhackfleisch',
            'order' => 1,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe1->id,
            'name' => '500g Spaghetti',
            'order' => 2,
        ]);

        // Recipe 2: Fish & Rice (Lachs mit Basmatireis)
        $recipe2 = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Gebratenes Lachsfilet mit Basmatireis',
            'instructions' => 'Braten',
            'is_shared' => true,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe2->id,
            'name' => '2 Lachsfilets',
            'order' => 1,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe2->id,
            'name' => '200g Basmati-Reis',
            'order' => 2,
        ]);

        // Recipe 3: Chicken & Vegetables (Pouletgeschnetzeltes mit Zucchetti)
        $recipe3 = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Pouletgeschnetzeltes mit Zucchetti',
            'instructions' => 'Dünsten',
            'is_shared' => true,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe3->id,
            'name' => '400g Pouletbrüstli geschnetzelt',
            'order' => 1,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe3->id,
            'name' => '2 mittelgrosse Zucchetti',
            'order' => 2,
        ]);

        // Run the command
        $this->artisan('recipes:assign-ingredient-categories')
            ->assertSuccessful();

        // Recipe 1 should have Fleisch and Teigwaren
        $r1Cats = $recipe1->fresh()->categories->pluck('name')->toArray();
        $this->assertContains('Fleisch', $r1Cats);
        $this->assertContains('Teigwaren', $r1Cats);

        // Recipe 2 should have Fisch, Lachs and Reis
        $r2Cats = $recipe2->fresh()->categories->pluck('name')->toArray();
        $this->assertContains('Fisch', $r2Cats);
        $this->assertContains('Lachs', $r2Cats);
        $this->assertContains('Reis', $r2Cats);

        // Recipe 3 should have Geflügel, Pouletbrust and Gemüse
        $r3Cats = $recipe3->fresh()->categories->pluck('name')->toArray();
        $this->assertContains('Geflügel', $r3Cats);
        $this->assertContains('Pouletbrust', $r3Cats);
        $this->assertContains('Gemüse', $r3Cats);
    }

    public function test_can_filter_by_single_category(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Pouletsalat',
            'instructions' => 'Mischen',
            'is_shared' => true,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'name' => '300g Pouletbrust',
            'order' => 1,
        ]);

        $this->artisan('recipes:assign-ingredient-categories', ['--category' => 'Geflügel'])
            ->assertSuccessful();

        $this->assertDatabaseHas('categories', ['name' => 'Geflügel']);
        // Pouletbrust shouldn't be processed when only Geflügel was asked
        $this->assertDatabaseMissing('categories', ['name' => 'Pouletbrust']);
    }
}
