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
     * Tampilkan daftar produk, bisa dengan pencarian dan filter kategori
     *
     * Query params:
     * - keyword: string (search by name/description)
     * - category_id: int (filter by category)
     * - bengkel_id: int (filter by bengkel)
     * - min_price: int (minimum price)
     * - max_price: int (maximum price)
     * - sort_by: string (name, price, created_at)
     * - sort_order: string (asc, desc)
     * - per_page: int (default 10)
     */
    public function index(Request $request)
    {
        $query = Product::with(['bengkel', 'category']);

        // Search by keyword (name or description)
        if ($request->filled('keyword')) {
            $keyword = $request->query('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Filter by bengkel
        if ($request->filled('bengkel_id')) {
            $query->where('bengkel_id', $request->query('bengkel_id'));
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->query('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->query('max_price'));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at');
        $sortOrder = $request->query('sort_order', 'desc');
        $allowedSorts = ['name', 'price', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $products = $query->paginate($perPage);

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
