<?php

namespace App\Http\Controllers;

use App\Models\Pantry;
use App\Models\PantrySession;
use App\Models\MealPlan;
use App\Models\Transaction;
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

        if ($request->date) $query->where('date', $request->date);
        if ($request->status) $query->where('status', $request->status);
        if ($request->type) $query->where('type', $request->type);

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

        return response()->json(
            PantrySession::create($validated),
            201
        );
    }

    public function updateSession(Request $request, PantrySession $session): JsonResponse
    {
        $this->authorizeTenant($session);
        $session->update($request->all());

        return response()->json($session);
    }

    public function destroySession(PantrySession $session): JsonResponse
    {
        $this->authorizeTenant($session);
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

        return response()->json([
            'transactions' => Transaction::where('tenant_id', $tenantId)
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

        return response()->json($pantryItem);
    }

    public function itemsDestroy(Pantry $pantryItem): JsonResponse
    {
        $this->authorizeTenant($pantryItem);
        $pantryItem->delete();

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