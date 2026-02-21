<?php

namespace App\Policies;

use App\Models\TastingSession;
use App\Models\User;

class TastingSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TastingSession $tastingSession): bool
    {
        return $tastingSession->isHost($user)
            || $tastingSession->participants()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TastingSession $tastingSession): bool
    {
        return $tastingSession->isHost($user);
    }

    public function delete(User $user, TastingSession $tastingSession): bool
    {
        return $tastingSession->isHost($user);
    }
}
