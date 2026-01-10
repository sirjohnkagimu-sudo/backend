<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * ============================
     * REGISTER INSTITUTION (ADMIN)
     * ============================
     */
    public function register(Request $request)
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'centre_number'    => 'required|string|max:50|unique:schools,centre_number',
            'district'         => 'required|string|max:255',
            'adminName'        => 'required|string|max:255',
            'adminEmail'       => 'required|email|unique:users,email',
            'adminPhone'       => 'required|string|max:50',
            'password'         => 'required|string|min:6|confirmed',
        ]);

        $user = null;
        $school = null;

        \DB::transaction(function () use ($request, &$school, &$user) {
            // Create or get the school (tenant)
            $school = School::firstOrCreate(
                ['centre_number' => $request->centre_number],
                [
                    'id'       => Str::uuid(),
                    'name'     => $request->institution_name,
                    'district' => $request->district,
                    'admin_name'  => $request->adminName,
                    'admin_email' => $request->adminEmail,
                    'admin_phone' => $request->adminPhone,
                    'status'   => 'active',
                    'data' => [
                        'paymentMethods'    => $request->paymentMethods ?? [],
                        'mobileMoneyNumber' => $request->mobileMoneyNumber ?? null,
                        'bankAccount'       => $request->bankAccount ?? null,
                        'designation'       => $request->designation ?? null,
                        'customDesignation' => $request->customDesignation ?? null,
                    ],
                ]
            );

            // Create the admin user
            $user = User::firstOrCreate(
                ['email' => $request->adminEmail],
                [
                    'firstName'       => $request->adminName,
                    'lastName'        => '',
                    'phone'           => $request->adminPhone,
                    'password'        => Hash::make($request->password),
                    'tenant_id'       => $school->id,
                    'role_id'         => 1,
                    'is_school_admin' => true,
                ]
            );
        });

        return response()->json([
            'message' => 'Registration successful',
            'user'    => $user,
            'school'  => $school,
            'token'   => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    /**
     * =============
     * LOGIN USER
     * =============
     */
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|string']);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials']]);
        }

        if ($user->school && $user->school->status !== 'active') {
            return response()->json(['message' => 'Institution account is not active'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $this->authUserResponse($user),
            'token'   => $token,
        ]);
    }

    /**
     * ============================
     * LOGOUT
     * ============================
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * ============================
     * CREATE TENANT USER (SAFE)
     * ============================
     */
    public function createTenantUser(Request $request)
    {
        $currentUser = $request->user();

        if (!$currentUser->is_school_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName'  => 'nullable|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
        ]);

        $newUser = User::create([
            'firstName'       => $request->firstName,
            'lastName'        => $request->lastName,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'tenant_id'       => $currentUser->tenant_id,
            'role_id'         => $request->role_id,
            'is_school_admin' => false,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $newUser->load('role'),
        ], 201);
    }

    /**
     * ============================
     * UPDATE TENANT USER
     * ============================
     */
    public function updateTenantUser(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (!$currentUser->is_school_admin || $user->tenant_id !== $currentUser->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'firstName' => 'sometimes|required|string|max:255',
            'lastName'  => 'nullable|string|max:255',
            'email'     => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role_id'   => 'sometimes|required|exists:roles,id',
        ]);

        $user->update($request->only(['firstName', 'lastName', 'email', 'role_id']));

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->load('role'),
        ]);
    }

    /**
     * ============================
     * DELETE TENANT USER
     * ============================
     */
    public function deleteTenantUser(Request $request, User $user)
    {
        $currentUser = $request->user();

        if (
            !$currentUser->is_school_admin ||
            $user->tenant_id !== $currentUser->tenant_id ||
            $user->id === $currentUser->id
        ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * ============================
     * UPDATE OWN PROFILE
     * ============================
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'firstName' => 'sometimes|string|max:255',
            'lastName'  => 'sometimes|nullable|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $user->id,
            'phone'     => 'sometimes|nullable|string|max:50',
        ]);

        $user->update($request->only(['firstName', 'lastName', 'email', 'phone']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $this->authUserResponse($user),
        ]);
    }

    /**
     * ============================
     * GET TENANT USERS
     * ============================
     */
    public function getTenantUsers(Request $request)
    {
        $user = $request->user();

        if (!$user->is_school_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::where('tenant_id', $user->tenant_id)->with('role')->get();

        return response()->json(['users' => $users]);
    }

    /**
     * ============================
     * GET ALL USERS (ADMIN)
     * ============================
     */
    public function getAllUsers()
    {
        $users = User::with('school')->get();

        return response()->json(['users' => $users]);
    }

    /**
     * ============================
     * STANDARD AUTH RESPONSE
     * ============================
     */
    private function authUserResponse(User $user): array
    {
        return [
            'id'              => $user->id,
            'firstName'       => $user->firstName,
            'lastName'        => $user->lastName,
            'email'           => $user->email,
            'role_id'         => $user->role_id,
            'role'            => $user->role,
            'is_school_admin' => (bool) $user->is_school_admin,
            'tenant_id'       => $user->tenant_id,
            'accountType'     => 'institution',
            'school' => $user->school ? [
                'id'     => $user->school->id,
                'name'   => $user->school->name,
                'status' => $user->school->status,
            ] : null,
        ];
    }
}
