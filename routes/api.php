<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminPencairanController;
use App\Http\Controllers\API\BengkelBookingController;
use App\Http\Controllers\API\BengkelController;
use App\Http\Controllers\API\BengkelTransactionController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\ChatbotController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\JadwalController;
use App\Http\Controllers\API\LayananController;
use App\Http\Controllers\API\PageController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ProfileUserController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\ChatApiController;
use App\Http\Controllers\API\WithdrawRequestController;
use App\Http\Controllers\API\SpecialistController;
use App\Http\Controllers\API\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ✅ Public (no auth)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/midtrans-callback', [CheckoutController::class, 'callback']);
Route::post('/chatbot', [ChatbotController::class, 'handle']);
Route::get('/home', [PageController::class, 'home']);
Route::get('/products', [PageController::class, 'index']);
Route::get('/products/{id}', [PageController::class, 'detailProduct']);

// Specialists
Route::get('/specialists', [SpecialistController::class, 'index']);
Route::get('/specialists/{id}', [SpecialistController::class, 'show']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/products', [CategoryController::class, 'products']);

Route::prefix('service')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::get('/kecamatan', [ServiceController::class, 'getKecamatan']);
    Route::get('/kelurahan/{kecamatan_id}', [ServiceController::class, 'getKelurahans']);
    Route::get('/bengkel/{id}', [ServiceController::class, 'detailBengkel']);
});




    Route::prefix('bengkel')->group(function () {

        Route::get('/', [BengkelController::class, 'index']);
        Route::get('/list', [BengkelController::class, 'all']);
        Route::get('/nearby', [BengkelController::class, 'findNearby']);
        Route::get('/{id}', [BengkelController::class, 'show']);
        Route::get('/kelurahan/{kecamatan_id}', [BengkelController::class, 'getKelurahans']);




        // // Booking Mitra
        // Route::get('/bookings', [BengkelBookingController::class, 'index']);
        // Route::get('/bookings/{id}', [BengkelBookingController::class, 'show']);
        // Route::put('/bookings/{id}', [BengkelBookingController::class, 'update']);

        // // Transactions Mitra
        // Route::get('/transactions', [BengkelTransactionController::class, 'index']);
        // Route::get('/transactions/{id}', [BengkelTransactionController::class, 'show']);
        // Route::put('/transactions/{id}', [BengkelTransactionController::class, 'update']);

        // // Cart Mitra
        // Route::post('/cart/add', [BengkelTransactionController::class, 'cartAdd']);
        // Route::delete('/cart/{id}', [BengkelTransactionController::class, 'cartRemove']);

        // // Checkout Mitra
        // Route::post('/checkout', [BengkelTransactionController::class, 'checkout']);
    });

// ✅ Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'fetch']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/checkout-summary', [CheckoutController::class, 'getCheckoutSummary']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/jadwals', [JadwalController::class, 'index']);

    Route::get('/layanans', [LayananController::class, 'index']);
    Route::get('/layanans/{id}', [LayananController::class, 'show']);

    // Profile
    Route::get('/profile', [ProfileUserController::class, 'show']);
    Route::put('/profile', [ProfileUserController::class, 'update']);

    // Booking
    Route::get('/profile/bookings', [ProfileUserController::class, 'bookingList']);
    Route::get('/profile/bookings/{id}', [ProfileUserController::class, 'bookingDetail']);

    // Transactions
    Route::get('/profile/transactions', [ProfileUserController::class, 'transactionList']);
    Route::get('/profile/transactions/{id}', [ProfileUserController::class, 'transactionDetail']);

    // Rating
    Route::post('/ratings', [\App\Http\Controllers\API\RatingController::class, 'store']);
    Route::get('/ratings/product/{product}', [\App\Http\Controllers\API\RatingController::class, 'listByProduct']);

    Route::post('/chat/send', [ChatApiController::class, 'send']);

    Route::get('/withdraw-requests', [WithdrawRequestController::class, 'index']);
    Route::post('/withdraw-requests', [WithdrawRequestController::class, 'store']);
    Route::get('/withdraw-requests/{id}', [WithdrawRequestController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Bengkel (Mitra) Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('bengkel')->group(function () {
        // Bengkel CRUD
        // Route::get('/', [BengkelController::class, 'index']);
        // Route::get('/{id}', [BengkelController::class, 'show']);
        // Route::post('/', [BengkelController::class, 'store']);
        // Route::put('/{id}', [BengkelController::class, 'update']);
        // Route::delete('/{id}', [BengkelController::class, 'destroy']);
        // Route::get('/kelurahan/{kecamatan_id}', [BengkelController::class, 'getKelurahans']);

        // Booking Mitra
        Route::get('/bookings', [BengkelBookingController::class, 'index']);
        Route::get('/bookings/{id}', [BengkelBookingController::class, 'show']);
        Route::put('/bookings/{id}', [BengkelBookingController::class, 'update']);

        // Transactions Mitra
        Route::get('/transactions', [BengkelTransactionController::class, 'index']);
        Route::get('/transactions/create/{bookingId}', [BengkelTransactionController::class, 'create']);
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

    // Lihat booking user sendiri
    Route::get('/user/bookings', [BookingController::class, 'userBookings']);
    Route::get('/user/bookings/{id}', [BookingController::class, 'showUserBooking']);

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


Route::post('/register-owner', [AuthController::class, 'registerOwner']);
Route::post('/login-owner', [AuthController::class, 'loginOwner']);

Route::middleware('auth:owner-api')->group(function () {
    Route::get('/owner/profile', [AuthController::class, 'fetchOwner']);
    Route::post('/owner/logout', [AuthController::class, 'logoutOwner']);

    Route::get('/my-bengkel', [BengkelController::class, 'myBengkel']);

    Route::prefix('bengkel')->group(function () {
        Route::get('/my-bengkel', [BengkelController::class, 'myBengkel']);
        Route::post('/', [BengkelController::class, 'store']);
        Route::put('/{id}', [BengkelController::class, 'update']);
        Route::delete('/{id}', [BengkelController::class, 'destroy']);
    });

    Route::prefix('jadwals')->group(function () {
        Route::get('/', [JadwalController::class, 'index']);
        Route::post('/', [JadwalController::class, 'store']);
        Route::get('/{id}', [JadwalController::class, 'show']);
        Route::put('/{id}', [JadwalController::class, 'update']);
        Route::delete('/{id}', [JadwalController::class, 'destroy']);
    });

    Route::prefix('layanans')->group(function(){
        Route::get('/', [LayananController::class, 'index']);
        Route::post('/', [LayananController::class, 'store']);
        Route::get('/{id}', [LayananController::class, 'show']);
        Route::put('/{id}', [LayananController::class, 'update']);
        Route::delete('/{id}', [LayananController::class, 'destroy']);
    });

     Route::prefix('products-owner')->group(function(){
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    Route::prefix('transaction-owner')->group(function(){
        Route::get('/', [BengkelTransactionController::class, 'index']);
        Route::get('/{id}', [BengkelTransactionController::class, 'show']);
        Route::put('/{id}', [BengkelTransactionController::class, 'update']);
    });

     Route::prefix('bengkel-booking')->group(function(){
        Route::get('/', [BengkelBookingController::class, 'index']);
        Route::get('/{id}', [BengkelBookingController::class, 'show']);
        Route::put('/{id}', [BengkelBookingController::class, 'update']);
    });

});

Route::post('/midtrans-callback', [CheckoutController::class, 'callback']);
