<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || (bool) $user->is_admin;
    }

    public function cancel(User $user, Order $order): bool
    {
        return (bool) $user->is_admin;
    }
}
