<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\User;
use App\Models\Order;
use App\Models\School;
use App\Models\Item;
use App\Models\LabAccessCode;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
     public function dashboard()
    {
        session(['title' => 'Dashboard']);

        $labs = number_format(Lab::count());
        $schoolsCount = number_format(School::count());
        $users = number_format(User::count());

          // Get registered schools for display
        $schools = School::latest()->paginate(10);



        return view('dashboard', compact('labs', 'users', 'schools', 'schoolsCount'));
    }

    /**
     * Combined dashboard stats API endpoint (optimized with caching)
     * Returns all dashboard data in a single request to reduce API calls
     */
    public function dashboardStats(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $tenantId = $user->tenant_id;
        $cacheKey = "dashboard_stats_{$tenantId}";

        // Check cache first (3-minute cache)
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Get total items count
        $totalItems = Item::where('tenant_id', $tenantId)->count();

        // Get unread notifications count
        $unreadNotifications = Notification::where('is_ignored', false)
            ->where('is_read', false)
            ->count();

        // Get active departments (unique departments with active users)
        $activeUsers = LabAccessCode::where('school_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // Get analytics data
        $analyticsData = DB::table('items')
            ->where('tenant_id', $tenantId)
            ->select(
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(quantity * unit_cost) as total_value')
            )
            ->first();

        $lowStockItems = Item::where('tenant_id', $tenantId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();

        $recentTransactions = DB::table('stock_movements')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $result = [
            'total_items' => (int) $totalItems,
            'unread_notifications' => (int) $unreadNotifications,
            'active_departments' => $activeUsers,
            'analytics' => [
                'total_items' => (int) $analyticsData->total_items,
                'total_value' => (float) $analyticsData->total_value,
                'low_stock_items' => (int) $lowStockItems,
                'recent_transactions' => (int) $recentTransactions,
                'active_users' => $activeUsers,
            ],
            'cached_at' => now()->toIso8601String(),
        ];

        // Cache for 3 minutes
        Cache::put($cacheKey, $result, 180);

        return response()->json($result);
    }
}
