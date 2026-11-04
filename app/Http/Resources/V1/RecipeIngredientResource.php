<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeIngredientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipe_id' => $this->recipe_id,
            'sort_order' => (int) $this->sort_order,
            'group_name' => $this->group_name,
            'name' => $this->name,
            'amount_numeric' => $this->amount_numeric !== null ? (float) $this->amount_numeric : null,
            'unit' => $this->unit,
            'amount_text' => $this->amount_text,
        ];
    }
}
