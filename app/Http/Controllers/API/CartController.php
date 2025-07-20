<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;

class CartController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        \Log::info("User ID: " . $userId);

        $carts = Cart::with(['product', 'bengkel'])->where('user_id', $userId)->get();

        if ($carts->isEmpty()) {
            return ResponseFormatter::success([], 'Keranjang kosong');
        }

        return ResponseFormatter::success($carts, 'Data keranjang berhasil diambil');
    }


    // POST /api/cart/add
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'bengkel_id' => 'required|exists:bengkels,id',
        ]);

        $user_id = Auth::id();
        $product = Product::findOrFail($request->product_id);

        // Cek jika ada cart yang tidak sesuai bengkel
        $existingCarts = Cart::where('user_id', $user_id)->get();

        if ($existingCarts->isNotEmpty()) {
            $bengkelInCart = $existingCarts->first()->bengkel_id;
            if ($bengkelInCart != $request->bengkel_id) {
                return ResponseFormatter::error(null, 'Anda hanya dapat menambahkan produk dari bengkel yang sama', 409);
            }
        }

        $existingCart = Cart::where('user_id', $user_id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingCart) {
            $newQty = $existingCart->quantity + $request->quantity;
            if ($newQty > $product->stock) {
                return ResponseFormatter::error(null, 'Jumlah melebihi stok tersedia', 422);
            }

            $existingCart->update(['quantity' => $newQty]);
        } else {
            if ($request->quantity > $product->stock) {
                return ResponseFormatter::error(null, 'Jumlah melebihi stok tersedia', 422);
            }

            Cart::create([
                'user_id' => $user_id,
                'product_id' => $product->id,
                'bengkel_id' => $request->bengkel_id,
                'quantity' => $request->quantity,
                'price' => $product->price,
            ]);
        }

        return ResponseFormatter::success(null, 'Produk berhasil ditambahkan ke keranjang');
    }

    // PUT /api/cart/{id}
    public function update(Request $request, $id)
    {
        $cart = Cart::with(['product', 'bengkel'])->findOrFail($id);

        if ($cart->user_id !== Auth::id()) {
            return ResponseFormatter::error(null, 'Akses ditolak', 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $cart->product->stock,
        ]);

        $cart->update(['quantity' => $request->quantity]);

        return ResponseFormatter::success([
            'cart' => $cart,
            'new_price' => $cart->product->price * $cart->quantity,
        ], 'Jumlah produk berhasil diperbarui');
    }

    // DELETE /api/cart/{id}
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);

        if ($cart->user_id !== Auth::id()) {
            return ResponseFormatter::error(null, 'Akses ditolak', 403);
        }

        $cart->delete();

        return ResponseFormatter::success(null, 'Item berhasil dihapus dari keranjang');
    }
}
