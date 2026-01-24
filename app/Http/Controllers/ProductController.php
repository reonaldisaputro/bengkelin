<?php

namespace App\Http\Controllers;

use App\Models\Bengkel;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $item['bengkels'] = Bengkel::where('pemilik_id', Auth::id())->get();
        $bengkel_ids = $item['bengkels']->map(function ($bengkel) {
            return $bengkel->id;
        });
        $products['products'] = Product::with('bengkel')->whereIn('bengkel_id', $bengkel_ids)->get();
        return view("mitra.product.index", $products);
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view("mitra.product.add", compact('categories'));
    }

    public function store(Request $request)
    {
        $imageName = time() . '.' . $request->image->extension();

        $request->image->move(public_path('images'), $imageName);

        $owner_id = Auth::id();
        $bengkel_id = Bengkel::where("pemilik_id", $owner_id)->first()->id;
        $products = new Product();
        $products->bengkel_id = $bengkel_id;
        $products->category_id = $request->category_id;
        $products->image = $imageName;
        $products->name = $request->name;
        $products->description = $request->description;
        $products->price = $request->price;
        $products->weight = $request->weight;
        $products->stock = $request->stock;
        $products->save();

        return redirect('owner/product')->with('success', 'Product berhasil ditambahkan');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('mitra.product.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();

            $request->image->move(public_path('images'), $imageName);

            $product->image = $imageName;
        }

        $product->category_id = $request->category_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->weight = $request->weight;
        $product->stock = $request->stock;
        $product->save();

        return redirect('owner/product')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy($id)
    {
        $data = Product::findOrFail($id);

        if ($data->image) {
            // hapus gambar jika ada
            File::delete(public_path('images/' . $data->image));
        }

        $data->delete();

        return redirect('owner/product')->with('success', 'Product berhasil dihapus');
    }
}
