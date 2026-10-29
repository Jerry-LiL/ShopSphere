<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isCustomer() && $order->customer_id === $user->id) {
            return true;
        }

        if ($user->isVendor() && $user->store) {
            return $order->items()->where('store_id', $user->store->id)->exists();
        }

        return false;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor() && $user->store) {
            return $order->items()->where('store_id', $user->store->id)->exists();
        }

        return false;
    }

    public function cancel(User $user, Order $order): bool
    {
        if (!$order->canCancel()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCustomer() && $order->customer_id === $user->id;
    }
}
