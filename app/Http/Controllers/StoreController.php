<?php

namespace App\Http\Controllers;

use App\Models\StoreItem;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Condition;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\StoreSession;
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
        $query = StoreItem::where('tenant_id', $tenantId)->with(['location', 'supplier']);

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
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
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
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
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

        $query = Transaction::whereHas('storeItem', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->with(['storeItem']);

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

        return response()->json($transaction->load('storeItem'), 201);
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

    // ==================== STORE CATEGORIES ====================

    public function categories(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = \App\Models\Category::where('tenant_id', $tenantId)->get();
        return response()->json($categories);
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $name = trim((string) $request->input('name', ''));

        if ($name === '') {
            return response()->json(['error' => 'Category name is required.'], 422);
        }

        $exists = \App\Models\Category::where('tenant_id', $user->tenant_id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Category already exists.'], 422);
        }

        $category = \App\Models\Category::create([
            'tenant_id' => $user->tenant_id,
            'name' => $name,
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'create',
            'type' => 'category',
            'description' => 'Created category: ' . $category->name,
        ]);

        return response()->json($category, 201);
    }

    // ==================== STORE CONDITIONS ====================

    public function conditions(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $conditions = \App\Models\Condition::where('tenant_id', $tenantId)->get();
        return response()->json($conditions);
    }

    public function storeCondition(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $name = trim((string) $request->input('name', ''));

        if ($name === '') {
            return response()->json(['error' => 'Condition name is required.'], 422);
        }

        try {
            \DB::statement("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");

            $exists = \App\Models\Condition::where('tenant_id', $user->tenant_id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->exists();

            if ($exists) {
                return response()->json(['error' => 'Condition already exists.'], 422);
            }

            $condition = \App\Models\Condition::create([
                'tenant_id' => $user->tenant_id,
                'name' => $name,
            ]);

            ActivityLog::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'action' => 'create',
                'type' => 'condition',
                'description' => 'Created condition: ' . $condition->name,
            ]);

            return response()->json($condition, 201);
        } catch (\Throwable $e) {
            \Log::error('Failed to create condition', [
                'tenant_id' => $user->tenant_id,
                'name' => $name,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create condition.',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    // ==================== STORE LOCATIONS ====================

    public function locations(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $locations = \App\Models\Location::where('tenant_id', $tenantId)
            ->where('department', 'store')
            ->get();
        return response()->json($locations);
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'storeType' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $location = \App\Models\Location::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'name' => $request->name,
            'type' => $request->type ?? 'warehouse',
            'store_type' => $request->storeType ?? 'office',
            'capacity' => $request->capacity ?? 100,
            'department' => 'store',
            'description' => $request->description,
            'address' => $request->address,
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'create',
            'type' => 'location',
            'description' => 'Created store location: ' . $location->name,
        ]);

        return response()->json($location->loadCount('items'), 201);
    }

    public function updateLocation(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $location = \App\Models\Location::where('tenant_id', $user->tenant_id)
            ->where('department', 'store')
            ->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'storeType' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $location->update($request->only(['name', 'type', 'store_type', 'capacity', 'description', 'address']));

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'update',
            'type' => 'location',
            'description' => 'Updated store location: ' . $location->name,
        ]);

        return response()->json($location->loadCount('items'));
    }

    public function destroyLocation($id): JsonResponse
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $location = \App\Models\Location::where('tenant_id', $user->tenant_id)
            ->where('department', 'store')
            ->findOrFail($id);

        if ($location->items()->exists()) {
            return response()->json(['message' => 'Cannot delete location with items inside'], 422);
        }

        $name = $location->name;
        $location->delete();

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'delete',
            'type' => 'location',
            'description' => 'Deleted store location: ' . $name,
        ]);

        return response()->json(['message' => 'Location deleted']);
    }

    // ==================== STORE SUPPLIERS ====================

    public function suppliers(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $suppliers = \App\Models\Supplier::where('tenant_id', $tenantId)->get();
        return response()->json($suppliers);
    }

    public function storeSupplier(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $supplier = \App\Models\Supplier::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'name' => $request->name,
            'contact_person' => $request->contact ?? '',
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'create',
            'type' => 'supplier',
            'description' => 'Added store supplier: ' . $supplier->name,
        ]);

        return response()->json($supplier, 201);
    }

    public function updateSupplier(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $supplier = \App\Models\Supplier::where('tenant_id', $user->tenant_id)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'contact' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ]);

        $supplier->update($request->only(['name', 'contact_person', 'email', 'phone', 'address', 'notes']));

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'update',
            'type' => 'supplier',
            'description' => 'Updated store supplier: ' . $supplier->name,
        ]);

        return response()->json($supplier);
    }

    public function destroySupplier($id): JsonResponse
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $supplier = \App\Models\Supplier::where('tenant_id', $user->tenant_id)->findOrFail($id);
        $name = $supplier->name;
        $supplier->delete();

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'delete',
            'type' => 'supplier',
            'description' => 'Deleted store supplier: ' . $name,
        ]);

        return response()->json(['message' => 'Supplier deleted']);
    }

    // ==================== STORE SESSIONS/CALENDAR ====================

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

    public function quotations(Request $request): JsonResponse
    {
        return response()->json([]);
    }

    public function storeQuotation(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $quotation = $request->validate([
            'quotation_id' => 'required|string',
            'notes' => 'required|string',
            'contact_details' => 'required|string',
            'quotation_data' => 'required|array',
            'quotation_data.items' => 'required|array',
            'quotation_data.totalEstimatedCost' => 'required|numeric',
            'quotation_data.createdDate' => 'required|string',
            'quotation_data.createdBy' => 'required|string',
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'store_quotation',
            'type' => 'store_quotation',
            'description' => 'Created store quotation: ' . $quotation['quotation_id'],
            'metadata' => [
                'quotation_id' => $quotation['quotation_id'],
                'total_cost' => $quotation['quotation_data']['totalEstimatedCost'],
                'department' => 'Store',
            ]
        ]);

        return response()->json([
            'message' => 'Quotation created',
            'quotation' => $quotation,
        ], 201);
    }

    public function updateQuotation(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['message' => 'Quotation updated']);
    }

    public function destroyQuotation($id): JsonResponse
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['message' => 'Quotation deleted']);
    }
}
