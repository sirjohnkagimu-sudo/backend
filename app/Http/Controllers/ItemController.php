<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    public function index()
    {
        return Item::with(['category', 'supplier', 'location'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'quantity' => 'required|integer|min:0',
            'min_quantity' => 'required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $validated['tenant_id'] = $user->tenant_id;

        return Item::create($validated);
    }

    public function show(Item $item)
    {
        // Ensure the item belongs to the authenticated user's tenant
        if ($item->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this item');
        }

        return $item->load(['category', 'supplier', 'location']);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer',
            'location_id' => 'nullable|integer',
            'quantity' => 'sometimes|required|integer|min:0',
            'min_quantity' => 'sometimes|required|integer|min:0',
            'expiry_date' => 'nullable|date',
            'unit' => 'nullable|string|max:50',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $item->update($validated);

        return $item->load(['category', 'supplier', 'location']);
    }

    public function destroy(Item $item)
    {
        // Ensure the item belongs to the authenticated user's tenant
        if ($item->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this item');
        }

        $item->delete();

        return response()->json(['message' => 'Item deleted']);
    }

    public function lowStock()
    {
        return Item::whereColumn('quantity', '<=', 'min_quantity')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['category', 'supplier', 'location'])
            ->get();
    }
}
