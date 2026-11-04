<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'title' => $this->title,
            'description' => $this->description,
            'portions' => (int) $this->portions,
            'prep_time_minutes' => $this->prep_time_minutes !== null ? (int) $this->prep_time_minutes : null,
            'cook_time_minutes' => $this->cook_time_minutes !== null ? (int) $this->cook_time_minutes : null,
            'rest_time_minutes' => $this->rest_time_minutes !== null ? (int) $this->rest_time_minutes : null,
            'notes' => $this->notes,
            'source_url' => $this->source_url,
            'is_favorite' => $user ? $this->isFavoritedBy($user) : (bool) $this->is_favorite,
            'is_shared' => (bool) $this->is_shared,
            'has_image' => !empty($this->image_path),
            'image_hash' => $this->image_hash,
            'server_version' => (int) $this->server_version,
            'category_ids' => $this->categories ? $this->categories->pluck('id')->values()->all() : [],
            'ingredients' => RecipeIngredientResource::collection($this->whenLoaded('ingredients')),
            'steps' => RecipeStepResource::collection($this->whenLoaded('steps')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}

