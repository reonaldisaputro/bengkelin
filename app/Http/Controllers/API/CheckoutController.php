<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\DetailTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        $transaction_code = 'TRANS-' . mt_rand(100000, 999999);

        $carts = Cart::with(['product', 'user', 'bengkel'])
            ->where('user_id', $user->id)
            ->get();

        if ($carts->isEmpty()) {
            return ResponseFormatter::error(null, 'Keranjang kosong', 400);
        }

        $bengkel_id = $carts->first()->bengkel->id;

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

        $itemDetails = [];

        foreach ($carts as $cart) {
            DetailTransaction::create([
                'transaction_id' => $transaction->id,
                'product_id' => $cart->product->id,
                'qty' => $cart->quantity,
                'product_price' => $cart->product->price,
            ]);

            $product = Product::find($cart->product_id);
            $product->decrement('stock', $cart->quantity);

            $itemDetails[] = [
                'id' => $cart->product->id,
                'price' => $cart->product->price,
                'quantity' => $cart->quantity,
                'name' => $cart->product->name,
            ];
        }

        Cart::where('user_id', $user->id)->delete();

        // Midtrans Config
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
                'address' => $user->address,
            ],
            'enabled_payments' => [
                'gopay', 'permata_va', 'bank_transfer'
            ],
            'vtweb' => []
        ];

        $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

        return ResponseFormatter::success(['payment_url' => $paymentUrl], 'Checkout berhasil');
    }

    public function callback(Request $request)
    {
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        $notification = new Notification();

        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        $transaction = Transaction::findOrFail($order_id);

        if ($status == 'capture') {
            $transaction->payment_status = $fraud === 'challenge' ? 'Pending' : 'Success';
        } elseif ($status == 'settlement') {
            $transaction->payment_status = 'Success';
        } elseif (in_array($status, ['pending', 'deny', 'expire', 'cancel'])) {
            $transaction->payment_status = 'Cancelled';
        }

        $transaction->save();

        return response()->json(['status' => 'callback received']);
    }
}
