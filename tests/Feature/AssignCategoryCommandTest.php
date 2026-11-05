<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssignCategoryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigns_category_to_matching_recipes_by_ingredient_or_title(): void
    {
        $user = User::factory()->create();

        // Recipe 1: Kartoffel in title
        $recipe1 = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Kartoffelgratin Spezial',
            'instructions' => 'Backen',
            'is_shared' => true,
        ]);

        // Recipe 2: Kartoffel in ingredient
        $recipe2 = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Rindsschmorbraten mit Beilage',
            'instructions' => 'Schmoren',
            'is_shared' => true,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe2->id,
            'name' => '500g Kartoffeln, festkochend',
            'order' => 1,
        ]);

        // Recipe 3: Completely unrelated
        $recipe3 = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Schokoladenkuchen',
            'instructions' => 'Backen',
            'is_shared' => true,
        ]);

        $this->artisan('recipes:assign-category', ['category' => 'Kartoffel'])
            ->expectsOutputToContain("Gefundene Rezepte mit 'Kartoffel': 2")
            ->assertSuccessful();

        $category = Category::where('name', 'Kartoffel')->first();
        $this->assertNotNull($category);
        $this->assertTrue($recipe1->fresh()->categories->contains($category));
        $this->assertTrue($recipe2->fresh()->categories->contains($category));
        $this->assertFalse($recipe3->fresh()->categories->contains($category));
    }
}
