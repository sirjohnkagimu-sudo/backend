<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StationaryController;
use App\Http\Controllers\SportsController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\FurnitureController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\Api\LabApiController;
use App\Http\Controllers\ComputerLabController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\TeacherPasscodeController;
use App\Http\Controllers\LabAccessCodeController;
use App\Http\Controllers\DepartmentAccessCodeController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\LabCalendarController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PantryController;
use App\Http\Controllers\SickbayController;
use App\Http\Controllers\UnitController;


// Public login routes (no middleware)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/tenant/login', [AuthController::class, 'tenantLogin']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);


// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
    // User management - moved inside auth middleware
    Route::get('/users', [AuthController::class, 'getAllUsers']);
    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/cart/all', [CartController::class, 'allCarts']);
    Route::get('/cart', [CartController::class, 'view']);               // ✅ Fetch cart
    Route::post('/cart/add', [CartController::class, 'add']);           // ✅ Add to cart
    Route::put('/cart/{product_id}', [CartController::class, 'update']); // ✅ Update quantity
    Route::delete('/cart/remove/{product_id}', [CartController::class, 'remove']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //inventory routes

    Route::get('items/low-stock', [ItemController::class,'lowStock']);

    Route::get('/lab/calendar', [LabCalendarController::class, 'index']);
    Route::post('/lab/sessions', [LabCalendarController::class, 'store']);
    Route::put('/lab/sessions/{labSession}', [LabCalendarController::class, 'update']);
    Route::delete('/lab/sessions/{labSession}', [LabCalendarController::class, 'destroy']);

    Route::get('/orders/pending', [OrderController::class, 'checkPendingOrder']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/checkout/confirm-pay-on-delivery', [OrderController::class, 'confirmPayOnDelivery']);
    Route::get('/orders', [OrderController::class, 'index']);

        // Analytics
    Route::get('/analytics', [ReportController::class, 'analytics']);
    Route::post('/reports/log-download', [ReportController::class, 'logReportDownload']);

    // Combined dashboard stats endpoint (reduces API calls from 4+ to 1)
    Route::get('/dashboard/stats', [DashboardController::class, 'dashboardStats']);

    // ==================== LABORATORY ROUTES ====================
    Route::apiResource('items', ItemController::class);
    Route::post('/items/bulk-import', [ItemController::class, 'bulkImport']);
    Route::get('/items/location/{locationId}', [ItemController::class, 'getByLocation']);

    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('conditions', ConditionController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('transaction-types', TransactionTypeController::class);
    Route::apiResource('stock-movements', StockMovementController::class);
    Route::apiResource('units', UnitController::class);

    // ==================== PANTRY ITEMS ROUTES ====================
    Route::get('/pantry-items', [PantryController::class, 'itemsIndex']);
    Route::post('/pantry-items', [PantryController::class, 'itemsStore']);
    Route::get('/pantry-items/{pantryItem}', [PantryController::class, 'itemsShow']);
    Route::put('/pantry-items/{pantryItem}', [PantryController::class, 'itemsUpdate']);
    Route::delete('/pantry-items/{pantryItem}', [PantryController::class, 'itemsDestroy']);
    Route::post('/pantry-items/bulk-import', [PantryController::class, 'itemsBulkImport']);
    Route::get('/pantry-items/location/{locationId}', [PantryController::class, 'itemsGetByLocation']);
    Route::get('/pantry-items/low-stock', [PantryController::class, 'itemsLowStock']);
     Route::get('/pantry-storage-locations', [PantryController::class, 'storageLocations']);
    Route::post('/pantry-storage-locations', [PantryController::class, 'storeStorageLocation']);
    Route::put('/pantry-storage-locations/{Pantry}', [PantryController::class, 'updateStorageLocation']);
    Route::delete('/pantry-storage-locations/{Pantry}', [PantryController::class, 'destroyStorageLocation']);

    // ==================== LIBRARY ROUTES ====================
    Route::get('/libraries', [LibraryController::class, 'apiIndex']);
    Route::post('/libraries', [LibraryController::class, 'apiStore']);
    Route::get('/libraries/{id}', [LibraryController::class, 'apiShow']);
    Route::put('/libraries/{id}', [LibraryController::class, 'apiUpdate']);
    Route::delete('/libraries/{id}', [LibraryController::class, 'apiDestroy']);
    Route::post('/libraries/bulk-import', [LibraryController::class, 'bulkImport']);
    Route::get('/libraries/location/{locationId}', [LibraryController::class, 'getByLocation']);
    Route::get('/libraries/low-stock', [LibraryController::class, 'lowStock']);

    // ==================== FURNITURE ROUTES ====================
    Route::get('/furnitures', [FurnitureController::class, 'apiIndex']);
    Route::post('/furnitures', [FurnitureController::class, 'apiStore']);
    Route::get('/furnitures/{id}', [FurnitureController::class, 'apiShow']);
    Route::put('/furnitures/{id}', [FurnitureController::class, 'apiUpdate']);
    Route::delete('/furnitures/{id}', [FurnitureController::class, 'apiDestroy']);
    Route::post('/furnitures/bulk-import', [FurnitureController::class, 'bulkImport']);
    Route::get('/furnitures/location/{locationId}', [FurnitureController::class, 'getByLocation']);
    Route::get('/furnitures/low-stock', [FurnitureController::class, 'lowStock']);


    // Admin routes for school management
    Route::apiResource('schools', SchoolController::class)->only(['index', 'store', 'update']);
    Route::get('/schools/{school}/details', [SchoolController::class, 'getSchoolDetails']);
    Route::post('/schools/{school}/deactivate', [SchoolController::class, 'deactivate']);
    Route::post('/schools/{school}/activate', [SchoolController::class, 'activate']);
    Route::post('/schools/{school}/suspend', [SchoolController::class, 'suspend']);
    Route::post('/schools/{school}/suspended', [SchoolController::class, 'suspend']);
    Route::get('/school', [SchoolController::class, 'show']); // Single school for current tenant
    Route::put('/school/update', [SchoolController::class, 'updateSchool']);

    // Teacher passcode management
    Route::apiResource('teacher-passcodes', TeacherPasscodeController::class);

    // Lab/department access code management
    Route::apiResource('lab-access-codes', LabAccessCodeController::class);
    Route::apiResource('department-access-codes', DepartmentAccessCodeController::class);

    // User management within tenant
    Route::get('/tenant-users', [AuthController::class, 'getTenantUsers']);
    Route::post('/tenant-users', [AuthController::class, 'createTenantUser']);
    Route::put('/tenant-users/{user}', [AuthController::class, 'updateTenantUser']);
    Route::delete('/tenant-users/{user}', [AuthController::class, 'deleteTenantUser']);

    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::put('/notifications/{id}/ignore', [NotificationController::class, 'ignore']);
    Route::delete('/notifications/read/clear', [NotificationController::class, 'clearRead']);
    Route::post('/notifications/sample', [NotificationController::class, 'createSample']);

    // Department count
    Route::get('/departments/count', [AuthController::class, 'getDepartmentsCount']);
    Route::get('/departments/unlocked', [AuthController::class, 'getUnlockedDepartments']);

    // Quotation routes
    Route::post('/quotations/send', [QuotationController::class, 'sendQuotation']);

    // Audit trail (admin only)
    Route::get('/audit-trail', [AuditController::class, 'index']);

    // ==================== PANTRY ROUTES ====================
    Route::prefix('pantry')->group(function () {
        // Pantry management
        Route::get('/', [PantryController::class, 'index']);

        // Sessions - must come BEFORE the /{pantry} route
        Route::get('/sessions', [PantryController::class, 'sessions']);
        Route::post('/sessions', [PantryController::class, 'storeSession']);
        Route::put('/sessions/{session}', [PantryController::class, 'updateSession']);
        Route::delete('/sessions/{session}', [PantryController::class, 'destroySession']);

        // Meal plans - must come BEFORE the /{pantry} route
        Route::get('/meal-plans', [PantryController::class, 'mealPlans']);
        Route::post('/meal-plans', [PantryController::class, 'storeMealPlan']);
        Route::put('/meal-plans/{mealPlan}', [PantryController::class, 'updateMealPlan']);
        Route::post('/meal-plans/{mealPlan}/approve', [PantryController::class, 'approveMealPlan']);
        Route::delete('/meal-plans/{mealPlan}', [PantryController::class, 'destroyMealPlan']);

        // Dashboard stats - must come BEFORE the /{pantry} route
        Route::get('/dashboard/stats', [PantryController::class, 'dashboardStats']);

        // Items - must come BEFORE the /{pantry} route
        Route::get('/items', [PantryController::class, 'items']);

        // Transactions - must come BEFORE the /{pantry} route
        Route::get('/transactions', [PantryController::class, 'transactions']);
        Route::post('/transactions', [PantryController::class, 'storeTransaction']);

        // Reports - must come BEFORE the /{pantry} route
        Route::get('/reports', [PantryController::class, 'reports']);

        // Suppliers - must come BEFORE the /{pantry} route
        Route::get('/suppliers', [PantryController::class, 'suppliers']);

        // Storage locations - must come BEFORE the /{pantry} route
        Route::get('/storage-locations', [PantryController::class, 'storageLocations']);

        // Pantry management - must come AFTER specific routes
        Route::post('/', [PantryController::class, 'store']);
        Route::get('/{pantry}', [PantryController::class, 'show']);
        Route::put('/{pantry}', [PantryController::class, 'update']);
        Route::delete('/{pantry}', [PantryController::class, 'destroy']);
    });

    // ==================== STORE ROUTES ====================
    Route::prefix('store')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/categories', [StoreController::class, 'categories']);
        Route::get('/transactions', [StoreController::class, 'transactions']);
        Route::post('/transactions', [StoreController::class, 'storeTransaction']);
        Route::get('/reports', [StoreController::class, 'reports']);
        Route::get('/audit', [StoreController::class, 'audit']);
        Route::get('/{id}', [StoreController::class, 'show']);
        Route::post('/', [StoreController::class, 'store']);
        Route::put('/{id}', [StoreController::class, 'update']);
        Route::delete('/{id}', [StoreController::class, 'destroy']);
    });

    // ==================== FURNITURE ROUTES ====================
    Route::prefix('furniture')->group(function () {
        Route::get('/', [FurnitureController::class, 'getFurniture']);
        Route::get('/categories', [FurnitureController::class, 'getCategories']);
        Route::get('/calendar', [FurnitureController::class, 'getCalendar']);
        Route::post('/calendar', [FurnitureController::class, 'storeCalendar']);
        Route::put('/calendar/{id}', [FurnitureController::class, 'updateCalendar']);
        Route::delete('/calendar/{id}', [FurnitureController::class, 'destroyCalendar']);
        Route::get('/{id}', [FurnitureController::class, 'getFurnitureById']);
    });

    // ==================== SPORTS ROUTES ====================
    Route::prefix('sports')->group(function () {
        Route::get('/', [SportsController::class, 'getSports']);
        Route::get('/categories', [SportsController::class, 'getCategories']);
        Route::get('/calendar', [SportsController::class, 'getCalendar']);
        Route::post('/calendar', [SportsController::class, 'storeCalendar']);
        Route::put('/calendar/{id}', [SportsController::class, 'updateCalendar']);
        Route::delete('/calendar/{id}', [SportsController::class, 'destroyCalendar']);
        Route::get('/{id}', [SportsController::class, 'getSportsById']);
        Route::post('/', [SportsController::class, 'apiStore']);
        Route::put('/{id}', [SportsController::class, 'apiUpdate']);
        Route::delete('/{id}', [SportsController::class, 'apiDestroy']);
    });

    // ==================== SICKBAY ROUTES ====================
    Route::prefix('sickbay')->group(function () {
        // Dashboard stats
        Route::get('/dashboard/stats', [SickbayController::class, 'dashboardStats']);

        // Students
        Route::get('/students', [SickbayController::class, 'students']);
        Route::post('/students', [SickbayController::class, 'storeStudent']);
        Route::get('/students/{student}', [SickbayController::class, 'showStudent']);
        Route::put('/students/{student}', [SickbayController::class, 'updateStudent']);
        Route::delete('/students/{student}', [SickbayController::class, 'destroyStudent']);

        // Medicines
        Route::get('/medicines', [SickbayController::class, 'medicines']);
        Route::post('/medicines', [SickbayController::class, 'storeMedicine']);
        Route::get('/medicines/{medicine}', [SickbayController::class, 'showMedicine']);
        Route::put('/medicines/{medicine}', [SickbayController::class, 'updateMedicine']);
        Route::delete('/medicines/{medicine}', [SickbayController::class, 'destroyMedicine']);

        // Visits
        Route::get('/visits', [SickbayController::class, 'visits']);
        Route::post('/visits', [SickbayController::class, 'storeVisit']);
        Route::get('/visits/{visit}', [SickbayController::class, 'showVisit']);
        Route::put('/visits/{visit}', [SickbayController::class, 'updateVisit']);
        Route::delete('/visits/{visit}', [SickbayController::class, 'destroyVisit']);

        // Admissions
        Route::get('/admissions', [SickbayController::class, 'admissions']);
        Route::post('/admissions', [SickbayController::class, 'storeAdmission']);
        Route::get('/admissions/{admission}', [SickbayController::class, 'showAdmission']);
        Route::put('/admissions/{admission}', [SickbayController::class, 'updateAdmission']);
        Route::post('/admissions/{admission}/discharge', [SickbayController::class, 'dischargePatient']);
        Route::delete('/admissions/{admission}', [SickbayController::class, 'destroyAdmission']);

        // Referrals
        Route::get('/referrals', [SickbayController::class, 'referrals']);
        Route::post('/referrals', [SickbayController::class, 'storeReferral']);
        Route::get('/referrals/{referral}', [SickbayController::class, 'showReferral']);
        Route::put('/referrals/{referral}', [SickbayController::class, 'updateReferral']);
        Route::post('/referrals/{referral}/complete', [SickbayController::class, 'completeReferral']);
        Route::delete('/referrals/{referral}', [SickbayController::class, 'destroyReferral']);
    });

    // ==================== LIBRARY MANAGEMENT ROUTES ====================
    Route::prefix('library')->group(function () {
        // Dashboard stats
        Route::get('/dashboard/stats', [LibraryController::class, 'getStats']);

        // Book Titles
        Route::get('/book-titles', [LibraryController::class, 'getBookTitles']);
        Route::post('/book-titles', [LibraryController::class, 'createBookTitle']);
        Route::post('/book-titles/bulk-import', [LibraryController::class, 'bulkImportBookTitles']);
        Route::put('/book-titles/{id}', [LibraryController::class, 'updateBookTitle']);
        Route::delete('/book-titles/{id}', [LibraryController::class, 'deleteBookTitle']);

        // Book Copies
        Route::get('/book-copies', [LibraryController::class, 'getBookCopies']);
        Route::post('/book-copies', [LibraryController::class, 'createBookCopy']);
        Route::put('/book-copies/{id}', [LibraryController::class, 'updateBookCopy']);
        Route::delete('/book-copies/{id}', [LibraryController::class, 'deleteBookCopy']);

        // Students
        Route::get('/students', [LibraryController::class, 'getStudents']);
        Route::post('/students', [LibraryController::class, 'createStudent']);
        Route::put('/students/{id}', [LibraryController::class, 'updateStudent']);
        Route::delete('/students/{id}', [LibraryController::class, 'deleteStudent']);

        // Transactions
        Route::get('/transactions', [LibraryController::class, 'getTransactions']);
        Route::post('/transactions/issue', [LibraryController::class, 'issueBook']);
        Route::post('/transactions/{id}/return', [LibraryController::class, 'returnBook']);

        // Donors
        Route::get('/donors', [LibraryController::class, 'getDonors']);
        Route::post('/donors', [LibraryController::class, 'createDonor']);

        // Donations
        Route::get('/donations', [LibraryController::class, 'getDonations']);
        Route::post('/donations', [LibraryController::class, 'createDonation']);

        // Reservations
        Route::get('/reservations', [LibraryController::class, 'getReservations']);
        Route::post('/reservations', [LibraryController::class, 'createReservation']);
        Route::post('/reservations/{id}/approve', [LibraryController::class, 'approveReservation']);

        // Attendance
        Route::get('/attendance', [LibraryController::class, 'getAttendance']);
        Route::post('/attendance', [LibraryController::class, 'recordAttendance']);

        // Teacher Allocations
        Route::get('/teacher-allocations', [LibraryController::class, 'getTeacherAllocations']);
        Route::post('/teacher-allocations', [LibraryController::class, 'createTeacherAllocation']);

        // Bulk Issues
        Route::get('/bulk-issues', [LibraryController::class, 'getBulkIssues']);
        Route::post('/bulk-issues', [LibraryController::class, 'createBulkIssue']);

        // Clearances
        Route::get('/clearances', [LibraryController::class, 'getClearances']);
        Route::post('/clearances', [LibraryController::class, 'createClearance']);

        // Payments
        Route::get('/payments', [LibraryController::class, 'getPayments']);
        Route::post('/payments', [LibraryController::class, 'recordPayment']);

        // Invoices
        Route::get('/invoices', [LibraryController::class, 'getInvoices']);
        Route::post('/invoices', [LibraryController::class, 'createInvoice']);

        // Settings
        Route::get('/settings', [LibraryController::class, 'getSettings']);
        Route::put('/settings', [LibraryController::class, 'updateSettings']);
    });

});





Route::post('/flutterwave/webhook', [CheckoutController::class, 'handleWebhook']);
//cart and order routes
// API routes for various resources
Route::get('/stationary', [StationaryController::class, 'getStationary']);
Route::get('/sports', [SportsController::class, 'getSports']);
Route::get('/holidays', [HolidayController::class, 'getHolidays']);
Route::get('/furniture', [FurnitureController::class, 'getFurniture']);
Route::get('/libraries', [LibraryController::class, 'getLibraries'])->name('api.libraries') ;


// Lab routes protected by auth middleware
Route::prefix('labs')->group(function () {
    Route::get('/', [LabApiController::class, 'index']);
    Route::post('/', [LabApiController::class, 'store']);
});

Route::get('/computer-labs', [ComputerLabController::class, 'getComputerLab'])->name('api.computer_lab');

