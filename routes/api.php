<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminPencairanController;
use App\Http\Controllers\Api\BengkelBookingController;
use App\Http\Controllers\Api\BengkelController;
use App\Http\Controllers\Api\BengkelTransactionController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\JadwalController;
use App\Http\Controllers\API\LayananController;
use App\Http\Controllers\API\PageController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileUserController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\WithdrawRequestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ Public (no auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register-owner', [AuthController::class, 'registerOwner']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/midtrans-callback', [CheckoutController::class, 'callback']);
Route::post('/chatbot', [ChatbotController::class, 'handle']);
Route::get('/home', [PageController::class, 'home']);
Route::get('/products', [PageController::class, 'index']);
Route::get('/products/{id}', [PageController::class, 'detailProduct']);

 Route::prefix('service')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::get('/kecamatan', [ServiceController::class, 'getKecamatan']);
        Route::get('/kelurahan/{kecamatan_id}', [ServiceController::class, 'getKelurahans']);
        Route::get('/bengkel/{id}', [ServiceController::class, 'detailBengkel']);
    });

// ✅ Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/jadwals', [JadwalController::class, 'index']);
    Route::post('/jadwals', [JadwalController::class, 'store']);
    Route::get('/jadwals/{id}', [JadwalController::class, 'show']);
    Route::put('/jadwals/{id}', [JadwalController::class, 'update']);
    Route::delete('/jadwals/{id}', [JadwalController::class, 'destroy']);

    Route::get('/layanans', [LayananController::class, 'index']);
    Route::post('/layanans', [LayananController::class, 'store']);
    Route::get('/layanans/{id}', [LayananController::class, 'show']);
    Route::put('/layanans/{id}', [LayananController::class, 'update']);
    Route::delete('/layanans/{id}', [LayananController::class, 'destroy']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Profile
    Route::get('/profile', [ProfileUserController::class, 'show']);
    Route::put('/profile', [ProfileUserController::class, 'update']);

    // Booking
    Route::get('/profile/bookings', [ProfileUserController::class, 'bookingList']);
    Route::get('/profile/bookings/{id}', [ProfileUserController::class, 'bookingDetail']);

    // Transactions
    Route::get('/profile/transactions', [ProfileUserController::class, 'transactionList']);
    Route::get('/profile/transactions/{id}', [ProfileUserController::class, 'transactionDetail']);

    Route::get('/', [WithdrawRequestController::class, 'index']);
    Route::post('/', [WithdrawRequestController::class, 'store']);
    Route::get('/{id}', [WithdrawRequestController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Users
        Route::get('/users', [AdminController::class, 'listUser']);
        Route::get('/users/{id}', [AdminController::class, 'detailUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

        // Owners
        Route::get('/owners', [AdminController::class, 'listOwner']);
        Route::get('/owners/{id}', [AdminController::class, 'detailOwner']);
        Route::delete('/owners/{id}', [AdminController::class, 'deleteOwner']);

        // Bengkels (Admin View)
        Route::get('/bengkels', [AdminController::class, 'listBengkel']);
        Route::get('/bengkels/{id}', [AdminController::class, 'detailBengkel']);
        Route::delete('/bengkels/{id}', [AdminController::class, 'deleteBengkel']);

        // Bookings (Admin View)
        Route::get('/bookings', [AdminController::class, 'listBooking']);
        Route::get('/bookings/{id}', [AdminController::class, 'detailBooking']);

        // Transactions (Admin View)
        Route::get('/transactions', [AdminController::class, 'listTransaction']);
        Route::get('/transactions/{id}', [AdminController::class, 'detailTransaction']);

        // Pencairan
        Route::get('/pencairan', [AdminPencairanController::class, 'index']);
        Route::get('/pencairan/{id}', [AdminPencairanController::class, 'show']);
        Route::put('/pencairan/{id}', [AdminPencairanController::class, 'update']);
    });

    /*
    |--------------------------------------------------------------------------
    | Bengkel (Mitra) Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('bengkel')->group(function () {
        // Bengkel CRUD
        Route::get('/', [BengkelController::class, 'index']);
        Route::get('/{id}', [BengkelController::class, 'show']);
        Route::post('/', [BengkelController::class, 'store']);
        Route::put('/{id}', [BengkelController::class, 'update']);
        Route::delete('/{id}', [BengkelController::class, 'destroy']);
        Route::get('/kelurahan/{kecamatan_id}', [BengkelController::class, 'getKelurahans']);

        // Booking Mitra
        Route::get('/bookings', [BengkelBookingController::class, 'index']);
        Route::get('/bookings/{id}', [BengkelBookingController::class, 'show']);
        Route::put('/bookings/{id}', [BengkelBookingController::class, 'update']);

        // Transactions Mitra
        Route::get('/transactions', [BengkelTransactionController::class, 'index']);
        Route::get('/transactions/{id}', [BengkelTransactionController::class, 'show']);
        Route::put('/transactions/{id}', [BengkelTransactionController::class, 'update']);

        // Cart Mitra
        Route::post('/cart/add', [BengkelTransactionController::class, 'cartAdd']);
        Route::delete('/cart/{id}', [BengkelTransactionController::class, 'cartRemove']);

        // Checkout Mitra
        Route::post('/checkout', [BengkelTransactionController::class, 'checkout']);
    });

    /*
    |--------------------------------------------------------------------------
    | Booking (User)
    |--------------------------------------------------------------------------
    */
    Route::prefix('booking')->group(function () {
        Route::post('/', [BookingController::class, 'store']);
        Route::get('/{bengkel_id}/booked-times', [BookingController::class, 'getBookedTimes']);
    });

    /*
    |--------------------------------------------------------------------------
    | Cart (User)
    |--------------------------------------------------------------------------
    */
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });
});

Route::post('/midtrans-callback', [CheckoutController::class, 'callback']);
