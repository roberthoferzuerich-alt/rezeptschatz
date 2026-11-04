<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'recipe_id',
        'sort_order',
        'group_name',
        'name',
        'amount_numeric',
        'unit',
        'amount_text',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'amount_numeric' => 'decimal:3',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}

