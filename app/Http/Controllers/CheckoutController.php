<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use App\Models\Cart;
use App\Models\User;
use Midtrans\Config;
use App\Models\Product;
use Midtrans\Notification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\DetailTransaction;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    public function checkoutPage(Request $request)
    {
        $carts = Cart::with('bengkel')->where('user_id', Auth::user()->id)->get();
        $user = User::with('kecamatan', 'kelurahan')->where('id', Auth::user()->id)->first();
        return view('user.checkoutpage', ['carts' => $carts, 'user' => $user]);
    }

    public function checkoutProcess(Request $request)
    {
        $user = Auth::user();
        // Checkout Process
        $transaction_code = 'TRANS-' . mt_rand(000, 999);

        $carts = Cart::with(['product', 'user', 'bengkel'])
            ->where('user_id', Auth::user()->id)
            ->get();

        foreach ($carts as $cart) {
            $bengkel_id = $cart->bengkel->id;
        }

        // Create Transaction
        $transaction = Transaction::create([
            'transaction_code' => $transaction_code,
            'user_id' => $user->id,
            'bengkel_id' => $bengkel_id,
            'booking_id' => $request->booking_id,
            'layanan_id' => $request->layanan_id,
            'product_id' => $request->product_id,
            'payment_status' => 'Pending',
            'shipping_status' => 'Pending',
            'ongkir' => $request->ongkir,
            'administrasi' => $request->administrasi,
            'grand_total' => $request->grand_total
        ]);

        // Create Detail Transaction
        foreach ($carts as $cart) {
            DetailTransaction::create([
                'transaction_id' => $transaction->id,
                'product_id' => $cart->product->id,
                'qty' => $cart->quantity,
                'product_price' => $cart->product->price,
            ]);

            // Reduce product stock if transaction successful
            $product = Product::find($cart->product_id);

            $product->update([
                'stock' => $product->stock - $cart->quantity
            ]);

            // Add item details to the array
            $itemDetails[] = [
                'id' => $cart->product->id,
                'price' => $cart->product->price,
                'quantity' => $cart->qty,
                'name' => $cart->product->name,
            ];
        }

        // Delete Cart Data
        Cart::with(['product', 'user', 'bengkel'])
            ->where('user_id', Auth::user()->id)
            ->delete();

        // Midtrans Configuration
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $orderId = 'ORDER-' . $transaction->id . '-' . now()->timestamp;

        // Create array for Midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => $orderId, // Use unique order_id
                'gross_amount' => $transaction->grand_total,
            ],
            'customer_details' => [
                'first_name' => Auth::user()->name,
                'phone' => Auth::user()->phone_number,
                'email' => Auth::user()->email,
                'address' => Auth::user()->address,
            ],
            'enabled_payments' => [
                'gopay', 'permata_va', 'bank_transfer'
            ],
            'vtweb' => []
        ];

        // Get Snap Payment Page URL
        $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

        // Redirect to Snap Payment Page
        return redirect($paymentUrl);
    }

    public function callback(Request $request)
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Instance notifikasi Midtrans
        $notification = new Notification();

        // Assign ke variabel untuk memudahkan penulisan kode
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($order_id);

        // Handle status notifikasi
        if ($status == 'capture') {
            if ($type == 'credit_cart') {
                if ($fraud == 'challenge') {
                    $transaction->payment_status = 'PENDING';
                    $transaction->update(['payment_status' => 'Pending']);
                } else {
                    $transaction->payment_status = 'SUCCESS';
                    $transaction->update(['payment_status' => 'Success']);
                }
            }
        } else if ($status == 'settlement') {
            $transaction->payment_status = 'SUCCESS';
            $transaction->update(['payment_status' => 'Success']);
        } else if ($status == 'pending') {
            $transaction->payment_status = 'PENDING';
            $transaction->update(['payment_status' => 'Pending']);
        } else if ($status == 'deny') {
            $transaction->payment_status = 'CANCELLED';
            $transaction->update(['payment_status' => 'Cancelled']);
        } else if ($status == 'expire') {
            $transaction->payment_status = 'CANCELLED';
            $transaction->update(['payment_status' => 'Cancelled']);
        } else if ($status == 'cancel') {
            $transaction->payment_status = 'CANCELLED';
            $transaction->update(['payment_status' => 'Cancelled']);
        }

        // Simpan transaksi
        $transaction->save();
    }
}
