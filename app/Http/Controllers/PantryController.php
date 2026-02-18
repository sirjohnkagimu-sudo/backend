<?php

namespace App\Http\Controllers;

use App\Models\Pantry;
use App\Models\PantrySession;
use App\Models\MealPlan;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Supplier;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PantryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Get all pantries for the tenant
     */
    public function index(): JsonResponse
    {
        $pantries = Pantry::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($pantries);
    }

    /**
     * Store a new pantry
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:main-kitchen,cafeteria,staff-room,hostel',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:0',
            'operating_hours_open' => 'nullable|date_format:H:i',
            'operating_hours_close' => 'nullable|date_format:H:i',
            'theme_color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $pantry = Pantry::create($validated);

        return response()->json($pantry, 201);
    }

    /**
     * Get a specific pantry
     */
    public function show(Pantry $pantry): JsonResponse
    {
        if ($pantry->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this pantry');
        }

        return response()->json($pantry);
    }

    /**
     * Update a pantry
     */
    public function update(Request $request, Pantry $pantry): JsonResponse
    {
        if ($pantry->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this pantry');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:main-kitchen,cafeteria,staff-room,hostel',
            'location' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:0',
            'operating_hours_open' => 'nullable|date_format:H:i',
            'operating_hours_close' => 'nullable|date_format:H:i',
            'theme_color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $pantry->update($validated);

        return response()->json($pantry);
    }

    /**
     * Delete a pantry
     */
    public function destroy(Pantry $pantry): JsonResponse
    {
        if ($pantry->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this pantry');
        }

        $pantry->delete();

        return response()->json(['message' => 'Pantry deleted successfully']);
    }

    /**
     * Get pantry dashboard stats
     */
    public function dashboardStats(): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $totalItems = Item::where('tenant_id', $tenantId)->count();
        $lowStockItems = Item::where('tenant_id', $tenantId)
            ->whereColumn('quantity', '<=', 'min_quantity')
            ->count();
        $totalValue = Item::where('tenant_id', $tenantId)->sum('total_value');

        $today = now()->format('Y-m-d');
        $mealsToday = PantrySession::where('tenant_id', $tenantId)
            ->where('date', $today)
            ->whereIn('status', ['planned', 'ongoing', 'completed'])
            ->count();

        $recentTransactions = Transaction::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $paxServedToday = PantrySession::where('tenant_id', $tenantId)
            ->where('date', $today)
            ->where('status', 'completed')
            ->sum('actual_pax');

        return response()->json([
            'totalItems' => $totalItems,
            'lowStockItems' => $lowStockItems,
            'totalValue' => $totalValue,
            'totalPantries' => Pantry::where('tenant_id', $tenantId)->count(),
            'mealsToday' => $mealsToday,
            'recentTransactions' => $recentTransactions,
            'paxServedToday' => $paxServedToday,
        ]);
    }

    // ==================== SESSIONS ====================

    /**
     * Get all sessions for the pantry
     */
    public function sessions(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = PantrySession::where('tenant_id', $tenantId);

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $sessions = $query->orderBy('date')->orderBy('start_time')->get();

        return response()->json($sessions);
    }

    /**
     * Store a new session
     */
    public function storeSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pantry_id' => 'nullable|exists:pantries,id',
            'title' => 'required|string|max:255',
            'type' => 'required|in:breakfast,lunch,dinner,snack,special',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'expected_pax' => 'nullable|integer|min:0',
            'instructor' => 'nullable|string|max:255',
            'required_items' => 'nullable|array',
            'notes' => 'nullable|string',
            'menu' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['status'] = 'planned';

        $session = PantrySession::create($validated);

        return response()->json($session, 201);
    }

    /**
     * Update a session
     */
    public function updateSession(Request $request, PantrySession $session): JsonResponse
    {
        if ($session->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this session');
        }

        $validated = $request->validate([
            'pantry_id' => 'nullable|exists:pantries,id',
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:breakfast,lunch,dinner,snack,special',
            'description' => 'nullable|string',
            'date' => 'sometimes|date',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'expected_pax' => 'nullable|integer|min:0',
            'actual_pax' => 'nullable|integer|min:0',
            'instructor' => 'nullable|string|max:255',
            'required_items' => 'nullable|array',
            'status' => 'sometimes|in:planned,ongoing,completed,cancelled',
            'notes' => 'nullable|string',
            'menu' => 'nullable|string',
        ]);

        $session->update($validated);

        return response()->json($session);
    }

    /**
     * Delete a session
     */
    public function destroySession(PantrySession $session): JsonResponse
    {
        if ($session->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this session');
        }

        $session->delete();

        return response()->json(['message' => 'Session deleted successfully']);
    }

    // ==================== MEAL PLANS ====================

    /**
     * Get all meal plans
     */
    public function mealPlans(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = MealPlan::where('tenant_id', $tenantId);

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $mealPlans = $query->orderBy('date')->get();

        return response()->json($mealPlans);
    }

    /**
     * Store a new meal plan
     */
    public function storeMealPlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pantry_id' => 'nullable|exists:pantries,id',
            'day' => 'required|string|max:50',
            'date' => 'required|date',
            'breakfast' => 'nullable|array',
            'lunch' => 'nullable|array',
            'dinner' => 'nullable|array',
            'snacks' => 'nullable|array',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['prepared_by'] = auth()->user()->id;
        $validated['status'] = 'draft';

        $mealPlan = MealPlan::create($validated);

        return response()->json($mealPlan, 201);
    }

    /**
     * Update a meal plan
     */
    public function updateMealPlan(Request $request, MealPlan $mealPlan): JsonResponse
    {
        if ($mealPlan->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this meal plan');
        }

        $validated = $request->validate([
            'pantry_id' => 'nullable|exists:pantries,id',
            'day' => 'sometimes|string|max:50',
            'date' => 'sometimes|date',
            'breakfast' => 'nullable|array',
            'lunch' => 'nullable|array',
            'dinner' => 'nullable|array',
            'snacks' => 'nullable|array',
            'status' => 'sometimes|in:draft,approved,executed',
        ]);

        $mealPlan->update($validated);

        return response()->json($mealPlan);
    }

    /**
     * Approve a meal plan
     */
    public function approveMealPlan(MealPlan $mealPlan): JsonResponse
    {
        if ($mealPlan->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this meal plan');
        }

        $mealPlan->update([
            'status' => 'approved',
            'approved_by' => auth()->user()->id,
        ]);

        return response()->json($mealPlan);
    }

    /**
     * Delete a meal plan
     */
    public function destroyMealPlan(MealPlan $mealPlan): JsonResponse
    {
        if ($mealPlan->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this meal plan');
        }

        $mealPlan->delete();

        return response()->json(['message' => 'Meal plan deleted successfully']);
    }

    // ==================== PANTRY ITEMS (using existing Item model) ====================

    /**
     * Get pantry items
     */
    public function items(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Item::where('tenant_id', $tenantId);

        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $items = $query->with(['category', 'supplier', 'location'])->get();

        return response()->json($items);
    }

    /**
     * Get pantry transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $query = Transaction::where('tenant_id', $tenantId);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return response()->json($transactions);
    }

    /**
     * Get pantry reports
     */
    public function reports(Request $request): JsonResponse
    {
        $tenantId = auth()->user()->tenant_id;
        $type = $request->type ?? 'daily';

        if ($type === 'daily') {
            $date = $request->has('date') ? $request->date : now()->format('Y-m-d');

            $itemsAdded = Transaction::where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->where('type', 'purchase')
                ->count();

            $itemsConsumed = Transaction::where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->where('type', 'consumption')
                ->count();

            $itemsWasted = Transaction::where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->where('type', 'wastage')
                ->count();

            $mealsServed = PantrySession::where('tenant_id', $tenantId)
                ->where('date', $date)
                ->where('status', 'completed')
                ->count();

            $totalPaxServed = PantrySession::where('tenant_id', $tenantId)
                ->where('date', $date)
                ->where('status', 'completed')
                ->sum('actual_pax');

            $lowStockAlerts = Item::where('tenant_id', $tenantId)
                ->whereColumn('quantity', '<=', 'min_quantity')
                ->pluck('name')
                ->toArray();

            $totalTransactions = Transaction::where('tenant_id', $tenantId)
                ->whereDate('created_at', $date)
                ->count();

            $estimatedValue = Item::where('tenant_id', $tenantId)->sum('total_value');

            return response()->json([
                'date' => $date,
                'itemsAdded' => $itemsAdded,
                'itemsConsumed' => $itemsConsumed,
                'itemsWasted' => $itemsWasted,
                'mealsServed' => $mealsServed,
                'totalPaxServed' => $totalPaxServed,
                'lowStockAlerts' => $lowStockAlerts,
                'totalTransactions' => $totalTransactions,
                'estimatedValue' => $estimatedValue,
            ]);
        }

        return response()->json(['message' => 'Report type not implemented']);
    }

    /**
     * Get pantry suppliers
     */
    public function suppliers(): JsonResponse
    {
        $suppliers = Supplier::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($suppliers);
    }

    /**
     * Get pantry storage locations
     */
    public function storageLocations(): JsonResponse
    {
        $locations = Location::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($locations);
    }

    // ==================== PANTRY ITEMS CRUD (Similar to Laboratory Items) ====================

    /**
     * Get all pantry items
     */
    public function itemsIndex(): JsonResponse
    {
        $items = Item::with(['category', 'supplier', 'location'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('department', 'pantry')
            ->get();

        return response()->json($items);
    }

    /**
     * Store a new pantry item
     */
    public function itemsStore(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
        ]);

        $validated['tenant_id'] = $user->tenant_id;
        $validated['created_by'] = $user->id;
        $validated['department'] = 'pantry';

        $item = Item::create($validated);

        return response()->json($item->load(['category', 'supplier', 'location']), 201);
    }

    /**
     * Get a specific pantry item
     */
    public function itemsShow(Item $pantryItem): JsonResponse
    {
        if ($pantryItem->tenant_id !== auth()->user()->tenant_id || $pantryItem->department !== 'pantry') {
            abort(403, 'Unauthorized access to this item');
        }

        return response()->json($pantryItem->load(['category', 'supplier', 'location']));
    }

    /**
     * Update a pantry item
     */
    public function itemsUpdate(Request $request, Item $pantryItem): JsonResponse
    {
        if ($pantryItem->tenant_id !== auth()->user()->tenant_id || $pantryItem->department !== 'pantry') {
            abort(403, 'Unauthorized access to this item');
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'quantity' => 'sometimes|required|integer|min:0',
            'min_quantity' => 'sometimes|required|integer|min:0',
            'max_quantity' => 'nullable|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
        ]);

        $pantryItem->update($validated);

        return response()->json($pantryItem->load(['category', 'supplier', 'location']));
    }

    /**
     * Delete a pantry item
     */
    public function itemsDestroy(Item $pantryItem): JsonResponse
    {
        if ($pantryItem->tenant_id !== auth()->user()->tenant_id || $pantryItem->department !== 'pantry') {
            abort(403, 'Unauthorized access to this item');
        }

        $pantryItem->delete();

        return response()->json(['message' => 'Pantry item deleted successfully']);
    }

    /**
     * Bulk import pantry items
     */
    public function itemsBulkImport(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.category_id' => 'nullable|integer',
            'items.*.category' => 'nullable|string|max:255',
            'items.*.supplier_id' => 'nullable|integer',
            'items.*.location_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.min_quantity' => 'nullable|integer|min:0',
            'items.*.max_quantity' => 'nullable|integer|min:0',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.total_value' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        $items = $validated['items'];
        $created = [];

        foreach ($items as $itemData) {
            $itemData['tenant_id'] = $user->tenant_id;
            $itemData['created_by'] = $user->id;
            $itemData['department'] = 'pantry';
            $created[] = Item::create($itemData);
        }

        return response()->json([
            'message' => 'Successfully imported ' . count($created) . ' pantry items',
            'count' => count($created),
            'items' => $created,
        ], 201);
    }

    /**
     * Get pantry items by location
     */
    public function itemsGetByLocation($locationId): JsonResponse
    {
        $items = Item::where('location_id', $locationId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('department', 'pantry')
            ->with(['category', 'supplier', 'location'])
            ->get();

        return response()->json($items);
    }

    /**
     * Get low stock pantry items
     */
    public function itemsLowStock(): JsonResponse
    {
        $items = Item::whereColumn('quantity', '<=', 'min_quantity')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('department', 'pantry')
            ->with(['category', 'supplier', 'location'])
            ->get();

        return response()->json($items);
    }
}
