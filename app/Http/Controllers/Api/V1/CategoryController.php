<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function store(Request $request, CategoryService $categoryService): JsonResponse
    {
        $this->authorize('create', Category::class);

        $validated = $request->validate([
            'id' => ['nullable', 'uuid'],
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $category = $categoryService->createCategory($request->user(), $validated);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($category),
            'message' => 'Kategorie erfolgreich erstellt.',
        ], 201);
    }

    public function update(Request $request, Category $category, CategoryService $categoryService): JsonResponse
    {
        $this->authorize('update', $category);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        $updated = $categoryService->updateCategory($category, $validated);

        return response()->json([
            'success' => true,
            'data' => new CategoryResource($updated),
            'message' => 'Kategorie erfolgreich aktualisiert.',
        ]);
    }

    public function destroy(Category $category, CategoryService $categoryService): JsonResponse
    {
        $this->authorize('delete', $category);

        $categoryService->deleteCategory($category);

        return response()->json([
            'success' => true,
            'message' => 'Kategorie erfolgreich gelöscht.',
        ]);
    }
}

