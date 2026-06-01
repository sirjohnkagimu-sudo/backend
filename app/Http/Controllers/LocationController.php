<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Location::withCount('items as current_usage')->where('tenant_id', $user->tenant_id);

        if ($request->filled('labType')) {
            $query->where('lab_type', $request->labType);
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage locations.'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,NULL,id,tenant_id,' . $user->tenant_id,
        ]);

        $location = Location::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'name' => $data['name'],
            'type' => $request->type ?? 'shelf',
            'lab_type' => $request->labType ?? 'chemistry',
            'capacity' => $request->capacity ?? 100,
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'create',
            'type'      => 'location',
            'description' => 'Created storage location: ' . $location->name,
        ]);

        return response()->json($location->loadCount('items'), 201);
    }

    public function show(Location $location)
    {
        $user = request()->user();
        return response()->json($location);
    }

    public function update(Request $request, Location $location)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage locations.'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:locations,name,' . $location->id . ',id,tenant_id,' . $user->tenant_id,
            'type' => 'sometimes|required|string|max:255',
            'labType' => 'sometimes|required|in:chemistry,physics,biology,agriculture',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        $updates = array_intersect_key($validated, array_flip(['name', 'type', 'lab_type', 'capacity']));
        $location->update($updates);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'update',
            'type'      => 'location',
            'description' => 'Updated storage location: ' . $location->name,
            'metadata'  => $updates,
        ]);

        return response()->json($location->loadCount('items'));
    }

    public function destroy(Location $location)
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage locations.'], 403);
        }

        if ($location->items()->exists()) {
            return response()->json([
                'message' => 'Cannot delete location with items inside'
            ], 422);
        }

        $name = $location->name;
        $location->delete();

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'delete',
            'type'      => 'location',
            'description' => 'Deleted storage location: ' . $name,
        ]);

        return response()->json(['message' => 'Location deleted']);
    }
}
