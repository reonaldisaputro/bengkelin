<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Ambil 3 produk terbaru untuk halaman home
     */
    public function home()
    {
        $products = Product::with('bengkel')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return ResponseFormatter::success($products, 'Data produk terbaru berhasil diambil.');
    }

    /**
     * Tampilkan daftar produk, bisa dengan pencarian
     */
    public function index(Request $request)
    {
        $keyword = $request->query('keyword');

        $query = Product::with('bengkel');

        if ($keyword) {
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        $products = $query->paginate(10);

        return ResponseFormatter::success($products, 'Daftar produk berhasil diambil.');
    }

    /**
     * Tampilkan detail produk berdasarkan ID
     */
    public function detailProduct($id)
    {
        $product = Product::with('bengkel')->find($id);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan.', 404);
        }

        return ResponseFormatter::success($product, 'Detail produk berhasil diambil.');
    }
}
