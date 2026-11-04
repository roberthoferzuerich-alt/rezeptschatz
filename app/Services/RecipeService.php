<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\RecipeStep;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecipeService
{
    /**
     * Erstellt ein neues Rezept mit Zutaten und Zubereitungsschritten.
     */
    public function createRecipe(User $user, array $data): Recipe
    {
        return DB::transaction(function () use ($user, $data) {
            $recipeId = $data['id'] ?? (string) Str::uuid();

            $recipe = new Recipe();
            $recipe->id = $recipeId;
            $recipe->user_id = $user->id;
            $recipe->title = $data['title'];
            $recipe->description = $data['description'] ?? null;
            $recipe->portions = (int) ($data['portions'] ?? 4);
            $recipe->prep_time_minutes = isset($data['prep_time_minutes']) ? (int) $data['prep_time_minutes'] : null;
            $recipe->cook_time_minutes = isset($data['cook_time_minutes']) ? (int) $data['cook_time_minutes'] : null;
            $recipe->rest_time_minutes = isset($data['rest_time_minutes']) ? (int) $data['rest_time_minutes'] : null;
            $recipe->notes = $data['notes'] ?? null;
            $recipe->source_url = $data['source_url'] ?? null;
            $recipe->is_favorite = (bool) ($data['is_favorite'] ?? false);
            $recipe->is_shared = isset($data['is_shared']) ? (bool) $data['is_shared'] : true;
            $recipe->server_version = (int) ($data['server_version'] ?? 1);
            $recipe->save();

            // Wenn Favorit gewählt, auch in recipe_favorites eintragen
            if ($recipe->is_favorite) {
                $user->favoriteRecipes()->syncWithoutDetaching([$recipe->id]);
            }

            // Kategorien verknüpfen
            if (isset($data['category_ids']) && is_array($data['category_ids'])) {
                $recipe->categories()->sync($data['category_ids']);
            }

            // Zutaten anlegen
            if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                foreach ($data['ingredients'] as $index => $item) {
                    RecipeIngredient::create([
                        'id' => $item['id'] ?? (string) Str::uuid(),
                        'recipe_id' => $recipe->id,
                        'sort_order' => (int) ($item['sort_order'] ?? $index),
                        'group_name' => !empty($item['group_name']) ? trim($item['group_name']) : null,
                        'name' => trim($item['name'] ?? ''),
                        'amount_numeric' => isset($item['amount_numeric']) && is_numeric($item['amount_numeric']) ? (float) $item['amount_numeric'] : null,
                        'unit' => !empty($item['unit']) ? trim($item['unit']) : null,
                        'amount_text' => !empty($item['amount_text']) ? trim($item['amount_text']) : null,
                    ]);
                }
            }

            // Zubereitungsschritte anlegen
            if (isset($data['steps']) && is_array($data['steps'])) {
                foreach ($data['steps'] as $index => $step) {
                    $instruction = is_array($step) ? ($step['instruction'] ?? '') : (string) $step;
                    if (trim($instruction) === '') {
                        continue;
                    }
                    RecipeStep::create([
                        'id' => (is_array($step) && !empty($step['id'])) ? $step['id'] : (string) Str::uuid(),
                        'recipe_id' => $recipe->id,
                        'step_number' => (int) (is_array($step) && isset($step['step_number']) ? $step['step_number'] : ($index + 1)),
                        'instruction' => trim($instruction),
                    ]);
                }
            }

            // Bild verarbeiten, falls übergeben
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $this->saveRecipeImage($recipe, $data['image']);
            }

            return $recipe->load(['ingredients', 'steps', 'categories']);
        });
    }

    /**
     * Aktualisiert ein bestehendes Rezept (nur durch den Eigentümer).
     */
    public function updateRecipe(Recipe $recipe, array $data): Recipe
    {
        return DB::transaction(function () use ($recipe, $data) {
            $recipe->title = $data['title'] ?? $recipe->title;
            $recipe->description = array_key_exists('description', $data) ? $data['description'] : $recipe->description;
            $recipe->portions = isset($data['portions']) ? (int) $data['portions'] : $recipe->portions;
            $recipe->prep_time_minutes = array_key_exists('prep_time_minutes', $data) ? (isset($data['prep_time_minutes']) ? (int) $data['prep_time_minutes'] : null) : $recipe->prep_time_minutes;
            $recipe->cook_time_minutes = array_key_exists('cook_time_minutes', $data) ? (isset($data['cook_time_minutes']) ? (int) $data['cook_time_minutes'] : null) : $recipe->cook_time_minutes;
            $recipe->rest_time_minutes = array_key_exists('rest_time_minutes', $data) ? (isset($data['rest_time_minutes']) ? (int) $data['rest_time_minutes'] : null) : $recipe->rest_time_minutes;
            $recipe->notes = array_key_exists('notes', $data) ? $data['notes'] : $recipe->notes;
            $recipe->source_url = array_key_exists('source_url', $data) ? $data['source_url'] : $recipe->source_url;
            if (isset($data['is_shared'])) {
                $recipe->is_shared = (bool) $data['is_shared'];
            }

            // Version erhöhen
            $recipe->server_version = $recipe->server_version + 1;
            $recipe->save();

            // Kategorien aktualisieren
            if (isset($data['category_ids']) && is_array($data['category_ids'])) {
                $recipe->categories()->sync($data['category_ids']);
            }

            // Zutaten atomar ersetzen
            if (isset($data['ingredients']) && is_array($data['ingredients'])) {
                $recipe->ingredients()->delete();
                foreach ($data['ingredients'] as $index => $item) {
                    if (empty(trim($item['name'] ?? ''))) {
                        continue;
                    }
                    RecipeIngredient::create([
                        'id' => $item['id'] ?? (string) Str::uuid(),
                        'recipe_id' => $recipe->id,
                        'sort_order' => (int) ($item['sort_order'] ?? $index),
                        'group_name' => !empty($item['group_name']) ? trim($item['group_name']) : null,
                        'name' => trim($item['name']),
                        'amount_numeric' => isset($item['amount_numeric']) && is_numeric($item['amount_numeric']) ? (float) $item['amount_numeric'] : null,
                        'unit' => !empty($item['unit']) ? trim($item['unit']) : null,
                        'amount_text' => !empty($item['amount_text']) ? trim($item['amount_text']) : null,
                    ]);
                }
            }

            // Schritte atomar ersetzen
            if (isset($data['steps']) && is_array($data['steps'])) {
                $recipe->steps()->delete();
                foreach ($data['steps'] as $index => $step) {
                    $instruction = is_array($step) ? ($step['instruction'] ?? '') : (string) $step;
                    if (trim($instruction) === '') {
                        continue;
                    }
                    RecipeStep::create([
                        'id' => (is_array($step) && !empty($step['id'])) ? $step['id'] : (string) Str::uuid(),
                        'recipe_id' => $recipe->id,
                        'step_number' => (int) (is_array($step) && isset($step['step_number']) ? $step['step_number'] : ($index + 1)),
                        'instruction' => trim($instruction),
                    ]);
                }
            }

            // Bild löschen oder ersetzen
            if (!empty($data['remove_image'])) {
                $this->deleteRecipeImage($recipe);
            } elseif (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $this->saveRecipeImage($recipe, $data['image']);
            }

            return $recipe->load(['ingredients', 'steps', 'categories']);
        });
    }

    /**
     * Schaltet den persönlichen Favoriten-Status für einen bestimmten Benutzer um.
     */
    public function toggleFavorite(User $user, Recipe $recipe): bool
    {
        $exists = $user->favoriteRecipes()->where('recipe_id', $recipe->id)->exists();

        if ($exists) {
            $user->favoriteRecipes()->detach($recipe->id);
            return false;
        } else {
            $user->favoriteRecipes()->attach($recipe->id);
            return true;
        }
    }

    /**
     * Kopiert ein Rezept als eigenständige Kopie für einen Benutzer (z. B. für die Tochter zum Abwandeln).
     */
    public function copyRecipeForUser(User $user, Recipe $sourceRecipe): Recipe
    {
        return DB::transaction(function () use ($user, $sourceRecipe) {
            $newRecipe = new Recipe();
            $newRecipe->id = (string) Str::uuid();
            $newRecipe->user_id = $user->id;
            $newRecipe->title = $sourceRecipe->title . ' (Kopie)';
            $newRecipe->description = $sourceRecipe->description;
            $newRecipe->portions = $sourceRecipe->portions;
            $newRecipe->prep_time_minutes = $sourceRecipe->prep_time_minutes;
            $newRecipe->cook_time_minutes = $sourceRecipe->cook_time_minutes;
            $newRecipe->rest_time_minutes = $sourceRecipe->rest_time_minutes;
            $newRecipe->notes = $sourceRecipe->notes;
            $newRecipe->source_url = $sourceRecipe->source_url;
            $newRecipe->is_shared = true;
            $newRecipe->server_version = 1;

            // Bild kopieren falls vorhanden
            if ($sourceRecipe->image_path && Storage::disk('local')->exists($sourceRecipe->image_path)) {
                $ext = pathinfo($sourceRecipe->image_path, PATHINFO_EXTENSION) ?: 'jpg';
                $newPath = "recipes/{$user->id}/{$newRecipe->id}.{$ext}";
                Storage::disk('local')->copy($sourceRecipe->image_path, $newPath);
                $newRecipe->image_path = $newPath;
                $newRecipe->image_hash = $sourceRecipe->image_hash;
            }

            $newRecipe->save();

            // Kategorien übernehmen
            $newRecipe->categories()->sync($sourceRecipe->categories->pluck('id'));

            // Zutaten duplizieren
            foreach ($sourceRecipe->ingredients as $ing) {
                RecipeIngredient::create([
                    'id' => (string) Str::uuid(),
                    'recipe_id' => $newRecipe->id,
                    'sort_order' => $ing->sort_order,
                    'group_name' => $ing->group_name,
                    'name' => $ing->name,
                    'amount_numeric' => $ing->amount_numeric,
                    'unit' => $ing->unit,
                    'amount_text' => $ing->amount_text,
                ]);
            }

            // Schritte duplizieren
            foreach ($sourceRecipe->steps as $step) {
                RecipeStep::create([
                    'id' => (string) Str::uuid(),
                    'recipe_id' => $newRecipe->id,
                    'step_number' => $step->step_number,
                    'instruction' => $step->instruction,
                ]);
            }

            return $newRecipe->load(['ingredients', 'steps', 'categories']);
        });
    }

    /**
     * Löscht ein Rezept (Soft Delete).
     */
    public function deleteRecipe(Recipe $recipe): void
    {
        $recipe->server_version = $recipe->server_version + 1;
        $recipe->save();
        $recipe->delete();
    }

    /**
     * Speichert eine hochgeladene Bilddatei privat und berechnet den SHA-256 Hash.
     */
    public function saveRecipeImage(Recipe $recipe, UploadedFile $file): string
    {
        if ($recipe->image_path && Storage::disk('local')->exists($recipe->image_path)) {
            Storage::disk('local')->delete($recipe->image_path);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $relativePath = "recipes/{$recipe->user_id}/{$recipe->id}.{$extension}";

        Storage::disk('local')->putFileAs("recipes/{$recipe->user_id}", $file, "{$recipe->id}.{$extension}");

        $hash = hash_file('sha256', $file->getRealPath());

        $recipe->image_path = $relativePath;
        $recipe->image_hash = $hash;
        $recipe->save();

        return $relativePath;
    }

    /**
     * Entfernt das Bild eines Rezepts.
     */
    public function deleteRecipeImage(Recipe $recipe): void
    {
        if ($recipe->image_path && Storage::disk('local')->exists($recipe->image_path)) {
            Storage::disk('local')->delete($recipe->image_path);
        }

        $recipe->image_path = null;
        $recipe->image_hash = null;
        $recipe->save();
    }
}
