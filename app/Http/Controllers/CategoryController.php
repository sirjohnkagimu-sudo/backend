<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage categories.'], 403);
        }

        if (!$user->tenant_id) {
            return response()->json(['error' => 'User does not have an associated tenant'], 400);
        }

        $categories = Category::where('tenant_id', $user->tenant_id)->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage categories.'], 403);
        }

        if (!$user->tenant_id) {
            return response()->json(['error' => 'User does not have an associated tenant'], 400);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name,NULL,id,tenant_id,' . $user->tenant_id,
        ]);

        $category = Category::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'create',
            'type'      => 'category',
            'description' => 'Created category: ' . $category->name,
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage categories.'], 403);
        }

        return response()->json($category);
    }

    public function update(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage categories.'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $category->id . ',id,tenant_id,' . $user->tenant_id,
        ]);

        $original = $category->getOriginal();
        $category->update($request->only(['name']));

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'update',
            'type'      => 'category',
            'description' => 'Updated category: ' . $category->name,
            'metadata'  => [
                ['field' => 'name', 'old_value' => $original['name'] ?? null, 'new_value' => $category->name],
            ],
        ]);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1 || $category->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage categories.'], 403);
        }

        $name = $category->name;
        $category->delete();

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'delete',
            'type'      => 'category',
            'description' => 'Deleted category: ' . $name,
        ]);

        return response()->json(['message' => 'Category deleted']);
    }
}
