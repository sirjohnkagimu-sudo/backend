<?php

namespace App\Http\Controllers;

use App\Models\Sports;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SportsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        session(['title' => 'Sports Items']);
        $sports = Sports::all();
        return view('sports.index', compact('sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sports.create');
    }

    /**
     * Get all sports items as JSON (API)
     */
    public function getSports(Request $request): JsonResponse
    {
        $query = Sports::query();

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
        $sports = $query->paginate($perPage);

        return response()->json([
            'data' => $sports->items(),
            'meta' => [
                'current_page' => $sports->currentPage(),
                'last_page' => $sports->lastPage(),
                'per_page' => $sports->perPage(),
                'total' => $sports->total(),
            ]
        ]);
    }

    /**
     * Get single sports item by ID
     */
    public function getSportsById($id): JsonResponse
    {
        $sports = Sports::findOrFail($id);
        return response()->json($sports);
    }

    /**
     * Get sports categories
     */
    public function getCategories(): JsonResponse
    {
        $categories = Sports::distinct()->pluck('category');
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:jerseys,board_games,indoor_games,balls',
            'avatar' => 'nullable|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'condition' => 'required|in:new,old',
            'price' => 'required|string|min:0',
            'discount' => 'nullable|string|min:0|max:' . $request->price,
            'desc' => 'nullable|string|max:1000',
        ]);

        $sports = new Sports();
        $sports->name = $request->name;
        $sports->category = $request->category;
        $sports->color = $request->color;
        $sports->brand = $request->brand;
        $sports->in_stock = $request->in_stock;
        $sports->condition = $request->condition;
        $sports->price = $request->price;
        $sports->discount = $request->discount;
        $sports->desc = $request->desc;

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('images/sports', 'public');
            $sports->avatar = $avatarPath;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath[] = $image->store('images/sports', 'public');
            }
            $sports->images = json_encode($imagePath);
        }

        $sports->save();

        return redirect()->route('index.sports')->with('success', 'Sports created successfully.');
    }

    /**
     * Store API - Create new sports item
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
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string|max:1000',
            'location_id' => 'nullable|integer',
        ]);

        $sports = new Sports();
        $sports->fill($validated);
        $sports->save();

        return response()->json($sports, 201);
    }

    /**
     * Update API - Update sports item
     */
    public function apiUpdate(Request $request, $id): JsonResponse
    {
        $sports = Sports::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'nullable|string|max:255',
            'category' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'min_quantity' => 'nullable|integer|min:0',
            'condition' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'desc' => 'nullable|string|max:1000',
            'location_id' => 'nullable|integer',
        ]);

        $sports->update($validated);

        return response()->json($sports);
    }

    /**
     * Delete API - Delete sports item
     */
    public function apiDestroy($id): JsonResponse
    {
        $sports = Sports::findOrFail($id);

        // Delete the avatar image
        if ($sports->avatar) {
            \Storage::delete('public/' . $sports->avatar);
        }

        // Delete the images
        if (isset($sports->images)) {
            foreach (json_decode($sports->images) as $image) {
                \Storage::delete('public/' . $image);
            }
        }

        $sports->delete();

        return response()->json(['message' => 'Sports item deleted successfully']);
    }

    /**
     * Get sports calendar sessions
     */
    public function getCalendar(): JsonResponse
    {
        // Return empty array for now - can be extended to use a separate sessions table
        return response()->json(['sessions' => []]);
    }

    /**
     * Store sports calendar session
     */
    public function storeCalendar(Request $request): JsonResponse
    {
        // Return success for now - can be extended to save to sessions table
        return response()->json(['message' => 'Session created', 'session' => []], 201);
    }

    /**
     * Update sports calendar session
     */
    public function updateCalendar(Request $request, $id): JsonResponse
    {
        // Return success for now
        return response()->json(['message' => 'Session updated']);
    }

    /**
     * Delete sports calendar session
     */
    public function destroyCalendar($id): JsonResponse
    {
        // Return success for now
        return response()->json(['message' => 'Session deleted']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sports $sports)
    {
        return view('sports.show', compact('sports'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sports $sports)
    {
        return view('sports.edit', compact('sports'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sports $sports)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:balls,jerseys,board_games,indoor_games',
            'avatar' => 'nullable|image|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|max:2048',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'in_stock' => 'nullable|integer|min:0',
            'condition' => 'required|in:new,old',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:' . $request->price,
            'desc' => 'nullable|string|max:1000',
        ]);

        $sports->update($request->all());

        return redirect()->route('sports.index')->with('success', 'Sports updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sports $sports)
    {
        // Delete the avatar image
        if ($sports->avatar) {
            \Storage::delete('public/' . $sports->avatar);
        }

        // Delete the images
        if (isset($sports->images)) {
            foreach (json_decode($sports->images) as $image) {
                \Storage::delete('public/' . $image);
            }
        }

        $sports->delete();

        return redirect()->route('sports.index')->with('success', 'Sports deleted successfully.');
    }
}
