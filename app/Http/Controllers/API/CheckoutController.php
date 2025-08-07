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
    public function getCheckoutSummary(Request $request)
    {

        $user = Auth::user()->load('kecamatan', 'kelurahan');

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $carts = Cart::with('product', 'bengkel.kecamatan')
                     ->where('user_id', $user->id)
                     ->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'Keranjang Anda kosong.'], 404);
        }

        $sub_total = 0;
        $total_weight = 0;
        $order_items = [];

        foreach ($carts as $cart) {
            if ($cart->product) {
                $item_total = $cart->product->price * $cart->quantity;
                $sub_total += $item_total;
                $total_weight += $cart->product->weight * $cart->quantity;

                $order_items[] = [
                    'product_name' => $cart->product->name,
                    'quantity'     => $cart->quantity,
                    'price'        => (float) $cart->product->price,
                    'total_price'  => $item_total,
                    'image_url'    => $cart->product->image_url ?? null,
                ];
            }
        }
        
        $bengkel = $carts->first()->bengkel;
        $ongkir = 0;

        if ($bengkel && $bengkel->kecamatan_id == $user->kecamatan->id) {
            $ongkir = 15000;
        } else {
            $ongkir = 25000;
        }

        if ($total_weight > 10) {
            $extra_weight = $total_weight - 10;
            $ongkir += $extra_weight * 10000;
        }

        $administrasi = 0.05 * $sub_total;
        $grand_total = $sub_total + $ongkir + $administrasi;

        $response = [
            'user_info' => [
                'name'      => $user->name,
                'email'     => $user->email,
                'phone'     => $user->phone_number,
                'kecamatan' => $user->kecamatan->name,
                'kelurahan' => $user->kelurahan->name,
                'address'   => $user->alamat,
            ],
            'order_items' => $order_items,
            'cost_summary' => [
                'sub_total'     => $sub_total,
                'shipping_cost' => $ongkir,
                'admin_fee'     => $administrasi,
                'grand_total'   => $grand_total,
            ],
        ];

        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
    
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

        foreach ($carts as $cart) {
            $bengkel_id = $cart->bengkel->id;
        }

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

        $orderId = 'ORDER-' . $transaction->id . '-' . now()->timestamp;

        $midtrans = [
            'transaction_details' => [
                'order_id' => $orderId,
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
