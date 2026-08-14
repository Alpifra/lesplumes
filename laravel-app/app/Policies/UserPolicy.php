<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * The circle is open: every plume may look up the others, which is what
     * feeds the participant lists and the invitation lookups.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, User $model): bool
    {
        return true;
    }

    /**
     * A plume only edits her own account.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Same rule for closing an account.
     */
    public function delete(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }
}
