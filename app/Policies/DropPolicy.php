<?php

namespace App\Policies;

use App\Models\Drop;
use App\Models\User;

class DropPolicy
{
    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function request(User $user, Drop $drop): bool
    {
        return ! $drop->isEnded() && ! $drop->isSoldOut();
    }

    public function update(User $user, Drop $drop): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Drop $drop): bool
    {
        return (bool) $user->is_admin;
    }
}