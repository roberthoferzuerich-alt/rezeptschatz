<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Bestimmt, ob der Benutzer Kategorien einsehen darf.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Bestimmt, ob der Benutzer neue Kategorien erstellen darf.
     */
    public function create(User $user): bool
    {
        return $user->canManageCategories();
    }

    /**
     * Bestimmt, ob der Benutzer Kategorien bearbeiten darf.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->canManageCategories();
    }

    /**
     * Bestimmt, ob der Benutzer Kategorien löschen darf.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->canManageCategories();
    }
}

