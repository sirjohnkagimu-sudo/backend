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
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\LabCalendarController;


// Public login routes (no middleware)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/tenant/login', [AuthController::class, 'tenantLogin']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/users', [AuthController::class, 'getAllUsers']);


// Authenticated routes
Route::middleware(['auth:sanctum'])->group(function () {
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

    Route::apiResource('items', ItemController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('stock-movements', StockMovementController::class);


    // Admin routes for school management
    Route::apiResource('schools', SchoolController::class)->only(['index', 'store', 'update']);
    Route::put('/school/update', [SchoolController::class, 'updateSchool']);

    // Teacher passcode management
    Route::apiResource('teacher-passcodes', TeacherPasscodeController::class);

    // Lab access code management
    Route::apiResource('lab-access-codes', LabAccessCodeController::class);

    // User management within tenant
    Route::get('/tenant-users', [AuthController::class, 'getTenantUsers']);
    Route::post('/tenant-users', [AuthController::class, 'createTenantUser']);
    Route::put('/tenant-users/{user}', [AuthController::class, 'updateTenantUser']);
    Route::delete('/tenant-users/{user}', [AuthController::class, 'deleteTenantUser']);



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

