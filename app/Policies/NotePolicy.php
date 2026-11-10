<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

/**
 * Who may do what with a note. Laravel finds this policy automatically
 * because of its name (Note -> NotePolicy).
 */
class NotePolicy
{
    public function view(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    public function update(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }

    public function delete(User $user, Note $note): bool
    {
        return $note->user_id === $user->id;
    }
}
