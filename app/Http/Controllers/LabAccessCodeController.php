<?php

namespace App\Http\Controllers;

use App\Models\LabAccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LabAccessCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage lab access codes.'], 403);
        }

        return response()->json(LabAccessCode::where('school_id', $user->tenant_id)->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage lab access codes.'], 403);
        }

        $request->validate([
            'user_name' => 'required|string',
            'email' => 'nullable|email',
            'role' => 'nullable|string',
            'permissions' => 'nullable|array',
            'access_code' => 'nullable|string|size:8|unique:lab_access_codes,access_code',
        ]);

        $accessCode = $request->access_code ?: Str::random(8); // Use provided or generate 8-character access code

        $labAccessCode = LabAccessCode::create([
            'school_id' => $user->tenant_id,
            'access_code' => $accessCode,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'role' => $request->role,
            'permissions' => $request->permissions ?? [],
            'created_by' => $user->id,
        ]);

        return response()->json($labAccessCode, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = request()->user();
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $accessCode = LabAccessCode::where('id', $id)
            ->where('school_id', $user->tenant_id)
            ->firstOrFail();
        return response()->json($accessCode);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $accessCode = LabAccessCode::where('id', $id)
            ->where('school_id', $user->tenant_id)
            ->firstOrFail();

        $request->validate([
            'user_name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'role' => 'sometimes|string',
            'permissions' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $accessCode->update($request->only(['user_name', 'email', 'role', 'permissions', 'is_active']));
        return response()->json($accessCode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = request()->user();
        if ($user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage lab access codes.'], 403);
        }

        $accessCode = LabAccessCode::where('id', $id)
            ->where('school_id', $user->tenant_id)
            ->firstOrFail();

        $accessCode->delete();
        return response()->json(['message' => 'Access code deleted']);
    }
}
