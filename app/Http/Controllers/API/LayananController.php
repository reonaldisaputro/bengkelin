<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Bengkel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LayananController extends Controller
{
    public function index()
    {
        $bengkelIds = Bengkel::where('pemilik_id', Auth::id())->pluck('id');
        $layanans = Layanan::with('bengkel')->whereIn('bengkel_id', $bengkelIds)->orderBy('created_at', 'desc')->get();

        return ResponseFormatter::success($layanans, 'Data layanan berhasil diambil.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'layanan_name' => 'required|string|max:255',
            'layanan_price' => 'required|numeric|min:0'
        ]);

        $bengkel = Bengkel::where('pemilik_id', Auth::id())->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan.', 404);
        }

        $layanan = Layanan::create([
            'name' => $request->layanan_name,
            'price' => $request->layanan_price,
            'bengkel_id' => $bengkel->id
        ]);

        return ResponseFormatter::success($layanan, 'Layanan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $layanan = Layanan::with('bengkel')->find($id);

        if (!$layanan) {
            return ResponseFormatter::error(null, 'Layanan tidak ditemukan.', 404);
        }

        return ResponseFormatter::success($layanan, 'Detail layanan berhasil diambil.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'layanan_name' => 'required|string|max:255',
            'layanan_price' => 'required|numeric|min:0'
        ]);

        $layanan = Layanan::find($id);

        if (!$layanan) {
            return ResponseFormatter::error(null, 'Layanan tidak ditemukan.', 404);
        }

        $bengkel = Bengkel::where('pemilik_id', Auth::id())->first();
        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan.', 404);
        }

        $layanan->update([
            'name' => $request->layanan_name,
            'price' => $request->layanan_price,
            'bengkel_id' => $bengkel->id
        ]);

        return ResponseFormatter::success($layanan, 'Layanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $layanan = Layanan::find($id);

        if (!$layanan) {
            return ResponseFormatter::error(null, 'Layanan tidak ditemukan.', 404);
        }

        $layanan->delete();

        return ResponseFormatter::success(null, 'Layanan berhasil dihapus.');
    }
}
