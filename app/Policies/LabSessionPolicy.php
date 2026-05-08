<?php

namespace App\Policies;

use App\Models\LabSession;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LabSessionPolicy
{
    public function viewAny(User $user)
    {
        // All authenticated users can view lab sessions
        return true;
    }

    public function view(User $user, LabSession $session)
    {
        // All authenticated users can view individual lab sessions
        return true;
    }

    public function create(User $user)
    {
        // All authenticated users can create lab sessions
        return true;
    }

    public function update(User $user, LabSession $session)
    {
        // All authenticated users can update lab sessions
        return true;
    }

    public function delete(User $user, LabSession $session)
    {
        // Only admins can delete lab sessions
        return $user->role_id === 1;
    }
}

