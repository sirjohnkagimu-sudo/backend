<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\School;
use App\Models\LabAccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;

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
            // Check if school with centre_number already exists
            $existingSchool = School::where('centre_number', $request->centre_number)->first();
            if ($existingSchool) {
                throw ValidationException::withMessages(['centre_number' => ['Centre number already exists']]);
            }

            // Create the school (tenant)
            $school = School::create([
                'name'     => $request->institution_name,
                'centre_number' => $request->centre_number,
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
            ]);

            // Check if user with email already exists
            $existingUser = User::where('email', $request->adminEmail)->first();
            if ($existingUser) {
                throw ValidationException::withMessages(['adminEmail' => ['Email already exists']]);
            }

            // Create the admin user
            $user = User::create([
                'firstName'       => $request->adminName,
                'lastName'        => $request->lastName,
                'email'           => $request->adminEmail,
                'phone'           => $request->adminPhone,
                'password'        => Hash::make($request->password),
                'tenant_id'       => $school->id,
                'role_id'         => 1,
                'is_school_admin' => true,
            ]);
        });

        // Send welcome email
        Mail::to($school->admin_email)->send(new \App\Mail\SchoolRegistrationConfirmation($school));

        return response()->json([
            'message' => 'Registration successful',
            'user'    => $user,
            'school'  => $school,
            'token'   => $user->createToken('API Token')->plainTextToken,
        ]);
    }

    /**
     * =============
     * ADMIN LOGIN
     * =============
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials']]);
        }

        if (!$user->is_school_admin) {
            throw ValidationException::withMessages(['email' => ['Unauthorized. Admin access required.']]);
        }

        if ($user->school && $user->school->status !== 'active') {
            return response()->json(['message' => 'Institution account is not active'], 403);
        }

        // Update last login
        $user->update(['last_login' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful',
            'user'    => $this->authUserResponse($user),
            'token'   => $token,
        ]);
    }

    /**
     * =============
     * TENANT USER LOGIN WITH ACCESS CODE
     * =============
     */
    public function tenantLogin(Request $request)
    {
        $request->validate([
            'admin_email' => 'required|email',
            'access_code' => 'required|string',
        ]);

        // Find the admin user
        $admin = User::where('email', $request->admin_email)->where('is_school_admin', true)->first();

        if (!$admin) {
            throw ValidationException::withMessages(['admin_email' => ['Admin not found or not authorized']]);
        }

        // Check if school is active
        if ($admin->school && $admin->school->status !== 'active') {
            return response()->json(['message' => 'Institution account is not active'], 403);
        }

        // Find active access code for this school
        $accessCode = LabAccessCode::where('school_id', $admin->tenant_id)
            ->where('access_code', $request->access_code)
            ->active()
            ->first();

        if (!$accessCode) {
            throw ValidationException::withMessages(['access_code' => ['Invalid or expired access code']]);
        }

        // Update last used timestamp
        $accessCode->update(['last_used_at' => now()]);

        // Update last login for admin
        $admin->update(['last_login' => now()]);

        // Login as the admin but present as access user with role 2
        $token = $admin->createToken('auth_token')->plainTextToken;

        // Custom user response for access code user
        $userResponse = [
            'id'              => $admin->id, // Keep admin ID for tenancy
            'firstName'       => $accessCode->user_name,
            'lastName'        => '',
            'email'           => $accessCode->email ?: $admin->email,
            'role_id'         => 2,
            'role'            => ['id' => 2, 'name' => 'Access User'],
            'is_school_admin' => false,
            'tenant_id'       => $admin->tenant_id,
            'accountType'     => 'institution',
            'permissions'     => $accessCode->permissions,
            'school' => $admin->school ? [
                'id'         => $admin->school->id,
                'name'       => $admin->school->name,
                'centre_number' => $admin->school->centre_number,
                'district'   => $admin->school->district,
                'county'     => $admin->school->county,
                'subcounty'  => $admin->school->subcounty,
                'parish'     => $admin->school->parish,
                'village'    => $admin->school->village,
                'admin_name' => $admin->school->admin_name,
                'admin_email' => $admin->school->admin_email,
                'admin_phone' => $admin->school->admin_phone,
                'status'     => $admin->school->status,
            ] : null,
        ];

        return response()->json([
            'message' => 'Tenant login successful',
            'user'    => $userResponse,
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
     * =================
     * FORGOT PASSWORD
     * =================
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to your email']);
        }

        return response()->json(['message' => 'Unable to send reset link'], 400);
    }

    /**
     * ===============
     * RESET PASSWORD
     * ===============
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully']);
        }

        return response()->json(['message' => 'Invalid token or email'], 400);
    }

    /**
     * ============================
     * CREATE TENANT USER (SAFE)
     * ============================
     */
    public function createTenantUser(Request $request)
    {
        $currentUser = $request->user();

        if (!$currentUser || !$currentUser->is_school_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName'  => 'nullable|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role_id'   => 'required|exists:roles,id',
            'department' => 'required|string|in:laboratory,pantry,sickbay,sports',
        ]);

        $newUser = User::create([
            'firstName'       => $request->firstName,
            'lastName'        => $request->lastName,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'tenant_id'       => $currentUser->tenant_id,
            'role_id'         => $request->role_id,
            'department'      => $request->department,
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

        if (!$currentUser || !$currentUser->is_school_admin || $user->tenant_id !== $currentUser->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'firstName' => 'sometimes|required|string|max:255',
            'lastName'  => 'nullable|string|max:255',
            'email'     => 'sometimes|required|email|unique:users,email,' . $user->id,
            'role_id'   => 'sometimes|required|exists:roles,id',
            'department' => 'sometimes|required|string|in:laboratory,pantry,sickbay,sports',
        ]);

        $user->update($request->only(['firstName', 'lastName', 'email', 'role_id', 'department']));

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
            !$currentUser ||
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
     * UPDATE SCHOOL INFORMATION
     * ============================
     */
    public function updateSchool(Request $request)
    {
        $user = $request->user();

        // Only school admins can update school information
        if (!$user || !$user->is_school_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name'         => 'sometimes|string|max:255',
            'district'     => 'sometimes|string|max:255',
            'admin_name'   => 'sometimes|string|max:255',
            'admin_email'  => 'sometimes|email|max:255',
            'admin_phone'  => 'sometimes|string|max:50',
        ]);

        $school = $user->school;
        if (!$school) {
            return response()->json(['message' => 'School not found'], 404);
        }

        $school->update($request->only(['name', 'district', 'admin_name', 'admin_email', 'admin_phone']));

        return response()->json([
            'message' => 'School information updated successfully',
            'school'  => $school,
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

        $users = User::where('tenant_id', $user->tenant_id)
            ->with('role')
            ->orderBy('last_login', 'desc')
            ->get();

        return response()->json(['users' => $users]);
    }

    /**
     * ============================
     * GET ALL USERS (ADMIN)
     * ============================
     */
    public function getAllUsers(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->is_school_admin) {
            return response()->json(['message' => 'Unauthorized. Only school administrators can view all users.'], 403);
        }

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
            'department'      => $user->department,
            'is_school_admin' => (bool) $user->is_school_admin,
            'tenant_id'       => $user->tenant_id,
            'accountType'     => 'institution',
            'school' => $user->school ? [
                'id'         => $user->school->id,
                'name'       => $user->school->name,
                'centre_number' => $user->school->centre_number,
                'district'   => $user->school->district,
                'county'     => $user->school->county,
                'subcounty'  => $user->school->subcounty,
                'parish'     => $user->school->parish,
                'village'    => $user->school->village,
                'admin_name' => $user->school->admin_name,
                'admin_email' => $user->school->admin_email,
                'admin_phone' => $user->school->admin_phone,
                'status'     => $user->school->status,
            ] : null,
        ];
    }

    /**
     * ============================
     * ACCESS CODE USER RESPONSE
     * ============================
     */
    private function accessCodeUserResponse(User $admin, $accessCode): array
    {
        return [
            'id'              => $admin->id, // Keep admin ID for tenancy
            'firstName'       => $accessCode->user_name,
            'lastName'        => '',
            'email'           => $accessCode->email ?: $admin->email,
            'role_id'         => 2,
            'role'            => ['id' => 2, 'name' => 'Access User'],
            'is_school_admin' => false,
            'tenant_id'       => $admin->tenant_id,
            'accountType'     => 'institution',
            'permissions'     => $accessCode->permissions,
            'school' => $admin->school ? [
                'id'     => $admin->school->id,
                'name'   => $admin->school->name,
                'status' => $admin->school->status,
            ] : null,
        ];
    }

    /**
     * ============================
     * CHANGE PASSWORD
     * ============================
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages(['current_password' => ['Current password is incorrect']]);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * ============================
     * GET SCHOOL INFORMATION
     * ============================
     */
    public function getSchool(Request $request)
    {
        $user = $request->user();

        if (!$user->school) {
            return response()->json(['message' => 'School not found'], 404);
        }

        return response()->json([
            'school' => $user->school,
        ]);
    }

    /**
     * ============================
     * GET DEPARTMENTS COUNT
     * ============================
     */
    public function getDepartmentsCount(Request $request)
    {
        $user = $request->user();

        // Count distinct departments that have users in this tenant
        $departmentsCount = User::where('tenant_id', $user->tenant_id)
            ->whereNotNull('department')
            ->distinct('department')
            ->count('department');

        return response()->json([
            'count' => $departmentsCount,
        ]);
    }
}

