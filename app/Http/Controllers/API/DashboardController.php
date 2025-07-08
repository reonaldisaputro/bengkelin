<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Booking;
use App\Models\Layanan;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $pemilik_id = Auth::id();
        $bengkel = Bengkel::where('pemilik_id', $pemilik_id)->first();

        if (!$bengkel) {
            return ResponseFormatter::success([
                'layanan_count' => 0,
                'booking_count' => 0,
                'product_count' => 0,
                'transaction_count' => 0,
                'bengkel' => null
            ], 'Data dashboard kosong, belum ada bengkel yang terdaftar.');
        }

        $bengkel_id = $bengkel->id;

        $data = [
            'layanan_count' => Layanan::where('bengkel_id', $bengkel_id)->count(),
            'booking_count' => Booking::where('bengkel_id', $bengkel_id)->count(),
            'product_count' => Product::where('bengkel_id', $bengkel_id)->count(),
            'transaction_count' => Transaction::where('bengkel_id', $bengkel_id)->count(),
            'bengkel' => $bengkel
        ];

        return ResponseFormatter::success($data, 'Data dashboard berhasil diambil.');
    }
}
