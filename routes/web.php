    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\StationaryController;
    use App\Http\Controllers\SportsController;
    use App\Http\Controllers\EventsController;
    use App\Http\Controllers\HolidayController;
    use App\Http\Controllers\FurnitureController;
    use App\Http\Controllers\LibraryController;
    use App\Http\Controllers\LabController;
    use App\Http\Controllers\ComputerLabController;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\CartController;
    use App\Http\Controllers\CheckoutController;
    use App\Http\Controllers\SchoolController;
    use App\Http\Controllers\AuthController;
    use Illuminate\Support\Facades\Auth;




    // Root redirect
    Route::get('/', function () {
        return redirect()->route('login');
    });





    Route::middleware('auth')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/orders', [OrderController::class, 'getAllOrders'])->name('orders.index');
        Route::get('/quotations', function () {
            session(['title' => 'Quotations']);
            return view('quotations');
        })->name('quotations.index');
        Route::get('/dashboard/orders', [OrderController::class, 'dashboard'])->name('dashboard.orders');
        Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('orders.confirmPayment');
        Route::get('/stationaries', [StationaryController::class, 'index'])->name('index.stationaries');
        Route::get('/stationaries/create', [StationaryController::class, 'create'])->name('create.stationaries');
        Route::post('/stationaries/store', [StationaryController::class, 'store'])->name('store.stationaries');
        Route::get('/stationaries/{stationary}/edit', [StationaryController::class, 'edit'])->name('edit.stationaries');
        Route::put('/stationaries/{stationary}', [StationaryController::class, 'update'])->name('update.stationaries');
        Route::delete('/stationaries/{stationary}', [StationaryController::class, 'destroy'])->name('destroy.stationaries');

        Route::get('/sports', [SportsController::class, 'index'])->name('index.sports');
        Route::get('/sports/create', [SportsController::class, 'create'])->name('create.sports');
        Route::post('/sports/store', [SportsController::class, 'store'])->name('store.sports');
        Route::get('/sports/{sports}/edit', [SportsController::class, 'edit'])->name('edit.sports');
        Route::put('/sports/{sports}', [SportsController::class, 'update'])->name('update.sports');
        Route::delete('/sports/{sports}', [SportsController::class, 'destroy'])->name('destroy.sports');

        Route::get('/users', [AuthController::class, 'getAllUsersTable'])->name('users.index');

        // Schools management
        Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('/schools/all', [SchoolController::class, 'allSchoolsView'])->name('schools.all');
        Route::get('/schools/{school}', [SchoolController::class, 'schoolDetailsView'])->name('schools.details');
        Route::put('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::post('/schools/{school}/deactivate', [SchoolController::class, 'deactivate'])->name('schools.deactivate');
        Route::post('/schools/{school}/inactive', [SchoolController::class, 'deactivate'])->name('schools.inactive');
        Route::post('/schools/{school}/activate', [SchoolController::class, 'activate'])->name('schools.activate');
        Route::post('/schools/{school}/active', [SchoolController::class, 'activate'])->name('schools.active');
        Route::post('/schools/{school}/suspend', [SchoolController::class, 'suspend'])->name('schools.suspend');
        Route::post('/schools/{school}/suspended', [SchoolController::class, 'suspend'])->name('schools.suspended');
        Route::post('/users/reset-password', [SchoolController::class, 'resetUserPassword'])->name('users.reset-password');
        Route::post('/users/force-password-reset', [SchoolController::class, 'forcePasswordReset'])->name('users.force-password-reset');

        Route::get('/holidays', [HolidayController::class, 'index'])->name('index.holidays');
        Route::get('/holidays/create', [HolidayController::class, 'create'])->name('create.holidays');
        Route::post('/holidays/store', [HolidayController::class, 'store'])->name('store.holidays');
        Route::get('/holidays/{holiday}/edit', [HolidayController::class, 'edit'])->name('edit.holidays');
        Route::put('/holidays/{holiday}', [HolidayController::class, 'update'])->name('update.holidays');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('destroy.holidays');

        Route::get('/furniture', [FurnitureController::class, 'index'])->name('index.furniture');
        Route::get('/furniture/create', [FurnitureController::class, 'create'])->name('create.furniture');
        Route::post('/furniture/store', [FurnitureController::class, 'store'])->name('store.furniture');
        Route::get('/furniture/{furniture}/edit', [FurnitureController::class, 'edit'])->name('edit.furniture');
        Route::put('/furniture/{furniture}', [FurnitureController::class, 'update'])->name('update.furniture');
        Route::delete('/furniture/{furniture}', [FurnitureController::class, 'destroy'])->name('destroy.furniture');

        Route::get('/libraries', [LibraryController::class, 'index'])->name('index.libraries');
        Route::get('/libraries/create', [LibraryController::class, 'create'])->name('create.libraries');
        Route::post('/libraries/store', [LibraryController::class, 'store'])->name('store.libraries');
        Route::get('/libraries/{library}/edit', [LibraryController::class, 'edit'])->name('edit.libraries');
        Route::put('/libraries/{library}', [LibraryController::class, 'update'])->name('update.libraries');
        Route::delete('/libraries/{library}', [LibraryController::class, 'destroy'])->name('destroy.libraries');

            // Labs CRUD
        Route::get('/all-labs', [LabController::class, 'index'])->name('labs.index');
        Route::get('/labs/create', [LabController::class, 'create'])->name('labs.create');
        Route::post('/labs/store', [LabController::class, 'store'])->name('labs.store');
        Route::get('/labs/{lab}/edit', [LabController::class, 'edit'])->name('labs.edit');
        Route::put('/labs/{lab}', [LabController::class, 'update'])->name('labs.update');
        Route::delete('/labs/{lab}', [LabController::class, 'destroy'])->name('labs.destroy');

        // Import labs
        Route::post('/labs/import', [LabController::class, 'import'])->name('labs.import');



        Route::get('/computerLabs', [ComputerLabController::class, 'index'])->name('index.computerLabs');
        Route::get('/computerLabs/create', [ComputerLabController::class, 'create'])->name('create.computerLabs');
        Route::post('/computerLabs/store', [ComputerLabController::class, 'store'])->name('store.computerLabs');
        Route::get('/computerLabs/{computerLab}/edit', [ComputerLabController::class, 'edit'])->name('edit.computerLabs');
        Route::put('/computerLabs/{computerLab}', [ComputerLabController::class, 'update'])->name('update.computerLabs');
        Route::delete('/computerLabs/{computerLab}', [ComputerLabController::class, 'destroy'])->name('destroy.computerLabs');


    });

    Auth::routes();

    // Test route to bypass authentication
    Route::get('/test-login', function () {
        return response()->json(['message' => 'Test route working']);
    });

    // Custom login route for testing
    Route::post('/custom-login', function (Request $request) {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('web')->attempt($credentials)) {
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    });

    // New API-based login page
    Route::get('/api-login', function () {
        return view('auth.api-login');
    });

    // Login success page
    Route::get('/login-success', function () {
        return view('auth.login-success');
    });

