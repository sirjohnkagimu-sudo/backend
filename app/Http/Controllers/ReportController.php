<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use App\Models\LabAccessCode;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ReportController extends Controller
{
    public function analytics(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->is_school_admin) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can view analytics.'], 403);
        }

        // Get tenant_id from user
        $tenantId = $user->tenant_id;
        $cacheKey = "analytics_{$tenantId}";

        // Check cache first (3-minute cache)
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        // Get all analytics data in a single optimized query using subqueries
        $analyticsData = DB::table('items')
            ->where('tenant_id', $tenantId)
            ->select(
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(quantity * unit_cost) as total_value')
            )
            ->first();

        // Count low stock items
        $lowStockItems = Item::where('tenant_id', $tenantId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();

        // Count active users
        $activeUsers = LabAccessCode::where('school_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // Count total suppliers
        $totalSuppliers = Supplier::where('tenant_id', $tenantId)->count();

        // Count recent transactions (last 30 days)
        $recentTransactions = DB::table('stock_movements')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $result = [
            'total_items' => (int) $analyticsData->total_items,
            'active_users' => $activeUsers,
            'total_suppliers' => $totalSuppliers,
            'recent_transactions' => $recentTransactions,
            'low_stock_items' => $lowStockItems,
            'total_value' => (float) $analyticsData->total_value,
        ];

        // Cache for 3 minutes
        Cache::put($cacheKey, $result, 180);

        return response()->json($result);
    }

    public function logReportDownload(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'report_type' => 'required|string',
            'format' => 'required|string',
        ]);

        // Log the activity
        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'download',
            'type' => 'report',
            'description' => 'Downloaded ' . $request->report_type . ' report in ' . $request->format . ' format',
            'metadata' => [
                'report_type' => $request->report_type,
                'format' => $request->format,
            ]
        ]);

        return response()->json(['message' => 'Report download logged']);
    }
}
