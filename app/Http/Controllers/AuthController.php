<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * ============================
     * REGISTER INSTITUTION (ADMIN)
     * ============================
     */


    public function register(Request $request)
    {
        // 1️⃣ Validate the form data
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

        // 2️⃣ Wrap creation in a transaction
        \DB::transaction(function () use ($request, &$school, &$user) {
            // 3️⃣ Create the school (tenant)
            $school = School::create([
                'id'            => \Illuminate\Support\Str::uuid(),
                'name'          => $request->institution_name,
                'centre_number' => $request->centre_number,
                'district'      => $request->district,
                'admin_name'    => $request->adminName,
                'admin_email'   => $request->adminEmail,
                'admin_phone'   => $request->adminPhone,
                'status'        => 'active',
                'data' => [
                    'paymentMethods'   => $request->paymentMethods ?? [],
                    'mobileMoneyNumber'=> $request->mobileMoneyNumber ?? null,
                    'bankAccount'      => $request->bankAccount ?? null,
                    'designation'      => $request->designation ?? null,
                    'customDesignation'=> $request->customDesignation ?? null,
                ],
            ]);

            // 4️⃣ Create the admin user
            $user = User::create([
                'firstName'       => $request->adminName,
                'lastName'        => '', // optional
                'email'           => $request->adminEmail,
                'phone'           => $request->adminPhone,
                'password'        => \Hash::make($request->password),
                'tenant_id'       => $school->id,
                'role_id'         => 1, // Admin role
                'is_school_admin' => true,
            ]);
        });

        // 5️⃣ Return a clean response with token
        return response()->json([
            'message' => 'Registration successful',
            'user'    => $user,
            'school'  => $school,
            'token'   => $user->createToken('API Token')->plainTextToken,
        ]);
    }




    /**
     * ============
     * LOGIN USER
     * ============
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Check if passcode is provided (for teacher/lab login)
        if ($request->has('passcode')) {
            return $this->loginWithPasscode($request);
        }

        // Regular password login
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
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

    private function loginWithPasscode(Request $request)
    {
        $request->validate([
            'passcode' => 'required|string',
        ]);

        // Find the access code
        $accessCode = \App\Models\LabAccessCode::where('access_code', $request->passcode)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with(['creator', 'school'])
            ->first();

        if (!$accessCode) {
            throw ValidationException::withMessages([
                'passcode' => ['Invalid access code'],
            ]);
        }

        // Check if the email matches the creator's email (admin's email)
        if ($accessCode->creator->email !== $request->email) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        if ($accessCode->school->status !== 'active') {
            return response()->json(['message' => 'Institution account is not active'], 403);
        }

        // Create a virtual user response for the access code
        $token = $accessCode->creator->createToken('auth_token')->plainTextToken; // Use creator's token for now

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $accessCode->id,
                'firstName' => $accessCode->user_name,
                'lastName' => '',
                'email' => $request->email,
                'role_id' => 2, // Assuming teacher role
                'role' => $accessCode->role,
                'is_school_admin' => false,
                'tenant_id' => $accessCode->school_id,
                'accountType' => 'teacher',
                'permissions' => $accessCode->permissions,
                'access_code_id' => $accessCode->id,
                'school_id' => $accessCode->school_id,
                'school' => [
                    'id' => $accessCode->school->id,
                    'name' => $accessCode->school->name,
                    'status' => $accessCode->school->status,
                ],
            ],
            'token' => $token,
        ]);
    }

    /**
     * ============
     * LOGOUT
     * ============
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
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

        return response()->json([
            'users' => $users->map(function ($u) {
                return [
                    'id' => $u->id,
                    'firstName' => $u->firstName,
                    'lastName' => $u->lastName,
                    'email' => $u->email,
                    'role_id' => $u->role_id,
                    'role' => $u->role ? $u->role->name : null,
                    'is_school_admin' => $u->is_school_admin,
                ];
            }),
        ]);
    }

    /**
     * ============================
     * CREATE TENANT USER
     * ============================
     */
    public function createTenantUser(Request $request)
    {
        $user = $request->user();
        if (!$user->is_school_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $newUser = User::create([
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $user->tenant_id,
            'role_id' => $request->role_id,
            'is_school_admin' => false,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $newUser->load('role'),
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
            'lastName' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role_id' => 'sometimes|required|exists:roles,id',
        ]);

        $user->update($request->only(['firstName', 'lastName', 'email', 'role_id']));

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load('role'),
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
        if (!$currentUser->is_school_admin || $user->tenant_id !== $currentUser->tenant_id || $user->id === $currentUser->id) {
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
            'lastName' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|nullable|string|max:50',
        ]);

        $user->update($request->only(['firstName', 'lastName', 'email', 'phone']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $this->authUserResponse($user),
        ]);
    }

    /**
     * ============================
     * GET ALL USERS
     * ============================
     */
    public function getAllUsers()
    {
        $users = User::with('school')->get();

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'id'              => $user->id,
                    'firstName'       => $user->firstName,
                    'lastName'        => $user->lastName,
                    'email'           => $user->email,
                    'role_id'         => $user->role_id,
                    'is_school_admin' => (bool) $user->is_school_admin,
                    'tenant_id'       => $user->tenant_id,
                    'accountType'     => 'institution',
                    'school' => $user->school ? [
                        'id'     => $user->school->id,
                        'name'   => $user->school->name,
                        'status' => $user->school->status,
                    ] : null,
                ];
            }),
        ]);
    }

    /**
     * ============================
     * STANDARD AUTH RESPONSE
     * ============================
     */
    /**
 * Standard auth response including full school data
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
