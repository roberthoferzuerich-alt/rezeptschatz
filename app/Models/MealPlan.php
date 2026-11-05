<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'user_id',
        'recipe_id',
        'custom_title',
        'date',
        'meal_type',
        'portions',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'portions' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->recipe ? $this->recipe->title : ($this->custom_title ?: 'Geplantes Gericht');
    }
}

