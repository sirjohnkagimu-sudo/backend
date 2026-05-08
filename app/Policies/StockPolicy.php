<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StockPolicy
{
    public function before(User $user)
    {
        // Admin can do anything
        if ($user->role === 'admin') {
            return true;
        }
    }

    public function view(User $user, Item $item)
    {
        // All authenticated users can view stock/items
        return true;
    }

    public function stockIn(User $user)
    {
        // All authenticated users can stock in
        return true;
    }

    public function stockOut(User $user)
    {
        // All authenticated users can stock out
        return true;
    }

    public function adjust(User $user)
    {
        // All authenticated users can adjust stock
        return true;
    }

    public function viewReports(User $user)
    {
        // All authenticated users can view reports
        return true;
    }
}

