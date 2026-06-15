<?php

namespace App\Http\Controllers;

use App\Models\Pantry;
use App\Models\PantrySession;
use App\Models\MealPlan;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PantryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    // ==================== PANTRIES ====================

    public function index(): JsonResponse
    {
        return response()->json(
            Pantry::where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('name')
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'supplier_email' => 'nullable|email|max:255',
            'supplier_phone' => 'nullable|string|max:50',
            'quantity' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'min_quantity' => 'nullable|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        return response()->json(
            Pantry::create($validated),
            201
        );
    }

    public function show(Pantry $pantry): JsonResponse
    {
        $this->authorizeTenant($pantry);
        return response()->json($pantry);
    }

    public function update(Request $request, Pantry $pantry): JsonResponse
    {
        $this->authorizeTenant($pantry);

        $pantry->update($request->all());

        return response()->json($pantry);
    }

    public function destroy(Pantry $pantry): JsonResponse
    {
        $this->authorizeTenant($pantry);
        $pantry->delete();

        return response()->json(['message' => 'Pantry deleted']);
    }

    // ==================== DASHBOARD ====================

    public function dashboardStats(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        return response()->json([
            'totalItems' => Pantry::where('tenant_id', $tenantId)->count(),
            'lowStockItems' => Pantry::where('tenant_id', $tenantId)
                ->whereColumn('quantity', '<=', 'min_quantity')
                ->count(),
            'totalValue' => Pantry::where('tenant_id', $tenantId)->sum('total_value'),
        ]);
    }

    // ==================== SESSIONS ====================

    public function sessions(Request $request): JsonResponse
    {
        $query = PantrySession::where('tenant_id', auth()->user()->tenant_id);

        if ($request->filled('date')) $query->where('date', $request->date);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('start_date')) $query->where('date', '>=', $request->start_date);
        if ($request->filled('end_date')) $query->where('date', '<=', $request->end_date);

        return response()->json($query->orderBy('date')->get());
    }

    public function storeSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pantry_id' => 'nullable|integer',
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'expected_pax' => 'nullable|integer',
            'instructor' => 'nullable|string',
            'required_items' => 'nullable|array',
            'notes' => 'nullable|string',
            'menu' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['status'] = 'planned';

        $session = PantrySession::create($validated);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'created pantry session',
            'type' => 'pantry_session_create',
            'description' => 'Created pantry session: ' . $session->title . ' on ' . $session->date,
            'metadata' => [
                'pantry_session_id' => $session->id,
                'session_title' => $session->title,
                'session_date' => $session->date,
                'department' => 'Pantry',
            ],
        ]);

        return response()->json($session, 201);
    }

    public function updateSession(Request $request, PantrySession $session): JsonResponse
    {
        $this->authorizeTenant($session);
        $session->update($request->all());

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'updated pantry session',
            'type' => 'pantry_session_update',
            'description' => 'Updated pantry session: ' . $session->title . ' on ' . $session->date,
            'metadata' => [
                'pantry_session_id' => $session->id,
                'session_title' => $session->title,
                'session_date' => $session->date,
                'department' => 'Pantry',
            ],
        ]);

        return response()->json($session);
    }

    public function destroySession(PantrySession $session): JsonResponse
    {
        $this->authorizeTenant($session);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'deleted pantry session',
            'type' => 'pantry_session_delete',
            'description' => 'Deleted pantry session: ' . $session->title . ' on ' . $session->date,
            'metadata' => [
                'pantry_session_id' => $session->id,
                'session_title' => $session->title,
                'session_date' => $session->date,
                'department' => 'Pantry',
            ],
        ]);

        $session->delete();

        return response()->json(['message' => 'Session deleted']);
    }

    // ==================== MEAL PLANS ====================

    public function mealPlans(): JsonResponse
    {
        return response()->json(
            MealPlan::where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('date')
                ->get()
        );
    }

    public function storeMealPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pantry_id' => 'nullable|integer',
            'day' => 'required|string',
            'date' => 'required|date',
            'breakfast' => 'nullable|array',
            'lunch' => 'nullable|array',
            'dinner' => 'nullable|array',
            'snacks' => 'nullable|array',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['status'] = 'draft';

        return response()->json(MealPlan::create($validated), 201);
    }

    // ==================== PANTRY ITEMS (FIXED - NO department) ====================

    public function itemsIndex(): JsonResponse
    {
        return response()->json(
            Pantry::where('tenant_id', auth()->user()->tenant_id)
                ->orderBy('name')
                ->get()
        );
    }

    public function transactions(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $pantryItemIds = Pantry::where('tenant_id', $tenantId)->pluck('id');

        return response()->json([
            'transactions' => Transaction::where('tenant_id', $tenantId)
                ->whereIn('item_id', $pantryItemIds)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($tx) => [
                    'id' => $tx->id,
                    'type' => $tx->type,
                    'quantity' => $tx->quantity,
                    'item' => $tx->item ? ['name' => $tx->item->name, 'department' => 'Pantry'] : null,
                    'created_at' => $tx->created_at,
                    'meal_served' => $tx->meal_served ?? null,
                    'number_served' => $tx->number_served ?? null,
                ]),
        ]);
    }

    public function storeTransaction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:pantries,id',
            'type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'approved_by' => 'nullable|string',
            'created_by' => 'nullable|string',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'meal_served' => 'nullable|string',
            'number_served' => 'nullable|integer',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $transaction = Transaction::create($validated);

        return response()->json($transaction->load('item'), 201);
    }

    public function itemsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        return response()->json(
            Pantry::create($validated),
            201
        );
    }

    public function itemsShow(Pantry $pantryItem): JsonResponse
    {
        $this->authorizeTenant($pantryItem);
        return response()->json($pantryItem);
    }

    public function itemsUpdate(Request $request, Pantry $pantryItem): JsonResponse
    {
        $this->authorizeTenant($pantryItem);
        
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'quantity' => 'sometimes|required|integer|min:0',
            'min_quantity' => 'sometimes|required|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
        ]);
        
        $pantryItem->update($validated);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'updated pantry item',
            'type' => 'pantry_item_update',
            'description' => 'Updated pantry item: ' . $pantryItem->name,
            'metadata' => [
                'pantry_item_id' => $pantryItem->id,
                'pantry_item_name' => $pantryItem->name,
                'department' => 'Pantry',
            ],
        ]);

        return response()->json($pantryItem);
    }

    public function itemsDestroy(Pantry $pantryItem): JsonResponse
    {
        $this->authorizeTenant($pantryItem);

        ActivityLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'deleted pantry item',
            'type' => 'pantry_item_delete',
            'description' => 'Deleted pantry item: ' . $pantryItem->name,
            'metadata' => [
                'pantry_item_id' => $pantryItem->id,
                'pantry_item_name' => $pantryItem->name,
                'department' => 'Pantry',
            ],
        ]);

        $pantryItem->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function reports(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $items = Pantry::where('tenant_id', $tenantId)->get();

        $totalItems = $items->count();
        $lowStockItems = $items->filter(fn ($item) => $item->quantity <= $item->min_quantity)->values();
        $totalValue = (float) $items->sum(fn ($item) => ($item->quantity ?? 0) * ($item->unit_cost ?? 0));
        $overStockedItems = $items->filter(fn ($item) => $item->max_quantity && ($item->quantity ?? 0) > $item->max_quantity)->values();

        $topConsumedItems = $items->map(fn ($item) => [
            'name' => $item->name,
            'quantity' => $item->quantity ?? 0,
            'cost' => ($item->quantity ?? 0) * ($item->unit_cost ?? 0),
        ])
            ->sortByDesc('quantity')
            ->take(5)
            ->values();

        return response()->json([
            'totalMealsServed' => 0,
            'totalPaxServed' => 0,
            'totalItemsConsumed' => (int) $items->sum('quantity'),
            'totalItemsPurchased' => 0,
            'totalWastage' => 0,
            'totalCost' => $totalValue,
            'avgCostPerPax' => 0,
            'topConsumedItems' => $topConsumedItems,
            'lowStockItems' => $lowStockItems->map(fn ($item) => [
                'name' => $item->name,
                'current' => $item->quantity ?? 0,
                'minThreshold' => $item->min_quantity ?? 0,
            ])->values(),
        ]);
    }

    public function storageLocations(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        return response()->json(
            Location::where('tenant_id', $tenantId)
                ->where(function ($query) {
                    $query->where('type', 'pantry')
                        ->orWhereNull('type');
                })
                ->orderBy('name')
                ->get()
        );
    }

    public function storeStorageLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['type'] = $validated['type'] ?? 'pantry';
        $validated['department'] = $validated['department'] ?? 'pantry';

        return response()->json(Location::create($validated), 201);
    }

    public function updateStorageLocation(Request $request, Location $storageLocation): JsonResponse
    {
        if ($storageLocation->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $storageLocation->update($validated);

        return response()->json($storageLocation);
    }

    public function destroyStorageLocation(Location $storageLocation): JsonResponse
    {
        if ($storageLocation->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized');
        }

        $storageLocation->delete();

        return response()->json(['message' => 'Deleted']);
    }

    // ==================== HELPERS ====================

    private function authorizeTenant($model)
    {
        if ($model->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized');
        }
    }
}