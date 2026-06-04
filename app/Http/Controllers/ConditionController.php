<?php

namespace App\Http\Controllers;

use App\Models\Condition;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConditionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage conditions.'], 403);
        }

        if (!$user->tenant_id) {
            return response()->json(['error' => 'User does not have an associated tenant'], 400);
        }

        $conditions = Condition::where('tenant_id', $user->tenant_id)->get();
        return response()->json($conditions);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage conditions.'], 403);
        }

        if (!$user->tenant_id) {
            return response()->json(['error' => 'User does not have an associated tenant'], 400);
        }

        $request->validate([
            'name' => 'required|string|unique:conditions,name,NULL,id,tenant_id,' . $user->tenant_id,
        ]);

        $condition = Condition::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
        ]);

        ActivityLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id'   => $user->id,
            'action'    => 'create',
            'type'      => 'condition',
            'description' => 'Created condition: ' . $condition->name,
        ]);

        return response()->json($condition, 201);
    }
}
