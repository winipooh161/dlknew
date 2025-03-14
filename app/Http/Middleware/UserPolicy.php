<?php

// App\Policies\UserPolicy.php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Определяет, может ли $user обновлять профиль $targetUser.
     */
    public function update(User $user, User $targetUser)
    {
        // Пользователь может обновлять свой профиль
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Координатор может обновлять профиль исполнителя, но не клиента или партнёра
        if ($user->status === 'coordinator' && $targetUser->status === 'performer') {
            return true;
        }

        return false;
    }
}
