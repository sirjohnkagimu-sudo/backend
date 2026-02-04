<?php

namespace App\Http\Controllers;

use App\Models\TeacherPasscode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeacherPasscodeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return TeacherPasscode::where('tenant_id', auth()->user()->tenant_id)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passcode' => 'required|string|unique:teacher_passcodes|max:10',
            'teacher_name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        $passcode = TeacherPasscode::create($validated);

        return response()->json($passcode, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherPasscode $teacherPasscode): JsonResponse
    {
        if ($teacherPasscode->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this passcode');
        }

        return response()->json($teacherPasscode);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeacherPasscode $teacherPasscode): JsonResponse
    {
        if ($teacherPasscode->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this passcode');
        }

        $validated = $request->validate([
            'passcode' => 'sometimes|string|max:10',
            'teacher_name' => 'sometimes|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $teacherPasscode->update($validated);

        return response()->json($teacherPasscode);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherPasscode $teacherPasscode): JsonResponse
    {
        if ($teacherPasscode->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access to this passcode');
        }

        $teacherPasscode->delete();

        return response()->json(['message' => 'Passcode deleted successfully']);
    }

    /**
     * Validate a passcode
     */
    public function validatePasscode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passcode' => 'required|string',
        ]);

        $passcode = TeacherPasscode::where('passcode', $validated['passcode'])
            ->where('is_active', true)
            ->first();

        if ($passcode) {
            return response()->json([
                'valid' => true,
                'teacher_name' => $passcode->teacher_name,
            ]);
        }

        return response()->json([
            'valid' => false,
            'message' => 'Invalid or inactive passcode',
        ], 422);
    }
}
