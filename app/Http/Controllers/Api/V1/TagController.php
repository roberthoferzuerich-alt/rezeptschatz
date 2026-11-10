<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TagResource;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Liste aller Schlagwörter des authentifizierten Benutzers.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $tags = Tag::withCount('recipes')
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => TagResource::collection($tags),
        ]);
    }

    /**
     * Neues Schlagwort anlegen.
     */
    public function store(Request $request, TagService $tagService): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $tag = $tagService->findOrCreateTag(
            $request->user(),
            $validated['name'],
            $validated['color'] ?? '#8ea92c'
        );

        if (!empty($validated['icon'])) {
            $tag->icon = $validated['icon'];
            $tag->save();
        }

        return response()->json([
            'success' => true,
            'data' => new TagResource($tag->loadCount('recipes')),
            'message' => 'Schlagwort erfolgreich gespeichert.',
        ], 201);
    }

    /**
     * Schlagwort bearbeiten.
     */
    public function update(Request $request, Tag $tag, TagService $tagService): JsonResponse
    {
        if ($tag->user_id !== $request->user()->id) {
            abort(403, 'Nicht autorisiert.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:20'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $updated = $tagService->updateTag($tag, $validated);

        return response()->json([
            'success' => true,
            'data' => new TagResource($updated->loadCount('recipes')),
            'message' => 'Schlagwort erfolgreich aktualisiert.',
        ]);
    }

    /**
     * Schlagwort löschen.
     */
    public function destroy(Request $request, Tag $tag, TagService $tagService): JsonResponse
    {
        if ($tag->user_id !== $request->user()->id) {
            abort(403, 'Nicht autorisiert.');
        }

        $tagService->deleteTag($tag);

        return response()->json([
            'success' => true,
            'message' => 'Schlagwort erfolgreich gelöscht.',
        ]);
    }
}

