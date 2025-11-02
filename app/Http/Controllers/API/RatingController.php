<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\DetailTransaction;
use App\Models\Product;
use App\Models\Rating;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RatingController extends Controller
{
    // POST /v1/ratings
    public function store(Request $r)
    {
        $data = $r->validate([
            'detail_transaction_id' => ['required','integer','exists:detail_transactions,id'],
            'stars'   => ['required','integer','min:1','max:5'],
            'comment' => ['nullable','string','max:2000'],
        ]);

        $detail = DetailTransaction::with(['transaction','product'])->findOrFail($data['detail_transaction_id']);

        // Pastikan ini milik user & transaksi selesai
        if ($detail->transaction->user_id !== Auth::id()) {
            return ResponseFormatter::error(null,'Tidak berhak menilai item ini.',403);
        }
        if (!in_array(strtolower($detail->transaction->payment_status), ['paid','success','completed'])
            && !in_array(strtolower($detail->transaction->shipping_status), ['delivered','completed'])) {
            // sesuaikan logika "selesai" versi kamu
            return ResponseFormatter::error(null,'Transaksi belum selesai.',422);
        }

        // Cegah duplikasi rating per baris item
        $existing = Rating::where('user_id', Auth::id())
            ->where('detail_transaction_id', $detail->id)
            ->first();
        if ($existing) {
            // Update kalau mau idempotent
            $existing->update([
                'stars' => $data['stars'],
                'comment' => $data['comment'] ?? $existing->comment,
            ]);
            $this->recomputeProductCache($detail->product_id);
            return ResponseFormatter::success($existing->load('product'), 'Rating diperbarui.');
        }

        $rating = Rating::create([
            'user_id' => Auth::id(),
            'product_id' => $detail->product_id,
            'transaction_id' => $detail->transaction_id,
            'detail_transaction_id' => $detail->id,
            'stars' => $data['stars'],
            'comment' => $data['comment'] ?? null,
        ]);

        $this->recomputeProductCache($detail->product_id);

        return ResponseFormatter::success($rating->load('product'), 'Rating berhasil disimpan.');
    }

    // GET /v1/ratings/product/{product}
    public function listByProduct(Product $product)
    {
        $ratings = $product->ratings()->with('user')->latest()->paginate(10);
        return ResponseFormatter::success($ratings, 'Daftar rating produk.');
    }

    private function recomputeProductCache(int $productId): void
    {
        // cepat & aman, tanpa trigger database
        $product = Product::withAvg('ratings','stars')->withCount('ratings')->find($productId);
        if ($product) {
            $product->update([
                'avg_rating' => round($product->ratings_avg_stars ?? 0, 1),
                'ratings_count' => $product->ratings_count ?? 0,
            ]);
        }
    }
}