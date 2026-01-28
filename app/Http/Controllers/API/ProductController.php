<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $ownerId = Auth::id();
        $bengkelIds = Bengkel::where('pemilik_id', $ownerId)->pluck('id');

        $products = Product::with(['bengkel', 'category'])
            ->whereIn('bengkel_id', $bengkelIds)
            ->get();

        return ResponseFormatter::success($products, 'List produk berhasil diambil.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'weight'      => 'required|numeric',
            'stock'       => 'required|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
        }

        $bengkelId = Bengkel::where('pemilik_id', Auth::id())->first()->id;

        $product = Product::create([
            'bengkel_id'  => $bengkelId,
            'category_id' => $request->category_id,
            'image'       => $imageName,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'weight'      => $request->weight,
            'stock'       => $request->stock,
        ]);

        return ResponseFormatter::success($product, 'Produk berhasil ditambahkan.');
    }

    public function show($id)
    {
        $product = Product::with(['bengkel', 'category'])->find($id);

        if (!$product) {
            return ResponseFormatter::error(null, 'Produk tidak ditemukan', 404);
        }

        return ResponseFormatter::success($product, 'Detail produk berhasil diambil.');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $product->update([
            'name'        => $request->name ?? $product->name,
            'category_id' => $request->category_id ?? $product->category_id,
            'description' => $request->description ?? $product->description,
            'price'       => $request->price ?? $product->price,
            'weight'      => $request->weight ?? $product->weight,
            'stock'       => $request->stock ?? $product->stock,
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                File::delete(public_path('images/' . $product->image));
            }

            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);

            $product->update(['image' => $imageName]);
        }

        return ResponseFormatter::success($product, 'Produk berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image) {
            File::delete(public_path('images/' . $product->image));
        }

        $product->delete();

        return ResponseFormatter::success(null, 'Produk berhasil dihapus.');
    }
}
