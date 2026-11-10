<?php

namespace Tests\Feature;

use App\Livewire\RecipeNutritionCalculator;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class RecipeNutritionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_commit_saves_nutrition_to_internal_recipe(): void
    {
        $user = User::factory()->create();

        $recipe = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Herzhafte Hackfleisch-Pfanne',
            'portions' => 4,
            'server_version' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(RecipeNutritionCalculator::class)
            ->set('recipeData', [
                'type' => 'internal',
                'id' => $recipe->id,
                'title' => $recipe->title,
                'portions' => 4,
                'ingredients' => [],
                'source' => 'Test',
            ])
            ->set('calories', 405)
            ->set('carbohydrates', 24.5)
            ->set('fat', 27.3)
            ->set('protein', 16)
            ->set('portions', 4)
            ->call('saveNutrition')
            ->assertHasNoErrors()
            ->assertSet('errorMessage', null)
            ->assertRedirect(route('recipes.index'));

        $recipe->refresh();
        $this->assertEquals(405, $recipe->calories);
        $this->assertEquals(24.5, (float) $recipe->carbohydrates);
        $this->assertEquals(27.3, (float) $recipe->fat);
        $this->assertEquals(16.0, (float) $recipe->protein);
    }
}

