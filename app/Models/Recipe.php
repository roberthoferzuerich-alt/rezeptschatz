<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'id',
        'user_id',
        'title',
        'description',
        'portions',
        'prep_time_minutes',
        'cook_time_minutes',
        'rest_time_minutes',
        'calories',
        'carbohydrates',
        'fat',
        'protein',
        'nutrition_raw',
        'notes',
        'source_url',
        'is_favorite',
        'is_shared',
        'image_path',
        'image_hash',
        'server_version',
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'is_shared' => 'boolean',
            'portions' => 'integer',
            'prep_time_minutes' => 'integer',
            'cook_time_minutes' => 'integer',
            'rest_time_minutes' => 'integer',
            'calories' => 'integer',
            'carbohydrates' => 'float',
            'fat' => 'float',
            'protein' => 'float',
            'server_version' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'recipe_category');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'recipe_favorites')->withTimestamps();
    }

    public function isFavoritedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($this->relationLoaded('favoritedBy')) {
            return $this->favoritedBy->contains('id', $user->id);
        }

        return $this->favoritedBy()->where('user_id', $user->id)->exists();
    }

    public function canBeEditedBy(?User $user): bool
    {
        return $user && $user->id === $this->user_id;
    }
}
