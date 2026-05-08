<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Item;

class ItemPolicy
{
    public function viewAny(User $user)
    {
        // All authenticated users can view items
        return true;
    }

    public function view(User $user, Item $item)
    {
        // All authenticated users can view individual items
        return true;
    }

    public function create(User $user)
    {
        // All authenticated users can create items
        return true;
    }

    public function update(User $user, Item $item)
    {
        // All authenticated users can update items
        return true;
    }

    public function delete(User $user, Item $item)
    {
        // Only admins can delete items for data safety
        return $user->role === 'admin';
    }
}
