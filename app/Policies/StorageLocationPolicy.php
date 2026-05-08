<?php

namespace App\Policies;

use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StorageLocationPolicy
{
    public function viewAny(User $user)
    {
        // All authenticated users can view storage locations
        return true;
    }

    public function view(User $user, StorageLocation $storageLocation)
    {
        // All authenticated users can view individual storage locations
        return true;
    }

    public function create(User $user)
    {
        // All authenticated users can create storage locations
        return true;
    }

    public function update(User $user, StorageLocation $storageLocation)
    {
        // All authenticated users can update storage locations
        return true;
    }

    public function delete(User $user, StorageLocation $storageLocation)
    {
        // Only admins can delete storage locations for data safety
        return $user->role === 'admin';
    }
}
