<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        if ($product->is_active) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isVendor() && $user->store && $user->store->id === $product->store_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || ($user->isVendor() && (bool) $user->store);
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isVendor() && $user->store && $user->store->id === $product->store_id;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isVendor() && $user->store && $user->store->id === $product->store_id;
    }
}
