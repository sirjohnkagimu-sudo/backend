<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::all();
        return response()->json(['schools' => $schools]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'centre_number' => 'required|string|unique:schools,centre_number',
            'district' => 'nullable|string',
            'subcounty' => 'nullable|string',
            'parish' => 'nullable|string',
            'village' => 'nullable|string',
            'admin_name' => 'nullable|string',
            'admin_email' => 'nullable|email',
            'admin_phone' => 'nullable|string',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        // Create the school
        $school = School::create($request->all());

        return response()->json([
            'message' => 'School created successfully',
            'school' => $school
        ], 201);
    }

    public function update(Request $request, School $school)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $school->update(['status' => $request->status]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'School status updated successfully.', 'school' => $school]);
        }

        return redirect()->back()->with('success', 'School status updated successfully.');
    }
}
