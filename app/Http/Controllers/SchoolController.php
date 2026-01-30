<?php

namespace App\Http\Controllers;

use App\Mail\SchoolRegistrationConfirmation;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::all();
        return response()->json(['schools' => $schools]);
    }

    public function show(Request $request)
    {
        // Get the school for the authenticated user
        $user = $request->user();
        if (!$user || !$user->school) {
            return response()->json(['message' => 'No school associated with this account'], 404);
        }
        return response()->json(['school' => $user->school]);
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

        // Send confirmation email if admin_email is provided
        if ($school->admin_email) {
            try {
                Mail::to($school->admin_email)->send(new SchoolRegistrationConfirmation($school));
            } catch (\Exception $e) {
                // Log the error but don't fail the request
                \Log::error('Failed to send school registration confirmation email: ' . $e->getMessage());
            }
        }

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

    public function updateSchool(Request $request)
    {
        $user = $request->user();

        Log::info('SchoolController updateSchool called', [
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null,
            'is_school_admin' => $user ? $user->is_school_admin : null,
            'request_data' => $request->all()
        ]);

        if (!$user->school) {
            return response()->json(['message' => 'No school associated with this account'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|nullable|string|max:255',
            'county' => 'sometimes|nullable|string|max:255',
            'subcounty' => 'sometimes|nullable|string|max:255',
            'parish' => 'sometimes|nullable|string|max:255',
            'village' => 'sometimes|nullable|string|max:255',
            'admin_name' => 'sometimes|nullable|string|max:255',
            'admin_email' => 'sometimes|nullable|email',
            'admin_phone' => 'sometimes|nullable|string|max:50',
        ]);

        $user->school->update($request->only(['name', 'district', 'county', 'subcounty', 'parish', 'village', 'admin_name', 'admin_email', 'admin_phone']));

        Log::info('School updated successfully', [
            'school_id' => $user->school->id,
            'updated_data' => $request->only(['name', 'district', 'county', 'subcounty', 'parish', 'village', 'admin_name', 'admin_email', 'admin_phone'])
        ]);

        return response()->json([
            'message' => 'School information updated successfully',
            'school' => $user->school
        ]);
    }
}
