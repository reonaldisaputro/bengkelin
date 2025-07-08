<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Bengkel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function index()
    {
        $bengkels = Bengkel::where('pemilik_id', Auth::id())->get();
        $bengkel_ids = $bengkels->pluck('id');
        $jadwals = Jadwal::with('bengkel')->whereIn('bengkel_id', $bengkel_ids)->orderBy('created_at', 'desc')->get();

        return ResponseFormatter::success($jadwals, 'Data jadwal berhasil diambil.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'senin_buka' => 'nullable',
            'senin_tutup' => 'nullable',
            'selasa_buka' => 'nullable',
            'selasa_tutup' => 'nullable',
            'rabu_buka' => 'nullable',
            'rabu_tutup' => 'nullable',
            'kamis_buka' => 'nullable',
            'kamis_tutup' => 'nullable',
            'jumat_buka' => 'nullable',
            'jumat_tutup' => 'nullable',
            'sabtu_buka' => 'nullable',
            'sabtu_tutup' => 'nullable',
            'minggu_buka' => 'nullable',
            'minggu_tutup' => 'nullable',
        ]);

        $bengkel = Bengkel::where('pemilik_id', Auth::id())->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan.', 404);
        }

        $jadwal = Jadwal::create(array_merge(
            $request->only([
                'senin_buka', 'senin_tutup', 'selasa_buka', 'selasa_tutup',
                'rabu_buka', 'rabu_tutup', 'kamis_buka', 'kamis_tutup',
                'jumat_buka', 'jumat_tutup', 'sabtu_buka', 'sabtu_tutup',
                'minggu_buka', 'minggu_tutup'
            ]),
            ['bengkel_id' => $bengkel->id]
        ));

        return ResponseFormatter::success($jadwal, 'Jadwal berhasil ditambahkan.');
    }

    public function show($id)
    {
        $jadwal = Jadwal::with('bengkel')->find($id);

        if (!$jadwal) {
            return ResponseFormatter::error(null, 'Jadwal tidak ditemukan', 404);
        }

        return ResponseFormatter::success($jadwal, 'Detail jadwal berhasil diambil.');
    }

    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::find($id);

        if (!$jadwal) {
            return ResponseFormatter::error(null, 'Jadwal tidak ditemukan.', 404);
        }

        $jadwal->update($request->only([
            'senin_buka', 'senin_tutup', 'selasa_buka', 'selasa_tutup',
            'rabu_buka', 'rabu_tutup', 'kamis_buka', 'kamis_tutup',
            'jumat_buka', 'jumat_tutup', 'sabtu_buka', 'sabtu_tutup',
            'minggu_buka', 'minggu_tutup'
        ]));

        return ResponseFormatter::success($jadwal, 'Jadwal berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jadwal = Jadwal::find($id);

        if (!$jadwal) {
            return ResponseFormatter::error(null, 'Jadwal tidak ditemukan.', 404);
        }

        $jadwal->delete();

        return ResponseFormatter::success(null, 'Jadwal berhasil dihapus.');
    }
}
