<?php

namespace Tests\Feature;

use App\Livewire\MealPlanner;
use App\Models\MealPlan;
use App\Models\Recipe;
use App\Models\ShoppingListItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class MealPlannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/meal-planner');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_meal_planner(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/meal-planner');
        $response->assertStatus(200);
        $response->assertSee('Wochen-Menüplaner');
        $response->assertSee('Aktuelle Woche');
    }

    public function test_user_can_navigate_weeks(): void
    {
        $user = User::factory()->create();
        $thisMonday = Carbon::now()->startOfWeek()->format('Y-m-d');
        $nextMonday = Carbon::now()->startOfWeek()->addWeek()->format('Y-m-d');

        Livewire::actingAs($user)
            ->test(MealPlanner::class)
            ->assertSet('currentWeekStart', $thisMonday)
            ->call('nextWeek')
            ->assertSet('currentWeekStart', $nextMonday)
            ->call('previousWeek')
            ->assertSet('currentWeekStart', $thisMonday);
    }

    public function test_user_can_assign_recipe_to_day(): void
    {
        $user = User::factory()->create();
        $recipe = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Pasta Carbonara',
            'portions' => 4,
            'is_shared' => true,
        ]);

        $today = Carbon::now()->format('Y-m-d');

        Livewire::actingAs($user)
            ->test(MealPlanner::class)
            ->call('openAddModal', $today, 'dinner')
            ->assertSet('showRecipeModal', true)
            ->call('assignRecipe', $recipe->id)
            ->assertSet('showRecipeModal', false);

        $this->assertDatabaseHas('meal_plans', [
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'date' => $today,
            'meal_type' => 'dinner',
            'portions' => 4,
        ]);
    }

    public function test_user_can_assign_custom_meal(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now()->format('Y-m-d');

        Livewire::actingAs($user)
            ->test(MealPlanner::class)
            ->call('openAddModal', $today, 'lunch')
            ->set('customTitle', 'Pizza beim Italiener')
            ->call('assignCustomMeal');

        $this->assertDatabaseHas('meal_plans', [
            'user_id' => $user->id,
            'custom_title' => 'Pizza beim Italiener',
            'date' => $today,
            'meal_type' => 'lunch',
        ]);
    }

    public function test_user_can_send_week_ingredients_to_shopping_list(): void
    {
        $user = User::factory()->create();
        $today = Carbon::now()->format('Y-m-d');

        $recipe = Recipe::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => 'Kartoffelgratin',
            'portions' => 4,
            'is_shared' => true,
        ]);

        $recipe->ingredients()->create([
            'name' => 'Kartoffeln',
            'amount_numeric' => 800,
            'unit' => 'g',
            'sort_order' => 1,
        ]);

        MealPlan::create([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'recipe_id' => $recipe->id,
            'date' => $today,
            'meal_type' => 'dinner',
            'portions' => 4,
        ]);

        Livewire::actingAs($user)
            ->test(MealPlanner::class)
            ->call('sendWeekToShoppingList');

        $this->assertDatabaseHas('shopping_list_items', [
            'user_id' => $user->id,
            'name' => 'Kartoffeln',
            'amount_numeric' => 800,
            'unit' => 'g',
        ]);
    }
}
