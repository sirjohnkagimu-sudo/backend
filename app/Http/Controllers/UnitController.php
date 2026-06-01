<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $units = Unit::where('tenant_id', $user->tenant_id)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json($units);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,NULL,id,tenant_id,' . $user->tenant_id,
            'abbreviation' => 'nullable|string|max:50',
        ]);

        $unit = Unit::create([
            'tenant_id' => $user->tenant_id,
            'name' => $data['name'],
            'abbreviation' => $data['abbreviation'] ?? null,
            'is_default' => false,
        ]);

        return response()->json($unit, 201);
    }

    public function update(Request $request, Unit $unit)
    {
        $user = $request->user();
        if (!$user || $unit->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:units,name,' . $unit->id . ',id,tenant_id,' . $user->tenant_id,
            'abbreviation' => 'nullable|string|max:50',
        ]);

        $unit->update($data);

        return response()->json($unit);
    }

    public function destroy(Unit $unit)
    {
        $user = request()->user();
        if (!$user || $unit->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($unit->is_default) {
            return response()->json(['error' => 'Cannot delete default units'], 422);
        }

        $unit->delete();

        return response()->json(['message' => 'Unit deleted']);
    }
}
