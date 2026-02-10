<?php

namespace App\Http\Controllers;

use App\Mail\SchoolRegistrationConfirmation;
use App\Models\School;
use App\Models\User;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Location;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\Order;
use App\Models\LabAccessCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::all();
        return response()->json(['schools' => $schools]);
    }

    /**
     * Render the comprehensive schools management view
     */
    public function allSchoolsView()
    {
        session(['title' => 'Schools Management']);
        $schools = School::withCount('users')->latest()->paginate(10);
        return view('admin.schools', compact('schools'));
    }

    /**
     * Render the detailed school view with all data
     */
    public function schoolDetailsView($school)
    {
        $school = School::where('id', $school)->firstOrFail();
        session(['title' => $school->name . ' - Details']);

        // Get all related data for this school
        $users = User::where('tenant_id', $school->id)->get();
        $items = Item::where('tenant_id', $school->id)->get();
        $suppliers = Supplier::where('tenant_id', $school->id)->get();
        $locations = Location::where('tenant_id', $school->id)->get();
        $categories = Category::where('tenant_id', $school->id)->get();

        // Get lab access codes (these contain user roles and departments for non-admin users)
        $labAccessCodes = LabAccessCode::where('school_id', $school->id)->get();

        // Calculate stats
        $totalItems = $items->count();
        $totalValue = $items->sum(function($item) {
            return ($item->quantity ?? 0) * ($item->unit_cost ?? 0);
        });
        $lowStockItems = $items->where('quantity', '<=', 'min_quantity')->count();

        // Get department counts (tables without tenant_id)
        $labsCount = DB::table('labs')->count();
        $pantriesCount = DB::table('pantries')->where('tenant_id', $school->id)->count();
        $sportsCount = DB::table('sports')->count();
        $furnitureCount = DB::table('furniture')->count();
        $librariesCount = DB::table('libraries')->count();

        // Get unlocked departments from school data
        $schoolData = $school->data ?? [];
        $unlockedDepartments = $schoolData['unlocked_departments'] ?? ['Laboratory'];
        $lockedDepartments = ['Pantry', 'Sports', 'Furniture', 'Library'];

        // Filter locked departments
        $lockedDepartmentsList = array_diff($lockedDepartments, $unlockedDepartments);

        // Department details with access status
        $departments = [
            [
                'name' => 'Laboratory',
                'icon' => 'fa-flask',
                'color' => 'primary',
                'items_count' => $labsCount,
                'access' => 'unlocked',
                'description' => 'Science labs and experiments management'
            ],
            [
                'name' => 'Pantry',
                'icon' => 'fa-coffee',
                'color' => 'success',
                'items_count' => $pantriesCount,
                'access' => in_array('Pantry', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'Cafeteria and food management'
            ],
            [
                'name' => 'Sports',
                'icon' => 'fa-futbol-o',
                'color' => 'warning',
                'items_count' => $sportsCount,
                'access' => in_array('Sports', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'Sports equipment and activities'
            ],
            [
                'name' => 'Furniture',
                'icon' => 'fa-chair',
                'color' => 'info',
                'items_count' => $furnitureCount,
                'access' => in_array('Furniture', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'School furniture and assets'
            ],
            [
                'name' => 'Library',
                'icon' => 'fa-book',
                'color' => 'secondary',
                'items_count' => $librariesCount,
                'access' => in_array('Library', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'Library books and resources'
            ],
        ];

        return view('admin.school-details', compact('school', 'users', 'items', 'suppliers', 'locations', 'categories', 'totalItems', 'totalValue', 'lowStockItems', 'departments', 'unlockedDepartments', 'lockedDepartmentsList', 'labAccessCodes'));
    }

    /**
     * Get comprehensive school details including all related data
     */
    public function getSchoolDetails($school)
    {
        $school = School::where('id', $school)->firstOrFail();

        // Get users for this school
        $users = User::where('tenant_id', $school->id)->get();

        // Get inventory items
        $items = Item::where('tenant_id', $school->id)->get();

        // Get suppliers
        $suppliers = Supplier::where('tenant_id', $school->id)->get();

        // Get storage locations
        $locations = Location::where('tenant_id', $school->id)->get();

        // Get categories
        $categories = Category::where('tenant_id', $school->id)->get();

        // Get stock movements
        $stockMovements = StockMovement::where('tenant_id', $school->id)->get();

        // Get orders
        $orders = Order::where('tenant_id', $school->id)->get();

        // Calculate inventory stats
        $totalItems = $items->count();
        $totalValue = $items->sum(function($item) {
            return ($item->quantity ?? 0) * ($item->unit_cost ?? 0);
        });
        $lowStockItems = $items->where('quantity', '<=', 'min_quantity')->count();

        // Get department counts (tables without tenant_id)
        $labsCount = DB::table('labs')->count();
        $pantriesCount = DB::table('pantries')->where('tenant_id', $school->id)->count();
        $sportsCount = DB::table('sports')->count();
        $furnitureCount = DB::table('furniture')->count();
        $librariesCount = DB::table('libraries')->count();

        // Get unlocked departments from school data
        $schoolData = $school->data ?? [];
        $unlockedDepartments = $schoolData['unlocked_departments'] ?? ['Laboratory'];

        $departments = [
            [
                'name' => 'Laboratory',
                'icon' => 'fa-flask',
                'items_count' => $labsCount,
                'access' => 'unlocked',
                'description' => 'Science labs and experiments management'
            ],
            [
                'name' => 'Pantry',
                'icon' => 'fa-coffee',
                'items_count' => $pantriesCount,
                'access' => in_array('Pantry', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'Cafeteria and food management'
            ],
            [
                'name' => 'Sports',
                'icon' => 'fa-futbol-o',
                'items_count' => $sportsCount,
                'access' => in_array('Sports', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'Sports equipment and activities'
            ],
            [
                'name' => 'Furniture',
                'icon' => 'fa-chair',
                'items_count' => $furnitureCount,
                'access' => in_array('Furniture', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'School furniture and assets'
            ],
            [
                'name' => 'Library',
                'icon' => 'fa-book',
                'items_count' => $librariesCount,
                'access' => in_array('Library', $unlockedDepartments) ? 'unlocked' : 'locked',
                'description' => 'Library books and resources'
            ],
        ];

        return response()->json([
            'school' => $school,
            'users' => [
                'count' => $users->count(),
                'data' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->firstName . ' ' . $user->lastName,
                        'email' => $user->email,
                        'role' => $user->role,
                        'department' => $user->department,
                        'is_school_admin' => $user->is_school_admin,
                        'last_login' => $user->last_login,
                    ];
                })
            ],
            'inventory' => [
                'total_items' => $totalItems,
                'total_value' => $totalValue,
                'low_stock_count' => $lowStockItems,
                'items' => $items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'category' => $item->category ? $item->category->name : 'N/A',
                        'quantity' => $item->quantity,
                        'min_quantity' => $item->min_quantity,
                        'unit' => $item->unit,
                        'unit_cost' => $item->unit_cost,
                        'total_value' => $item->total_value,
                        'supplier' => $item->supplier ? $item->supplier->name : 'N/A',
                        'location' => $item->location ? $item->location->name : 'N/A',
                        'expiry_date' => $item->expiry_date,
                    ];
                })
            ],
            'suppliers' => [
                'count' => $suppliers->count(),
                'data' => $suppliers
            ],
            'storage_locations' => [
                'count' => $locations->count(),
                'data' => $locations
            ],
            'categories' => [
                'count' => $categories->count(),
                'data' => $categories
            ],
            'stock_movements' => [
                'count' => $stockMovements->count(),
                'recent' => $stockMovements->sortByDesc('created_at')->take(10)
            ],
            'orders' => [
                'total' => $orders->count(),
                'total_amount' => $orders->sum('total_amount'),
                'pending' => $orders->where('status', 'pending')->count(),
                'completed' => $orders->where('status', 'completed')->count(),
            ],
            'departments' => $departments,
            'unlocked_departments' => $unlockedDepartments,
            'locked_departments' => array_diff(['Pantry', 'Sports', 'Furniture', 'Library'], $unlockedDepartments),
            'activity_summary' => [
                'total_logins' => $users->sum('login_count'),
                'active_today' => $users->where('last_login', '>=', today())->count(),
            ]
        ]);
    }

    /**
     * Deactivate a school account
     */
    public function deactivate($school)
    {
        $school = School::where('id', $school)->firstOrFail();
        $school->update(['status' => 'inactive']);

        // Also deactivate all users associated with this school
        User::where('tenant_id', $school->id)->update(['is_active' => false]);

        return response()->json([
            'message' => 'School account has been deactivated successfully',
            'school' => $school
        ]);
    }

    /**
     * Activate a school account
     */
    public function activate($school)
    {
        $school = School::where('id', $school)->firstOrFail();
        $school->update(['status' => 'active']);

        // Also activate all users associated with this school
        User::where('tenant_id', $school->id)->update(['is_active' => true]);

        return response()->json([
            'message' => 'School account has been activated successfully',
            'school' => $school
        ]);
    }

    /**
     * Suspend a school account
     */
    public function suspend($school)
    {
        $school = School::where('id', $school)->firstOrFail();
        $school->update(['status' => 'suspended']);

        // Also deactivate all users associated with this school
        User::where('tenant_id', $school->id)->update(['is_active' => false]);

        return response()->json([
            'message' => 'School account has been suspended successfully',
            'school' => $school
        ]);
    }

    /**
     * Reset a user's password
     */
    public function resetUserPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = User::findOrFail($request->user_id);
        \Log::info('Admin resetting password for user', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'school_id' => $user->tenant_id
        ]);

        $user->update([
            'password' => bcrypt($request->new_password),
            'force_password_reset' => false,
        ]);

        return response()->json([
            'message' => 'Password has been reset successfully for ' . $user->firstName . ' ' . $user->lastName
        ]);
    }

    /**
     * Force password reset for a user (mark them to reset on next login)
     */
    public function forcePasswordReset(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        \Log::info('Admin forcing password reset for user', [
            'admin_id' => auth()->id(),
            'target_user_id' => $user->id,
            'school_id' => $user->tenant_id
        ]);

        $user->update([
            'force_password_reset' => true,
        ]);

        return response()->json([
            'message' => $user->firstName . ' ' . $user->lastName . ' will be required to reset their password on next login'
        ]);
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

    public function update(Request $request, $school)
    {
        $school = School::where('id', $school)->firstOrFail();
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
