<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\SyncMutationLog;
use App\Services\CategoryService;
use App\Services\RecipeService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SyncController extends Controller
{
    /**
     * Empfängt Mutations-Batches von mobilen Clients (Push).
     */
    public function push(Request $request, RecipeService $recipeService, CategoryService $categoryService): JsonResponse
    {
        $request->validate([
            'mutations' => ['required', 'array'],
            'mutations.*.mutation_id' => ['required', 'uuid'],
            'mutations.*.entity_type' => ['required', 'string', 'in:recipe,category'],
            'mutations.*.entity_id' => ['required', 'uuid'],
            'mutations.*.action' => ['required', 'string', 'in:create,update,delete'],
            'mutations.*.base_version' => ['required', 'integer'],
            'mutations.*.payload' => ['nullable', 'array'],
        ]);

        $user = $request->user();
        $processed = [];
        $conflicts = [];

        foreach ($request->input('mutations') as $mutation) {
            $mutationId = $mutation['mutation_id'];
            $entityType = $mutation['entity_type'];
            $entityId = $mutation['entity_id'];
            $action = $mutation['action'];
            $baseVersion = (int) $mutation['base_version'];
            $payload = $mutation['payload'] ?? [];

            // 1. Idempotenz-Prüfung
            $existingLog = SyncMutationLog::where('user_id', $user->id)
                ->where('mutation_id', $mutationId)
                ->first();

            if ($existingLog) {
                $processed[] = $existingLog->response_payload;
                continue;
            }

            // 2. Verarbeitung nach Entitätstyp
            if ($entityType === 'recipe') {
                $result = $this->handleRecipeMutation($user, $mutationId, $entityId, $action, $baseVersion, $payload, $recipeService);
            } else {
                $result = $this->handleCategoryMutation($user, $mutationId, $entityId, $action, $baseVersion, $payload, $categoryService);
            }

            if (isset($result['conflict'])) {
                $conflicts[] = $result['conflict'];
            } else {
                $processed[] = $result['success'];
            }
        }

        return response()->json([
            'success' => count($conflicts) === 0,
            'data' => [
                'processed_mutations' => $processed,
                'conflicts' => $conflicts,
            ],
        ]);
    }

    /**
     * Liefert alle Änderungen seit dem übergebenen Cursor an den Client (Pull).
     */
    public function pull(Request $request): JsonResponse
    {
        $user = $request->user();
        $cursor = $request->query('cursor');
        $limit = min((int) $request->query('limit', 50), 100);

        $cursorTime = $cursor ? Carbon::parse($cursor) : Carbon::createFromTimestamp(0);

        // Rezepte abfragen (inklusive Soft-Deletes)
        $recipeQuery = Recipe::withTrashed()
            ->with(['categories', 'tags', 'ingredients', 'steps', 'user', 'favoritedBy'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_shared', true);
            })
            ->where('updated_at', '>', $cursorTime)
            ->orderBy('updated_at', 'asc');

        $recipes = $recipeQuery->limit($limit + 1)->get();

        $hasMore = false;
        if ($recipes->count() > $limit) {
            $hasMore = true;
            $recipes->pop();
        }

        // Kategorien abfragen
        $categories = Category::withTrashed()
            ->where('updated_at', '>', $cursorTime)
            ->orderBy('updated_at', 'asc')
            ->get();

        // Neuesten Zeitstempel als nächsten Cursor ermitteln
        $latestRecipeUpdate = $recipes->max('updated_at');
        $latestCategoryUpdate = $categories->max('updated_at');

        $maxTimestamp = null;
        if ($latestRecipeUpdate && $latestCategoryUpdate) {
            $maxTimestamp = $latestRecipeUpdate->greaterThan($latestCategoryUpdate) ? $latestRecipeUpdate : $latestCategoryUpdate;
        } elseif ($latestRecipeUpdate) {
            $maxTimestamp = $latestRecipeUpdate;
        } elseif ($latestCategoryUpdate) {
            $maxTimestamp = $latestCategoryUpdate;
        }

        $nextCursor = $maxTimestamp ? $maxTimestamp->toISOString() : ($cursor ?: now()->toISOString());

        return response()->json([
            'success' => true,
            'data' => [
                'recipes' => RecipeResource::collection($recipes),
                'categories' => CategoryResource::collection($categories),
                'next_cursor' => $nextCursor,
                'has_more' => $hasMore,
                'server_time' => now()->toISOString(),
            ],
        ]);
    }

    protected function handleRecipeMutation($user, string $mutationId, string $entityId, string $action, int $baseVersion, array $payload, RecipeService $recipeService): array
    {
        return DB::transaction(function () use ($user, $mutationId, $entityId, $action, $baseVersion, $payload, $recipeService) {
            if ($action === 'create') {
                $existing = Recipe::withTrashed()->find($entityId);
                if ($existing) {
                    return [
                        'conflict' => [
                            'mutation_id' => $mutationId,
                            'entity_id' => $entityId,
                            'conflict_type' => 'already_exists',
                            'server_version' => $existing->server_version,
                            'server_recipe' => new RecipeResource($existing->load(['categories', 'ingredients', 'steps'])),
                        ],
                    ];
                }

                $payload['id'] = $entityId;
                $recipe = $recipeService->createRecipe($user, $payload);

                $responsePayload = [
                    'mutation_id' => $mutationId,
                    'status' => 'applied',
                    'entity_id' => $recipe->id,
                    'server_version' => $recipe->server_version,
                ];

                SyncMutationLog::create([
                    'user_id' => $user->id,
                    'mutation_id' => $mutationId,
                    'entity_type' => 'recipe',
                    'entity_id' => $recipe->id,
                    'action' => 'create',
                    'applied_version' => $recipe->server_version,
                    'response_payload' => $responsePayload,
                ]);

                return ['success' => $responsePayload];
            }

            if ($action === 'update') {
                $recipe = Recipe::withTrashed()->find($entityId);

                if (!$recipe) {
                    return [
                        'conflict' => [
                            'mutation_id' => $mutationId,
                            'entity_id' => $entityId,
                            'conflict_type' => 'not_found',
                        ],
                    ];
                }

                if (!$user->can('update', $recipe)) {
                    return [
                        'conflict' => [
                            'mutation_id' => $mutationId,
                            'entity_id' => $entityId,
                            'conflict_type' => 'permission_denied',
                        ],
                    ];
                }

                // Versionskonflikt prüfen
                if ($recipe->server_version > $baseVersion) {
                    return [
                        'conflict' => [
                            'mutation_id' => $mutationId,
                            'entity_id' => $entityId,
                            'conflict_type' => 'version_mismatch',
                            'base_version' => $baseVersion,
                            'server_version' => $recipe->server_version,
                            'server_recipe' => new RecipeResource($recipe->load(['categories', 'ingredients', 'steps'])),
                        ],
                    ];
                }

                $updated = $recipeService->updateRecipe($recipe, $payload);

                $responsePayload = [
                    'mutation_id' => $mutationId,
                    'status' => 'applied',
                    'entity_id' => $updated->id,
                    'server_version' => $updated->server_version,
                ];

                SyncMutationLog::create([
                    'user_id' => $user->id,
                    'mutation_id' => $mutationId,
                    'entity_type' => 'recipe',
                    'entity_id' => $updated->id,
                    'action' => 'update',
                    'applied_version' => $updated->server_version,
                    'response_payload' => $responsePayload,
                ]);

                return ['success' => $responsePayload];
            }

            if ($action === 'delete') {
                $recipe = Recipe::find($entityId);

                if (!$recipe) {
                    // Bereits gelöscht: idempotent als Erfolg werten
                    $responsePayload = [
                        'mutation_id' => $mutationId,
                        'status' => 'already_deleted',
                        'entity_id' => $entityId,
                        'server_version' => $baseVersion,
                    ];
                    return ['success' => $responsePayload];
                }

                if (!$user->can('delete', $recipe)) {
                    return [
                        'conflict' => [
                            'mutation_id' => $mutationId,
                            'entity_id' => $entityId,
                            'conflict_type' => 'permission_denied',
                        ],
                    ];
                }

                $recipeService->deleteRecipe($recipe);

                $responsePayload = [
                    'mutation_id' => $mutationId,
                    'status' => 'applied',
                    'entity_id' => $recipe->id,
                    'server_version' => $recipe->server_version,
                ];

                SyncMutationLog::create([
                    'user_id' => $user->id,
                    'mutation_id' => $mutationId,
                    'entity_type' => 'recipe',
                    'entity_id' => $recipe->id,
                    'action' => 'delete',
                    'applied_version' => $recipe->server_version,
                    'response_payload' => $responsePayload,
                ]);

                return ['success' => $responsePayload];
            }

            return ['conflict' => ['mutation_id' => $mutationId, 'conflict_type' => 'unsupported_action']];
        });
    }

    protected function handleCategoryMutation($user, string $mutationId, string $entityId, string $action, int $baseVersion, array $payload, CategoryService $categoryService): array
    {
        return DB::transaction(function () use ($user, $mutationId, $entityId, $action, $baseVersion, $payload, $categoryService) {
            if (!$user->canManageCategories()) {
                return [
                    'conflict' => [
                        'mutation_id' => $mutationId,
                        'entity_id' => $entityId,
                        'conflict_type' => 'permission_denied',
                    ],
                ];
            }

            if ($action === 'create') {
                $existing = Category::withTrashed()->find($entityId);
                if ($existing) {
                    return [
                        'conflict' => [
                            'mutation_id' => $mutationId,
                            'entity_id' => $entityId,
                            'conflict_type' => 'already_exists',
                        ],
                    ];
                }

                $payload['id'] = $entityId;
                $cat = $categoryService->createCategory($user, $payload);

                $responsePayload = [
                    'mutation_id' => $mutationId,
                    'status' => 'applied',
                    'entity_id' => $cat->id,
                    'server_version' => $cat->server_version,
                ];

                SyncMutationLog::create([
                    'user_id' => $user->id,
                    'mutation_id' => $mutationId,
                    'entity_type' => 'category',
                    'entity_id' => $cat->id,
                    'action' => 'create',
                    'applied_version' => $cat->server_version,
                    'response_payload' => $responsePayload,
                ]);

                return ['success' => $responsePayload];
            }

            return ['conflict' => ['mutation_id' => $mutationId, 'conflict_type' => 'unsupported_category_action']];
        });
    }
}

