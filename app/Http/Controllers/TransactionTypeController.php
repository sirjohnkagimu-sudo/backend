<?php

namespace App\Http\Controllers;

use App\Models\TransactionType;
use Illuminate\Http\Request;

class TransactionTypeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $types = TransactionType::where('tenant_id', $user->tenant_id)
            ->orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json($types);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:transaction_types,name,NULL,id,tenant_id,' . $user->tenant_id,
            'color' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:10',
        ]);

        $type = TransactionType::create([
            'tenant_id' => $user->tenant_id,
            'name' => $data['name'],
            'color' => $data['color'] ?? 'bg-gray-100 text-gray-800',
            'icon' => $data['icon'] ?? '📝',
            'is_default' => false,
        ]);

        return response()->json($type, 201);
    }

    public function update(Request $request, TransactionType $transactionType)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1 || $transactionType->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:transaction_types,name,' . $transactionType->id . ',id,tenant_id,' . $user->tenant_id,
            'color' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:10',
        ]);

        $transactionType->update($data);

        return response()->json($transactionType);
    }

    public function destroy(TransactionType $transactionType)
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1 || $transactionType->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($transactionType->is_default) {
            return response()->json(['error' => 'Cannot delete default transaction types'], 422);
        }

        $transactionType->delete();

        return response()->json(['message' => 'Transaction type deleted']);
    }
}
