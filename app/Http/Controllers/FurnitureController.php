<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FurnitureController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum'])->only([
            'apiIndex', 'apiStore', 'apiShow', 'apiUpdate', 'apiDestroy',
            'bulkImport', 'getByLocation', 'lowStock'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        session(['title' => 'Furniture']);
        $furnitures = Furniture::all();
        return view('furnitures.index', compact('furnitures'));
    }

    /**
     * Get all furniture items as JSON (API)
     */
    public function getFurniture(Request $request): JsonResponse
    {
        $query = Furniture::query();

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        // Filter by condition
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by in_stock status
        if ($request->has('in_stock')) {
            if ($request->in_stock === 'low') {
                $query->where('in_stock', '>', 0)->where('in_stock', '<=', 5);
            } elseif ($request->in_stock === 'out') {
                $query->where('in_stock', 0);
            } elseif ($request->in_stock === 'available') {
                $query->where('in_stock', '>', 0);
            }
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $furnitures = $query->paginate($perPage);

        return response()->json([
            'data' => $furnitures->items(),
            'meta' => [
                'current_page' => $furnitures->currentPage(),
                'last_page' => $furnitures->lastPage(),
                'per_page' => $furnitures->perPage(),
                'total' => $furnitures->total(),
            ]
        ]);
    }

    /**
     * Get single furniture item by ID
     */
    public function getFurnitureById($id): JsonResponse
    {
        $furniture = Furniture::findOrFail($id);
        return response()->json($furniture);
    }

    /**
     * Get furniture categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = Furniture::distinct()->pluck('category');
        return response()->json($categories);
    }

    // ==================== API ROUTES (Similar to Laboratory Items) ====================

    /**
     * Get all furniture items (API)
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = Furniture::query();

        // Filter by category
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('desc', 'like', "%{$search}%");
            });
        }

        // Filter by condition
        if ($request->has('condition')) {
            $query->where('condition', $request->condition);
        }

        // Filter by in_stock status
        if ($request->has('in_stock')) {
            if ($request->in_stock === 'low') {
                $query->where('in_stock', '>', 0)->where('in_stock', '<=', 5);
            } elseif ($request->in_stock === 'out') {
                $query->where('in_stock', 0);
            } elseif ($request->in_stock === 'available') {
                $query->where('in_stock', '>', 0);
            }
        }

        // Sort by
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $furnitures = $query->paginate($perPage);

        return response()->json([
            'data' => $furnitures->items(),
            'meta' => [
                'current_page' => $furnitures->currentPage(),
                'last_page' => $furnitures->lastPage(),
                'per_page' => $furnitures->perPage(),
                'total' => $furnitures->total(),
            ]
        ]);
    }

    /**
     * Store a new furniture item (API)
     */
    public function apiStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
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

        $furniture = Furniture::create($validated);

        return response()->json($furniture, 201);
    }

    /**
     * Get a specific furniture item (API)
     */
    public function apiShow($id): JsonResponse
    {
        $furniture = Furniture::findOrFail($id);
        return response()->json($furniture);
    }

    /**
     * Update a furniture item (API)
     */
    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $furniture = Furniture::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'condition' => 'sometimes|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string',
            'location_id' => 'nullable|integer',
        ]);

        $furniture->update($validated);

        return response()->json($furniture);
    }

    /**
     * Delete a furniture item (API)
     */
    public function apiDestroy($id): JsonResponse
    {
        $furniture = Furniture::findOrFail($id);

        // Delete the avatar image
        if ($furniture->avatar) {
            \Storage::delete('public/' . $furniture->avatar);
        }

        // Delete the images
        if (isset($furniture->images)) {
            foreach (json_decode($furniture->images, true) ?? [] as $image) {
                \Storage::delete('public/' . $image);
            }
        }

        $furniture->delete();

        return response()->json(['message' => 'Furniture item deleted successfully']);
    }

    /**
     * Bulk import furniture items
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.category' => 'required|string|max:255',
            'items.*.color' => 'nullable|string|max:255',
            'items.*.brand' => 'nullable|string|max:255',
            'items.*.in_stock' => 'nullable|integer|min:0',
            'items.*.min_quantity' => 'nullable|integer|min:0',
            'items.*.condition' => 'required|in:new,old',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.desc' => 'nullable|string',
            'items.*.location_id' => 'nullable|integer',
        ]);

        $items = $validated['items'];
        $created = [];

        foreach ($items as $itemData) {
            $created[] = Furniture::create($itemData);
        }

        return response()->json([
            'message' => 'Successfully imported ' . count($created) . ' furniture items',
            'count' => count($created),
            'items' => $created,
        ], 201);
    }

    /**
     * Get furniture items by location
     */
    public function getByLocation($locationId): JsonResponse
    {
        $items = Furniture::where('location_id', $locationId)->get();
        return response()->json($items);
    }

    /**
     * Get low stock furniture items
     */
    public function lowStock(): JsonResponse
    {
        $items = Furniture::whereColumn('in_stock', '<=', 'min_quantity')
            ->orWhere(function ($query) {
                $query->where('in_stock', '<=', 5)
                      ->whereNotNull('min_quantity');
            })
            ->get();

        return response()->json($items);
    }

    /**
     * Get furniture calendar sessions
     */
    public function getCalendar(): JsonResponse
    {
        // Return empty array for now - can be extended to use a separate sessions table
        return response()->json(['sessions' => []]);
    }

    /**
     * Store furniture calendar session
     */
    public function storeCalendar(Request $request): JsonResponse
    {
        // Return success for now - can be extended to save to sessions table
        return response()->json(['message' => 'Session created', 'session' => []], 201);
    }

    /**
     * Update furniture calendar session
     */
    public function updateCalendar(Request $request, $id): JsonResponse
    {
        // Return success for now
        return response()->json(['message' => 'Session updated']);
    }

    /**
     * Delete furniture calendar session
     */
    public function destroyCalendar($id): JsonResponse
    {
        // Return success for now
        return response()->json(['message' => 'Session deleted']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('furnitures.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer',
            'condition' => 'required|in:new,old',
            'price' => 'required|string',
            'discount' => 'nullable|string',
            'desc' => 'nullable|string|max:1000',
        ]);


        $furniture = new Furniture();
        $furniture->name = $request->name;
        $furniture->category = $request->category;
        $furniture->color = $request->color;
        $furniture->brand = $request->brand;
        $furniture->in_stock = $request->in_stock;
        $furniture->condition = $request->condition;
        $furniture->price = $request->price;
        $furniture->discount = $request->discount;
        $furniture->desc = $request->desc;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('images/labs', 'public');
            $furniture->avatar = $avatarPath;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath[] = $image->store('images/labs', 'public');
            }
            $furniture->images = json_encode($imagePath);
        }

        $furniture->save();

        return redirect()->route('index.furnitures')->with('success', 'Furniture created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Furniture $furniture)
    {
        return view('furnitures.show', compact('furniture'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Furniture $furniture)
    {
        return view('furnitures.edit', compact('furniture'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Furniture $furniture)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer',
            'condition' => 'required|in:new,old',
            'price' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'desc' => 'nullable|string|max:1000',
        ]);

        $furniture->update($request->all());

        return redirect()->route('furnitures.index')->with('success', 'Furniture updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Furniture $furniture)
    {
         // Delete the avatar image
         if ($furniture->avatar) {
            \Storage::delete('public/' . $furniture->avatar);
        }

        // Delete the images
        if (isset($furniture->images)) {
            foreach (json_decode($furniture->images) as $image) {
                \Storage::delete('public/' . $image);
            }
        }


        $furniture->delete();

        return redirect()->route('furnitures.index')->with('success', 'Furniture deleted successfully.');
    }
}
