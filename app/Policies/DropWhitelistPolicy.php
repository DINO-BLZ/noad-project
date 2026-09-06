<?php

namespace App\Policies;

use App\Models\DropWhitelist;
use App\Models\User;

class DropWhitelistPolicy
{
    public function approve(User $user, DropWhitelist $whitelist): bool
    {
        return (bool) $user->is_admin;
    }

    public function reject(User $user, DropWhitelist $whitelist): bool
    {
        return (bool) $user->is_admin;
    }
}
