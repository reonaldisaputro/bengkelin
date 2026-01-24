<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get list of active categories
     *
     * Query params:
     * - with_products: bool (include product count)
     * - sort_by: string (name, created_at)
     * - sort_order: string (asc, desc)
     */
    public function index(Request $request)
    {
        $query = Category::where('is_active', true);

        // Include product count if requested
        if ($request->boolean('with_products')) {
            $query->withCount('products');
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'name');
        $sortOrder = $request->query('sort_order', 'asc');
        $allowedSorts = ['name', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        $categories = $query->get();

        return ResponseFormatter::success($categories, 'Daftar kategori berhasil diambil.');
    }

    /**
     * Get category detail with products
     */
    public function show($id)
    {
        $category = Category::where('is_active', true)
            ->withCount('products')
            ->find($id);

        if (!$category) {
            return ResponseFormatter::error(null, 'Kategori tidak ditemukan.', 404);
        }

        return ResponseFormatter::success($category, 'Detail kategori berhasil diambil.');
    }

    /**
     * Get products by category
     *
     * Query params:
     * - keyword: string (search by name)
     * - sort_by: string (name, price, created_at)
     * - sort_order: string (asc, desc)
     * - per_page: int (default 10)
     */
    public function products(Request $request, $id)
    {
        $category = Category::where('is_active', true)->find($id);

        if (!$category) {
            return ResponseFormatter::error(null, 'Kategori tidak ditemukan.', 404);
        }

        $query = $category->products()->with('bengkel');

        // Search by keyword
        if ($request->filled('keyword')) {
            $keyword = $request->query('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('description', 'LIKE', '%' . $keyword . '%');
            });
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

        return ResponseFormatter::success([
            'category' => $category,
            'products' => $products,
        ], 'Produk dalam kategori berhasil diambil.');
    }
}
