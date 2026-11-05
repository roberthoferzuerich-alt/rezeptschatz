<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingListItem extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'user_id',
        'recipe_id',
        'name',
        'amount_numeric',
        'unit',
        'amount_text',
        'category',
        'is_completed',
        'sort_order',
    ];

    protected $casts = [
        'amount_numeric' => 'float',
        'is_completed' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Schöne lesbare Formatierung der Menge (z.B. "500 g" oder "1/2 TL").
     */
    public function getFormattedAmountAttribute(): string
    {
        if ($this->amount_text) {
            return $this->amount_text;
        }

        if ($this->amount_numeric !== null) {
            $num = $this->amount_numeric + 0;
            return trim($num . ' ' . ($this->unit ?? ''));
        }

        return $this->unit ?? '';
    }
}

