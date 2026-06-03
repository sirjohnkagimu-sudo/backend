<?php

namespace App\Http\Controllers;

use App\Models\DepartmentAccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentAccessCodeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $codes = DepartmentAccessCode::where('school_id', $user->tenant_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $codes]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'access_code' => 'required|string|max:255',
            'user_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'role' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'department' => 'required|string|in:Laboratory,Pantry,Furniture,Sports,Sickbay,Library',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $code = DepartmentAccessCode::create([
            'school_id' => $user->tenant_id,
            'access_code' => $request->access_code,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'department' => $request->department,
            'created_by' => $user->id,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json(['data' => $code], 201);
    }

    public function show(Request $request, DepartmentAccessCode $departmentAccessCode)
    {
        $user = $request->user();

        if ($departmentAccessCode->school_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $departmentAccessCode]);
    }

    public function update(Request $request, DepartmentAccessCode $departmentAccessCode)
    {
        $user = $request->user();

        if ($departmentAccessCode->school_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'access_code' => 'sometimes|required|string|max:255',
            'user_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'role' => 'sometimes|required|string|max:255',
            'permissions' => 'nullable|array',
            'department' => 'sometimes|required|string|in:Laboratory,Pantry,Furniture,Sports,Sickbay,Library',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $departmentAccessCode->update($request->only([
            'access_code',
            'user_name',
            'email',
            'role',
            'permissions',
            'department',
            'expires_at',
            'is_active',
        ]));

        return response()->json(['data' => $departmentAccessCode]);
    }

    public function destroy(Request $request, DepartmentAccessCode $departmentAccessCode)
    {
        $user = $request->user();

        if ($departmentAccessCode->school_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $departmentAccessCode->delete();

        return response()->json(null, 204);
    }
}
