<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\User;
use App\Services\RecipeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_push_creates_recipe_aggregate(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Android-App')->plainTextToken;

        $mutationId = (string) Str::uuid();
        $recipeId = (string) Str::uuid();
        $ingredientId = (string) Str::uuid();
        $stepId = (string) Str::uuid();

        $payload = [
            'mutations' => [
                [
                    'mutation_id' => $mutationId,
                    'entity_type' => 'recipe',
                    'entity_id' => $recipeId,
                    'action' => 'create',
                    'base_version' => 0,
                    'payload' => [
                        'title' => 'Offline Erstelltes Gericht',
                        'description' => 'Auf dem Smartphone ohne Internet erfasst.',
                        'portions' => 4,
                        'prep_time_minutes' => 15,
                        'cook_time_minutes' => 20,
                        'is_shared' => true,
                        'ingredients' => [
                            [
                                'id' => $ingredientId,
                                'sort_order' => 0,
                                'name' => 'Basmatireis',
                                'amount_numeric' => 250,
                                'unit' => 'g',
                            ],
                        ],
                        'steps' => [
                            [
                                'id' => $stepId,
                                'step_number' => 1,
                                'instruction' => 'Reis waschen und kochen.',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/sync/push', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'processed_mutations' => [
                    [
                        'mutation_id' => $mutationId,
                        'status' => 'applied',
                        'entity_id' => $recipeId,
                        'server_version' => 1,
                    ],
                ],
                'conflicts' => [],
            ],
        ]);

        $this->assertDatabaseHas('recipes', ['id' => $recipeId, 'title' => 'Offline Erstelltes Gericht']);
        $this->assertDatabaseHas('recipe_ingredients', ['id' => $ingredientId, 'name' => 'Basmatireis']);
        $this->assertDatabaseHas('recipe_steps', ['id' => $stepId, 'instruction' => 'Reis waschen und kochen.']);
    }

    public function test_sync_push_is_idempotent(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Android-App')->plainTextToken;

        $mutationId = (string) Str::uuid();
        $recipeId = (string) Str::uuid();

        $payload = [
            'mutations' => [
                [
                    'mutation_id' => $mutationId,
                    'entity_type' => 'recipe',
                    'entity_id' => $recipeId,
                    'action' => 'create',
                    'base_version' => 0,
                    'payload' => [
                        'title' => 'Idempotenz-Test-Rezept',
                        'portions' => 2,
                    ],
                ],
            ],
        ];

        // Erste Anfrage
        $response1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/sync/push', $payload);
        $response1->assertStatus(200);

        // Zweite identische Anfrage (z. B. nach Netzwerk-Timeout)
        $response2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/sync/push', $payload);
        $response2->assertStatus(200);

        // Genau 1 Rezept in der Datenbank
        $this->assertEquals(1, Recipe::where('title', 'Idempotenz-Test-Rezept')->count());
    }

    public function test_sync_push_detects_version_conflict(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Android-App')->plainTextToken;

        $recipeService = new RecipeService();
        $recipe = $recipeService->createRecipe($user, [
            'title' => 'Original-Titel auf dem Server',
            'portions' => 4,
        ]);

        // Server-Version wird im Web auf 2 erhöht
        $recipeService->updateRecipe($recipe, [
            'title' => 'Geändert im Web-Browser',
        ]);
        $recipe->refresh();
        $this->assertEquals(2, $recipe->server_version);

        // Smartphone sendet Update basierend auf alter Version 1
        $mutationId = (string) Str::uuid();
        $payload = [
            'mutations' => [
                [
                    'mutation_id' => $mutationId,
                    'entity_type' => 'recipe',
                    'entity_id' => $recipe->id,
                    'action' => 'update',
                    'base_version' => 1, // Veraltet!
                    'payload' => [
                        'title' => 'Gleichzeitige Änderung am Smartphone',
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/sync/push', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
            'data' => [
                'processed_mutations' => [],
                'conflicts' => [
                    [
                        'mutation_id' => $mutationId,
                        'entity_id' => $recipe->id,
                        'conflict_type' => 'version_mismatch',
                        'base_version' => 1,
                        'server_version' => 2,
                    ],
                ],
            ],
        ]);
    }

    public function test_sync_pull_returns_recipes_and_soft_deletes(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $token = $user->createToken('Android-App')->plainTextToken;

        $recipeService = new RecipeService();
        $recipe1 = $recipeService->createRecipe($user, ['title' => 'Aktiv Rezept 1']);
        $recipe2 = $recipeService->createRecipe($user, ['title' => 'Zu löschendes Rezept 2']);

        // Rezept 2 wird gelöscht
        $recipeService->deleteRecipe($recipe2);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/sync/pull');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'recipes',
                'categories',
                'next_cursor',
                'has_more',
                'server_time',
            ],
        ]);

        $recipes = $response->json('data.recipes');
        $this->assertCount(2, $recipes);

        // Prüfen, dass das gelöschte Rezept mit deleted_at ausgeliefert wird
        $deletedItem = collect($recipes)->firstWhere('id', $recipe2->id);
        $this->assertNotNull($deletedItem['deleted_at']);
    }
}

