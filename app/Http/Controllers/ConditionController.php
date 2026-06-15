<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ConditionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([]);
    }

    public function store(): JsonResponse
    {
        return response()->json([], 201);
    }

    public function show($id): JsonResponse
    {
        return response()->json([]);
    }

    public function update($id): JsonResponse
    {
        return response()->json([]);
    }

    public function destroy($id): JsonResponse
    {
        return response()->json(null, 204);
    }
}
