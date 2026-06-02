<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Notification;
use App\Models\ActivityLog;
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

        $item = Item::create($validated);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'create',
            'type' => 'item',
            'description' => 'Created new item: ' . $item->name,
        ]);

        return $item->load(['category', 'supplier', 'location']);
    }

    public function show(Item $item)
    {
        if ($item->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this item');
        }

        return $item->load(['category', 'supplier', 'location']);
    }

    public function update(Request $request, Item $item)
    {
        $user = $request->user();

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

        $oldQuantity = $item->quantity;
        $original = $item->getOriginal();
        $item->update($validated);
        $changedFields = array_keys($item->getChanges() ?? []);
        $diff = [];
        foreach ($changedFields as $field) {
            $diff[] = [
                'field' => $field,
                'old_value' => $original[$field] ?? null,
                'new_value' => $item->$field,
            ];
        }

        if (isset($validated['quantity']) && $validated['quantity'] <= $item->min_quantity && $oldQuantity > $item->min_quantity) {
            $this->createLowStockNotification($item);
        }

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'update',
            'type' => 'item',
            'description' => 'Updated item: ' . $item->name,
            'metadata' => ['fields' => $changedFields, 'diff' => $diff],
        ]);

        return $item->load(['category', 'supplier', 'location']);
    }

    public function destroy(Item $item)
    {
        $user = request()->user();

        if ($item->tenant_id !== $user->tenant_id) {
            abort(403, 'Unauthorized access to this item');
        }

        $name = $item->name;
        $item->delete();

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'delete',
            'type' => 'item',
            'description' => 'Deleted item: ' . $name,
        ]);

        return response()->json(['message' => 'Item deleted']);
    }

    public function lowStock()
    {
        return Item::whereColumn('quantity', '<=', 'min_quantity')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['category', 'supplier', 'location'])
            ->get();
    }

    public function getByLocation($locationId)
    {
        return Item::where('location_id', $locationId)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with(['category', 'supplier', 'location'])
            ->get();
    }

    public function bulkImport(Request $request)
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
            $created[] = Item::create($itemData);
        }

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => 'create',
            'type' => 'item',
            'description' => 'Bulk imported ' . count($created) . ' items',
        ]);

        return response()->json([
            'message' => 'Successfully imported ' . count($created) . ' items',
            'count' => count($created),
            'items' => $created,
        ], 201);
    }

    private function createLowStockNotification(Item $item)
    {
        $existing = Notification::where('type', 'low_stock')
            ->where('related_item', $item->name)
            ->where('is_ignored', false)
            ->first();

        if (!$existing) {
            Notification::create([
                'user_id' => null,
                'type' => 'low_stock',
                'title' => 'Low Stock Alert',
                'message' => "{$item->name} - Only {$item->quantity} items left in stock",
                'details' => 'Consider reordering soon',
                'is_read' => false,
                'is_ignored' => false,
                'timestamp' => now(),
                'priority' => 'medium',
                'related_item' => $item->name
            ]);
        }
    }
}