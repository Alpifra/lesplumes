<?php

namespace App\Policies;

use App\Models\Round;
use App\Models\User;

class RoundPolicy
{
    /**
     * A session and the texts it holds are only visible to the plumes it
     * involves: its master and its participants.
     */
    public function view(User $user, Round $round): bool
    {
        return $round->master_id === $user->id
            || $round->participants()->where('users.id', $user->id)->exists();
    }

    /**
     * A session only opens when the plume whose turn it is opens it — the
     * same rule guards handing the turn over to someone else.
     */
    public function create(User $user): bool
    {
        return Round::nextSelector()?->id === $user->id;
    }

    /**
     * Only the master steers his session, as for invitations.
     */
    public function update(User $user, Round $round): bool
    {
        return $round->master_id === $user->id;
    }

    /**
     * Only the master may close his session for good.
     */
    public function delete(User $user, Round $round): bool
    {
        return $this->update($user, $round);
    }
}
