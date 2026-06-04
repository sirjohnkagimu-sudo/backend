<?php

namespace App\Http\Controllers;

use App\Models\StoreItem;
use App\Models\Transaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    // ==================== STORE ITEMS ====================

    public function index(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = StoreItem::query()->where('tenant_id', $tenantId);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('in_stock')) {
            if ($request->in_stock === 'low') {
                $query->where('in_stock', '>', 0)->where('in_stock', '<=', 5);
            } elseif ($request->in_stock === 'out') {
                $query->where('in_stock', 0);
            } elseif ($request->in_stock === 'available') {
                $query->where('in_stock', '>', 0);
            }
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $items = $query->paginate($perPage);

        return response()->json([
            'data' => $items->items(),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ]
        ]);
    }

    public function show($id): JsonResponse
    {
        $item = StoreItem::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        return response()->json($item);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'condition' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $item = StoreItem::create($validated);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'created store item',
            'type' => 'store_item_create',
            'description' => 'Created store item: ' . $item->name,
            'metadata' => [
                'store_item_id' => $item->id,
                'item_name' => $item->name,
                'department' => 'Store',
            ],
        ]);

        return response()->json($item, 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $item = StoreItem::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'condition' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);

        $item->update($validated);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'updated store item',
            'type' => 'store_item_update',
            'description' => 'Updated store item: ' . $item->name,
            'metadata' => [
                'store_item_id' => $item->id,
                'item_name' => $item->name,
                'department' => 'Store',
            ],
        ]);

        return response()->json($item);
    }

    public function destroy($id): JsonResponse
    {
        $item = StoreItem::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'deleted store item',
            'type' => 'store_item_delete',
            'description' => 'Deleted store item: ' . $item->name,
            'metadata' => [
                'store_item_id' => $item->id,
                'item_name' => $item->name,
                'department' => 'Store',
            ],
        ]);

        $item->delete();

        return response()->json(['message' => 'Store item deleted']);
    }

    // ==================== TRANSACTIONS ====================

    public function transactions(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Transaction::whereHas('item', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->with(['item']);

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    public function storeTransaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:store_items,id',
            'type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'approved_by' => 'nullable|string',
            'created_by' => 'nullable|string',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $transaction = Transaction::create($validated);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'created store transaction',
            'type' => 'store_transaction_create',
            'description' => 'Created ' . $transaction->type . ' transaction for store item',
            'metadata' => [
                'transaction_id' => $transaction->id,
                'item_id' => $transaction->item_id,
                'department' => 'Store',
            ],
        ]);

        return response()->json($transaction->load('item'), 201);
    }

    // ==================== REPORTS ====================

    public function reports(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $items = StoreItem::where('tenant_id', $tenantId)->get();

        $totalItems = $items->count();
        $totalValue = (float) $items->sum(fn ($item) => ($item->in_stock ?? 0) * ($item->price ?? 0));
        $lowStockItems = $items->filter(fn ($item) => ($item->in_stock ?? 0) <= ($item->min_quantity ?? 0))->count();

        return response()->json([
            'totalItems' => $totalItems,
            'totalValue' => $totalValue,
            'lowStockItems' => $lowStockItems,
            'itemsByCategory' => $items->groupBy('category')->map->count(),
            'itemsByCondition' => $items->groupBy('condition')->map->count(),
        ]);
    }

    // ==================== AUDIT ====================

    public function audit(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $logs = ActivityLog::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->where('type', 'like', 'store_%');
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }
}
