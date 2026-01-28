<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Bengkel;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Layanan;
use App\Models\BengkelCart;
use App\Models\Transaction;
use App\Models\DetailTransaction;
use App\Helpers\ResponseFormatter;
use Midtrans\Snap;
use Midtrans\Config;

class BengkelTransactionController extends Controller
{
    public function index()
    {
        $ownerId = Auth::id();
        $bengkel = Bengkel::where('pemilik_id', $ownerId)->first();

        if (!$bengkel) {
            return ResponseFormatter::success([], 'Tidak ada transaksi, bengkel tidak ditemukan');
        }

        $transactions = Transaction::with('user')
            ->where('bengkel_id', $bengkel->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return ResponseFormatter::success($transactions, 'Data transaksi berhasil diambil');
    }

    public function show($id)
    {
        $transaction = Transaction::with([
            'user.kecamatan', 'user.kelurahan', 'detail_transactions.product', 'detail_transactions.layanan', 'detail_transactions.bengkel'
        ])->find($id);

        if (!$transaction) {
            return ResponseFormatter::error(null, 'Transaksi tidak ditemukan', 404);
        }

        return ResponseFormatter::success($transaction, 'Detail transaksi berhasil diambil');
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        if (!$transaction) {
            return ResponseFormatter::error(null, 'Transaksi tidak ditemukan', 404);
        }

        $request->validate([
            'shipping_status' => 'required|string',
        ]);

        $transaction->shipping_status = $request->shipping_status;
        $transaction->save();

        return ResponseFormatter::success($transaction, 'Status pengiriman berhasil diperbarui');
    }

    public function create($bookingId)
    {
        $ownerId = Auth::id();
        $bengkel = Bengkel::where('pemilik_id', $ownerId)->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return ResponseFormatter::error(null, 'Booking tidak ditemukan', 404);
        }

        $products = Product::where('bengkel_id', $bengkel->id)->get();
        $services = Layanan::where('bengkel_id', $bengkel->id)->get();
        $carts = BengkelCart::with(['product', 'layanan'])
            ->where('booking_id', $bookingId)
            ->get();

        $totalPrice = $carts->sum(fn($cart) => $cart->price * $cart->quantity);

        return ResponseFormatter::success([
            'booking' => $booking,
            'products' => $products,
            'services' => $services,
            'carts' => $carts,
            'total_price' => $totalPrice,
        ], 'Data untuk membuat transaksi berhasil diambil');
    }

    public function cartAdd(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'quantity' => 'required|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
            'layanan_id' => 'nullable|exists:layanans,id',
        ]);

        $booking = Booking::findOrFail($request->booking_id);
        $user_id = $booking->user_id;
        $bengkel_id = $booking->bengkel_id;

        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
            $existing = BengkelCart::where('booking_id', $booking->id)
                ->where('product_id', $product->id)
                ->first();

            if ($existing) {
                $existing->quantity += $request->quantity;
                $existing->save();
            } else {
                BengkelCart::create([
                    'bengkel_id' => $bengkel_id,
                    'booking_id' => $booking->id,
                    'product_id' => $product->id,
                    'user_id' => $user_id,
                    'quantity' => $request->quantity,
                    'price' => $product->price,
                ]);
            }
        } elseif ($request->layanan_id) {
            $layanan = Layanan::findOrFail($request->layanan_id);
            $exists = BengkelCart::where('booking_id', $booking->id)
                ->where('layanan_id', $layanan->id)
                ->first();

            if ($exists) {
                return ResponseFormatter::error(null, 'Layanan sudah ada di keranjang', 409);
            }

            BengkelCart::create([
                'bengkel_id' => $bengkel_id,
                'booking_id' => $booking->id,
                'layanan_id' => $layanan->id,
                'user_id' => $user_id,
                'quantity' => 1,
                'price' => $layanan->price,
            ]);
        }

        return ResponseFormatter::success(null, 'Item berhasil ditambahkan ke keranjang');
    }

    public function cartRemove($cartId)
    {
        $cart = BengkelCart::find($cartId);

        if (!$cart) {
            return ResponseFormatter::error(null, 'Item tidak ditemukan di keranjang', 404);
        }

        $cart->delete();
        return ResponseFormatter::success(null, 'Item berhasil dihapus dari keranjang');
    }

    public function checkout()
    {
        $ownerId = Auth::id();
        $bengkel = Bengkel::where('pemilik_id', $ownerId)->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        $carts = BengkelCart::with(['product', 'layanan', 'user', 'bengkel'])
            ->where('bengkel_id', $bengkel->id)
            ->get();

        if ($carts->isEmpty()) {
            return ResponseFormatter::error(null, 'Keranjang kosong', 400);
        }

        $user = $carts->first()->user;
        $booking_id = $carts->first()->booking_id;

        $totalPrice = $carts->sum(fn($cart) => $cart->price * $cart->quantity);
        $administrasi = 0.05 * $totalPrice;
        $grand_total = $totalPrice + $administrasi;

        $transaction = Transaction::create([
            'transaction_code' => 'TRANS-' . mt_rand(100, 999),
            'user_id' => $user->id,
            'bengkel_id' => $bengkel->id,
            'booking_id' => $booking_id,
            'payment_status' => 'pending',
            'shipping_status' => null,
            'ongkir' => 0,
            'administrasi' => $administrasi,
            'grand_total' => $grand_total,
        ]);

        foreach ($carts as $cart) {
            DetailTransaction::create([
                'transaction_id' => $transaction->id,
                'product_id' => $cart->product_id,
                'layanan_id' => $cart->layanan_id,
                'qty' => $cart->quantity,
                'product_price' => $cart->product?->price,
                'layanan_price' => $cart->layanan?->price,
            ]);
        }

        BengkelCart::where('bengkel_id', $bengkel->id)->delete();

        // Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $midtrans = [
            'transaction_details' => [
                'order_id' => $transaction->id,
                'gross_amount' => $transaction->grand_total,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'phone' => $user->phone_number,
                'email' => $user->email,
                'address' => $user->alamat,
            ],
            'enabled_payments' => ['gopay', 'permata_va', 'bank_transfer'],
            'vtweb' => [],
        ];

        $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

        return ResponseFormatter::success(['payment_url' => $paymentUrl], 'Transaksi berhasil dibuat, lanjutkan pembayaran');
    }
}
