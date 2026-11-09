<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RecipeResource;
use App\Models\Recipe;
use App\Services\RecipeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RecipeController extends Controller
{
    /**
     * Liste der verfügbaren Rezepte.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Recipe::with(['categories', 'tags', 'ingredients', 'steps', 'user', 'favoritedBy'])
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('is_shared', true);
            });

        // Suche
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('ingredients', fn($iq) => $iq->where('name', 'like', "%{$term}%"));
            });
        }

        // Kategorie-Filter
        if ($request->filled('category_id')) {
            $catId = $request->input('category_id');
            $query->whereHas('categories', fn($cq) => $cq->where('categories.id', $catId));
        }

        // Favoriten-Filter
        if ($request->boolean('favorite_only')) {
            $query->whereHas('favoritedBy', fn($fq) => $fq->where('user_id', $user->id));
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $recipes = $query->orderByDesc('updated_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => RecipeResource::collection($recipes),
            'meta' => [
                'current_page' => $recipes->currentPage(),
                'last_page' => $recipes->lastPage(),
                'per_page' => $recipes->perPage(),
                'total' => $recipes->total(),
            ],
        ]);
    }

    /**
     * Einzelnes Rezept-Aggregat abrufen.
     */
    public function show(Request $request, Recipe $recipe): JsonResponse
    {
        $this->authorize('view', $recipe);

        $recipe->load(['categories', 'tags', 'ingredients', 'steps', 'user', 'favoritedBy']);

        return response()->json([
            'success' => true,
            'data' => new RecipeResource($recipe),
        ]);
    }

    /**
     * Neues Rezept erstellen.
     */
    public function store(Request $request, RecipeService $recipeService): JsonResponse
    {
        $this->authorize('create', Recipe::class);

        $validated = $request->validate([
            'id' => ['nullable', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'portions' => ['required', 'integer', 'min:1', 'max:100'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0'],
            'rest_time_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'is_favorite' => ['boolean'],
            'is_shared' => ['boolean'],
            'category_ids' => ['array'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['uuid', 'exists:tags,id'],
            'ingredients' => ['array'],
            'ingredients.*.id' => ['nullable', 'uuid'],
            'ingredients.*.sort_order' => ['nullable', 'integer'],
            'ingredients.*.group_name' => ['nullable', 'string', 'max:100'],
            'ingredients.*.name' => ['required', 'string', 'max:255'],
            'ingredients.*.amount_numeric' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.amount_text' => ['nullable', 'string', 'max:100'],
            'steps' => ['array'],
            'steps.*.id' => ['nullable', 'uuid'],
            'steps.*.step_number' => ['nullable', 'integer'],
            'steps.*.instruction' => ['required', 'string'],
        ]);

        $recipe = $recipeService->createRecipe($request->user(), $validated);

        return response()->json([
            'success' => true,
            'data' => new RecipeResource($recipe),
            'message' => 'Rezept erfolgreich erstellt.',
        ], 201);
    }

    /**
     * Rezept aktualisieren.
     */
    public function update(Request $request, Recipe $recipe, RecipeService $recipeService): JsonResponse
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'portions' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'prep_time_minutes' => ['nullable', 'integer', 'min:0'],
            'cook_time_minutes' => ['nullable', 'integer', 'min:0'],
            'rest_time_minutes' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
            'source_url' => ['nullable', 'url', 'max:1000'],
            'is_shared' => ['boolean'],
            'category_ids' => ['array'],
            'category_ids.*' => ['uuid', 'exists:categories,id'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['uuid', 'exists:tags,id'],
            'ingredients' => ['array'],
            'ingredients.*.id' => ['nullable', 'uuid'],
            'ingredients.*.sort_order' => ['nullable', 'integer'],
            'ingredients.*.group_name' => ['nullable', 'string', 'max:100'],
            'ingredients.*.name' => ['required', 'string', 'max:255'],
            'ingredients.*.amount_numeric' => ['nullable', 'numeric', 'min:0'],
            'ingredients.*.unit' => ['nullable', 'string', 'max:50'],
            'ingredients.*.amount_text' => ['nullable', 'string', 'max:100'],
            'steps' => ['array'],
            'steps.*.id' => ['nullable', 'uuid'],
            'steps.*.step_number' => ['nullable', 'integer'],
            'steps.*.instruction' => ['required', 'string'],
        ]);

        $updated = $recipeService->updateRecipe($recipe, $validated);

        return response()->json([
            'success' => true,
            'data' => new RecipeResource($updated),
            'message' => 'Rezept erfolgreich aktualisiert.',
        ]);
    }

    /**
     * Rezept löschen (Soft Delete).
     */
    public function destroy(Request $request, Recipe $recipe, RecipeService $recipeService): JsonResponse
    {
        $this->authorize('delete', $recipe);

        $recipeService->deleteRecipe($recipe);

        return response()->json([
            'success' => true,
            'message' => 'Rezept erfolgreich gelöscht.',
        ]);
    }

    /**
     * Persönlichen Favoriten-Status umschalten.
     */
    public function toggleFavorite(Request $request, Recipe $recipe, RecipeService $recipeService): JsonResponse
    {
        $this->authorize('view', $recipe);

        $newState = $recipeService->toggleFavorite($request->user(), $recipe);

        return response()->json([
            'success' => true,
            'data' => [
                'recipe_id' => $recipe->id,
                'is_favorite' => $newState,
            ],
            'message' => $newState ? 'Zu Favoriten hinzugefügt.' : 'Aus Favoriten entfernt.',
        ]);
    }

    /**
     * Geschützter Bild-Upload.
     */
    public function uploadImage(Request $request, Recipe $recipe, RecipeService $recipeService): JsonResponse
    {
        $this->authorize('update', $recipe);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:10240'], // max 10MB
        ]);

        $recipeService->saveRecipeImage($recipe, $request->file('image'));

        return response()->json([
            'success' => true,
            'data' => [
                'recipe_id' => $recipe->id,
                'image_hash' => $recipe->image_hash,
            ],
            'message' => 'Bild erfolgreich hochgeladen.',
        ]);
    }

    /**
     * Geschützte Bildauslieferung für mobile Endgeräte.
     */
    public function getImage(Request $request, Recipe $recipe): BinaryFileResponse|Response
    {
        $this->authorize('view', $recipe);

        if (!$recipe->image_path || !Storage::disk('local')->exists($recipe->image_path)) {
            abort(404, 'Bilddatei nicht gefunden.');
        }

        $fullPath = Storage::disk('local')->path($recipe->image_path);

        $headers = [
            'Cache-Control' => 'private, max-age=86400, must-revalidate',
        ];

        if ($recipe->image_hash) {
            $headers['ETag'] = '"' . $recipe->image_hash . '"';
            if ($request->header('If-None-Match') === '"' . $recipe->image_hash . '"') {
                return response('', 304, $headers);
            }
        }

        $fileResponse = response()->file($fullPath, $headers);
        $fileResponse->setPrivate();

        return $fileResponse;
    }
}
