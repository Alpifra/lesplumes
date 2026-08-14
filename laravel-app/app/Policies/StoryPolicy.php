<?php

namespace App\Policies;

use App\Models\Story;
use App\Models\User;

class StoryPolicy
{
    /**
     * A text is readable by every plume of its session.
     */
    public function view(User $user, Story $story): bool
    {
        return $story->round !== null && $user->can('view', $story->round);
    }

    /**
     * A text belongs to the plume who wrote it: only its author replaces the
     * file attached to it. Not even the master of the session may.
     */
    public function update(User $user, Story $story): bool
    {
        return $story->writer_id === $user->id;
    }

    /**
     * Same rule for removing a text and its media.
     */
    public function delete(User $user, Story $story): bool
    {
        return $this->update($user, $story);
    }
}
