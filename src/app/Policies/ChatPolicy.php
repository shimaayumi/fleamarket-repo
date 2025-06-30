<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Chat $message)
    {
        return $user->id === $message->user_id;
    }

    public function delete(User $user, Chat $message)
    {
        return $user->id === $message->user_id;
    }
}
