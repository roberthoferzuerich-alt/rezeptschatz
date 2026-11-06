<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

class TagPolicy
{
    /**
     * Bestimmt, ob der Benutzer Schlagwörter einsehen darf.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Bestimmt, ob der Benutzer neue Schlagwörter erstellen darf.
     */
    public function create(User $user): bool
    {
        return $user->canManageTags();
    }

    /**
     * Bestimmt, ob der Benutzer Schlagwörter bearbeiten darf.
     */
    public function update(User $user, Tag $tag): bool
    {
        return $user->canManageTags();
    }

    /**
     * Bestimmt, ob der Benutzer Schlagwörter löschen darf.
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $user->canManageTags();
    }
}
