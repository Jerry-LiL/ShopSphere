<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Store $store): bool
    {
        return $store->is_active || ($user && ($user->isAdmin() || $store->user_id === $user->id));
    }

    public function update(User $user, Store $store): bool
    {
        return $user->isAdmin() || $store->user_id === $user->id;
    }
}
