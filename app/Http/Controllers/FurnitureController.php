<?php

namespace App\Http\Controllers;

use App\Models\Furniture;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FurnitureController extends Controller
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
