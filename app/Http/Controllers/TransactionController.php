<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $query = Transaction::where('tenant_id', $user->tenant_id)->with('item');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1 || !$user->tenant_id) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage transactions.'], 403);
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'approved_by' => 'required|string',
            'created_by' => 'required|string',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
        ]);

        $validated['tenant_id'] = $user->tenant_id;

        $transaction = Transaction::create($validated);

        return response()->json($transaction->load('item'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $user = request()->user();
        if (!$user || $transaction->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json($transaction->load('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        if (!$user || $user->role_id !== 1 || $transaction->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage transactions.'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|string|max:255',
            'quantity' => 'sometimes|integer|min:1',
            'reason' => 'sometimes|string',
            'approved_by' => 'sometimes|string',
            'notes' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'update_history' => 'sometimes|array',
        ]);

        $transaction->update($validated);

        return response()->json($transaction->load('item'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $user = request()->user();
        if (!$user || $user->role_id !== 1 || $transaction->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Unauthorized. Only school administrators can manage transactions.'], 403);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted']);
    }
}
