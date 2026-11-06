<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;

class TagService
{
    /**
     * Erstellt ein neues Schlagwort.
     */
    public function createTag(User $user, array $data): Tag
    {
        return Tag::create([
            'id' => $data['id'] ?? (string) Str::uuid(),
            'user_id' => $user->id,
            'name' => trim($data['name']),
            'icon' => $data['icon'] ?? 'label',
            'color' => $data['color'] ?? '#8ea92c',
            'server_version' => (int) ($data['server_version'] ?? 1),
        ]);
    }

    /**
     * Findet ein Schlagwort oder erstellt es bei Bedarf (z. B. im Rezeptformular).
     */
    public function findOrCreateTag(User $user, string $name, ?string $color = '#8ea92c'): Tag
    {
        $name = trim($name);
        $existing = Tag::where('user_id', $user->id)
            ->where('name', $name)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createTag($user, [
            'name' => $name,
            'color' => $color ?: '#8ea92c',
            'icon' => 'label',
        ]);
    }

    /**
     * Aktualisiert ein bestehendes Schlagwort.
     */
    public function updateTag(Tag $tag, array $data): Tag
    {
        $tag->name = trim($data['name'] ?? $tag->name);
        $tag->icon = array_key_exists('icon', $data) ? $data['icon'] : $tag->icon;
        $tag->color = array_key_exists('color', $data) ? $data['color'] : $tag->color;
        $tag->server_version = $tag->server_version + 1;
        $tag->save();

        return $tag;
    }

    /**
     * Löscht ein Schlagwort (Soft Delete).
     */
    public function deleteTag(Tag $tag): void
    {
        $tag->server_version = $tag->server_version + 1;
        $tag->save();
        $tag->delete();
    }
}

