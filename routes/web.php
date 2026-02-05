<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminPencairanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BengkelBookingController;
use App\Http\Controllers\BengkelController;
use App\Http\Controllers\BengkelTransactionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\MerkMobilController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileUserController;
use App\Http\Controllers\ServiceController;

use App\Http\Controllers\WithdrawRequestController;
use App\Models\PemilikBengkel;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/servicepage', [ServiceController::class, 'index'])->name('service');
Route::get('/kelurahan/{kecamatan_id}', [ServiceController::class, 'getKelurahans']);
Route::get('/detailbengkelpage/{id}', [ServiceController::class, 'detailBengkel']);
Route::get('/productpage', [PageController::class, 'index'])->name('product');
Route::get('/detailproductpage/{id}', [PageController::class, 'detailProduct']);


// AUTH
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('login', [AuthController::class, 'doLogin']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::get('/userregister', [AuthController::class, 'userregister'])->name('userregister');
Route::get('/get-kelurahans/{kecamatan_id}', [AuthController::class, 'getKelurahans']);
Route::get('/ownerregister', [AuthController::class, "ownerregister"])->name('ownerregister');
Route::post('/userregister', [AuthController::class, "douserregister"])->name('do.userregister');
Route::post('/ownerregister', [AuthController::class, "doownerregister"])->name('do.ownerregister');

Route::get('/forgot-password', function () {
    return view('forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $user = User::where('email', $request->email)->first();
    $owner = PemilikBengkel::where('email', $request->email)->first();

    if ($user) {
        $status = Password::sendResetLink(
            $request->only('email')
        );
    } elseif ($owner) {
        $status = Password::broker('owners')->sendResetLink(
            $request->only('email')
        );
    } else {
        $status = Password::INVALID_USER;
    }

    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $user = User::where('email', $request->email)->first();
    $owner = PemilikBengkel::where('email', $request->email)->first();

    if ($user) {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );
    } elseif ($owner) {
        $status = Password::broker('owners')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (PemilikBengkel $owner, string $password) {
                $owner->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $owner->save();

                event(new PasswordReset($owner));
            }
        );
    } else {
        $status = Password::INVALID_USER;
    }

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

// USER
Route::middleware(['auth:web'])->group(function () {
    // Profile
    Route::get('/profileuser', [ProfileUserController::class, 'showuser']);
    Route::get('/profileuser/{id}', [ProfileUserController::class, 'showdetailuser']);
    Route::get('/profileuser/{id}/edit', [ProfileUserController::class, 'edit']);
    Route::post('/profileuser/{id}', [ProfileUserController::class, 'storedetailuser']);
    Route::put('/profileuser/{id}', [ProfileUserController::class, 'updatedetailuser']);
    // Booking
    Route::get('/profile-booking', [ProfileUserController::class, 'bookingList']);
    Route::get('/profile-booking/{id}', [ProfileUserController::class, 'showBooking']);
    Route::get('/booking/add/{id}', [ServiceController::class, 'bookingPage']);
    Route::post('/booking', [BookingController::class, 'booking']);
    Route::get('/api/bengkel/{bengkel}/booked-times', [BookingController::class, 'getBookedTimes']);
    // Transaction
    Route::get('/profile-transaction', [ProfileUserController::class, 'transactionList']);
    Route::get('/profile-transaction/{transaction}', [ProfileUserController::class, 'showTransaction'])->name('customer.show.transaction');
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/{product}', [CartController::class, 'addToCart'])->name('add_toCart');
    Route::patch('/cart/{cart}', [CartController::class, 'updateCart'])->name('update_cart');
    // Route::delete('/cart/{cart}', [CartController::class, 'deleteCart'])->name('delete_cart');

    Route::delete('/cart/{cart}/delete', [CartController::class, 'deleteCart'])->name('delete_cart');


    // Checkout
    Route::get('/checkout-page', [CheckoutController::class, 'checkoutPage'])->name('checkout.page');
    Route::post('/checkout', [CheckoutController::class, 'checkoutProcess'])->name('checkout.process');
});

// ADMIN
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin-index', [AdminController::class, 'index'])->name('adminindex');
    // user
    Route::get('/admin-listuser', [AdminController::class, 'listuser'])->name('showlistuser');
    Route::get('/admin-detailuser/{id}', [AdminController::class, 'detailuser'])->name('detailuser');
    Route::get('/admin-listuser/{id}/delete', [AdminController::class, 'destroyuser'])->name('deletelistuser');
    // owner
    Route::get('/admin-listowner', [AdminController::class, 'listowner'])->name('showlistowner');
    Route::get('/admin-detailowner/{id}', [AdminController::class, 'detailowner'])->name('detailowner');
    Route::get('/admin-listowner/{id}/delete', [AdminController::class, 'destroyowner'])->name('deletelistowner');
    // bengkel
    Route::get('/admin-listbengkel', [AdminController::class, 'listbengkel'])->name('showlistbengkel');
    Route::get('/admin-detailbengkel/{id}', [AdminController::class, 'detailbengkel'])->name('detailbengkel');
    Route::get('/admin-listbengkel/{id}/delete', [AdminController::class, 'destroybengkel'])->name('deletelistbengkel');
    // booking
    Route::get('/admin-listbooking', [AdminController::class, 'listbooking'])->name('showlistbooking');
    Route::get('/admin-detailbooking/{id}', [AdminController::class, 'detailbooking'])->name('detailbooking');
    // transaction
    Route::get('/admin-transaction', [AdminController::class, 'listtransaction'])->name('showlisttransaction');
    Route::get('/admin-transaction/{transaction}', [AdminController::class, 'detailtransaction'])->name('admin.show.transaction');
    Route::put('/admin-transaction/{id}', [AdminController::class, 'updatetransaction'])->name('admin.update.transaction');
    // pencairan
    Route::get('/admin-pencairan', [AdminPencairanController::class, 'index'])->name('admin.pencairan');
    Route::get('/admin-pencairan/{id}/edit', [AdminPencairanController::class, 'edit'])->name('admin.pencairan.edit');
    Route::put('/admin-pencairan/{id}', [AdminPencairanController::class, 'update'])->name('admin.pencairan.update');
    // category
    Route::get('/admin-category', [CategoryController::class, 'index'])->name('admin.category.index');
    Route::get('/admin-category/create', [CategoryController::class, 'create'])->name('admin.category.create');
    Route::post('/admin-category', [CategoryController::class, 'store'])->name('admin.category.store');
    Route::get('/admin-category/{id}/edit', [CategoryController::class, 'edit'])->name('admin.category.edit');
    Route::put('/admin-category/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
    Route::delete('/admin-category/{id}', [CategoryController::class, 'destroy'])->name('admin.category.destroy');
    // merk mobil
    Route::resource('admin-merk-mobil', MerkMobilController::class)->names([
        'index' => 'merk-mobil.index',
        'create' => 'merk-mobil.create',
        'store' => 'merk-mobil.store',
        'show' => 'merk-mobil.show',
        'edit' => 'merk-mobil.edit',
        'update' => 'merk-mobil.update',
        'destroy' => 'merk-mobil.destroy',
    ]);
});

// BENGKEL
Route::prefix('/owner')->middleware('auth:owner')->group(function () {
    Route::get('/', [DashboardController::class, 'dashboard']);
    // bengkel
    Route::get('/bengkel', [BengkelController::class, 'index']);
    Route::get('/get-kelurahan/{kecamatan_id}', [BengkelController::class, 'getKelurahans']);
    Route::get('/bengkel/add', [BengkelController::class, 'create']);
    Route::post('/bengkel', [BengkelController::class, 'store']);
    Route::get('/bengkel/{id}/edit', [BengkelController::class, 'edit']);
    Route::put('/bengkel/{id}', [BengkelController::class, 'update']);
    Route::get('/bengkel/{id}/delete', [BengkelController::class, 'destroy']);
    // layanan
    Route::get('/layanan', [LayananController::class, 'index']);
    Route::get('/layanan/add', [LayananController::class, 'create']);
    Route::post('/layanan', [LayananController::class, 'store']);
    Route::get('/layanan/{id}/edit', [LayananController::class, 'edit']);
    Route::put('/layanan/{id}', [LayananController::class, 'update']);
    Route::get('/layanan/{id}/delete', [LayananController::class, 'destroy']);
    // Product
    Route::get('/product', [ProductController::class, 'index']);
    Route::get('/product/add', [ProductController::class, 'create']);
    Route::post('/product', [ProductController::class, 'store']);
    Route::get('/product/{id}/edit', [ProductController::class, 'edit']);
    Route::put('/product/{id}', [ProductController::class, 'update']);
    Route::get('/product/{id}/delete', [ProductController::class, 'destroy']);
    // jadwal
    Route::get('/jadwal', [JadwalController::class, 'index']);
    Route::get('/jadwal/add', [JadwalController::class, 'create']);
    Route::post('/jadwal', [JadwalController::class, 'store']);
    Route::get('/jadwal/{id}/edit', [JadwalController::class, 'edit']);
    Route::put('/jadwal/{id}', [JadwalController::class, 'update']);
    Route::get('/jadwal/{id}/delete', [JadwalController::class, 'destroy']);
    // booking
    Route::get('/booking', [BengkelBookingController::class, 'booking'])->name('bengkelbooking');
    Route::get('/booking/{id}/edit', [BengkelBookingController::class, 'editBooking']);
    Route::get('/booking/{id}', [BengkelBookingController::class, 'detailBooking']);
    Route::put('/booking/{id}', [BengkelBookingController::class, 'updateBooking'])->name('updatebooking');
    // transaction
    Route::get('/transaction', [BengkelTransactionController::class, 'index']);
    Route::get('/transaction/{transaction}', [BengkelTransactionController::class, 'show'])->name('mitra.show.transaction');
    Route::get('/transaction/{id}/edit', [BengkelTransactionController::class, 'edit']);
    Route::put('/transaction/{id}', [BengkelTransactionController::class, 'update'])->name('updatetransaction');
    
    Route::get('/transaction/add/{booking}', [BengkelTransactionController::class, 'create'])->name('mitra.add.transaction');
    Route::post('/transaction/{id}', [BengkelTransactionController::class, 'addToCart'])->name('add.to.cart');
    Route::delete('/transaction/cart/{id}', [BengkelTransactionController::class, 'removeFromCart'])->name('remove.from.cart');
    Route::post('/checkout/process/owner', [BengkelTransactionController::class, 'checkoutProcessForOwner'])->name('checkout.process.owner');
    // Pencairan
    Route::get('/withdrawal_request', [WithdrawRequestController::class, 'index'])->name('owner.withdrawal_request');
    Route::get('/withdrawal_request/add', [WithdrawRequestController::class, 'create'])->name('owner.withdrawal_requests.add');
    Route::post('/withdrawal_request', [WithdrawRequestController::class, 'store'])->name('owner.withdrawal_requests.store');
    Route::get('/withdrawal_request/detail/{id}', [WithdrawRequestController::class, 'detail'])->name('owner.withdrawal_requests.detail');
});

Route::match(['get', 'post'], '/botman', [ChatbotController::class, 'handle']);
