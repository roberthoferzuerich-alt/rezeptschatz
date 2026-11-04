<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;

class RecipePolicy
{
    /**
     * Bestimmt, ob der Benutzer die Rezeptliste oder ein Rezept einsehen darf.
     */
    public function view(User $user, Recipe $recipe): bool
    {
        return $recipe->is_shared || $recipe->user_id === $user->id;
    }

    /**
     * Bestimmt, ob der Benutzer ein neues Rezept erstellen darf.
     */
    public function create(User $user): bool
    {
        return $user->canCreateRecipes();
    }

    /**
     * Bestimmt, ob der Benutzer das Rezept bearbeiten darf.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        return $user->canEditRecipe($recipe);
    }

    /**
     * Bestimmt, ob der Benutzer das Rezept löschen darf.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        return $user->canDeleteRecipe($recipe);
    }
}

