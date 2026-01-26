<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage suppliers.'], 403);
        }

        return response()->json(Supplier::where('tenant_id', $user->tenant_id)->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage suppliers.'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:suppliers,name,NULL,id,tenant_id,' . $user->tenant_id,
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'contactPerson' => 'nullable|string',
            'isActive' => 'nullable|boolean',
        ]);

        $supplier = Supplier::create([
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'website' => $request->website,
            'address' => $request->address,
            'contact_person' => $request->contactPerson,
            'is_active' => $request->isActive ?? true,
        ]);

        return response()->json($supplier, 201);
    }

    public function show(Supplier $supplier)
    {
        $user = request()->user();
       if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage suppliers.'], 403);
        }

        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage suppliers.'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:suppliers,name,' . $supplier->id . ',id,tenant_id,' . $user->tenant_id,
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'contactPerson' => 'nullable|string',
            'isActive' => 'nullable|boolean',
        ]);

        $supplier->update($request->only(['name', 'phone', 'email', 'website', 'address', 'contact_person', 'is_active']));
        return response()->json($supplier);
    }

    public function destroy(Supplier $supplier)
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1 || $supplier->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage suppliers.'], 403);
        }

        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted']);
    }
}
