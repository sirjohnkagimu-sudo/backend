<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\User;
use App\Models\LabAccessCode;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Total inventory items for the tenant
        $totalItems = Item::where('tenant_id', $tenantId)->count();

        // Active users (lab access codes that are active)
        $activeUsers = LabAccessCode::where('school_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // Total suppliers for the tenant
        $totalSuppliers = Supplier::where('tenant_id', $tenantId)->count();

        // Recent transactions (stock movements)
        $recentTransactions = DB::table('stock_movements')
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Low stock items
        $lowStockItems = Item::where('tenant_id', $tenantId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();

        // Total value of inventory
        $totalValue = Item::where('tenant_id', $tenantId)
            ->sum(DB::raw('quantity * unit_cost'));

        return response()->json([
            'total_items' => $totalItems,
            'active_users' => $activeUsers,
            'total_suppliers' => $totalSuppliers,
            'recent_transactions' => $recentTransactions,
            'low_stock_items' => $lowStockItems,
            'total_value' => $totalValue,
        ]);
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
