<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Erstellt eine neue Kategorie.
     */
    public function createCategory(User $user, array $data): Category
    {
        return Category::create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => trim($data['name']),
            'icon' => $data['icon'] ?? null,
            'color' => $data['color'] ?? '#7a9c33',
            'server_version' => (int) ($data['server_version'] ?? 1),
        ]);
    }

    /**
     * Aktualisiert eine bestehende Kategorie.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->name = trim($data['name'] ?? $category->name);
        $category->icon = array_key_exists('icon', $data) ? $data['icon'] : $category->icon;
        $category->color = array_key_exists('color', $data) ? $data['color'] : $category->color;
        $category->server_version = $category->server_version + 1;
        $category->save();

        return $category;
    }

    /**
     * Löscht eine Kategorie (Soft Delete).
     */
    public function deleteCategory(Category $category): void
    {
        $category->server_version = $category->server_version + 1;
        $category->save();
        $category->delete();
    }
}
