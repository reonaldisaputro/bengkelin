<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Specialist;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $kecamatan_id = $request->kecamatan_id;
        $kelurahan_id = $request->kelurahan_id;
        $specialist_id = $request->specialist_id;

        $query = Bengkel::with(['specialists', 'kecamatan', 'kelurahan']);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('alamat', 'like', '%' . $keyword . '%');
            });
        }

        if ($kecamatan_id) {
            $query->where('kecamatan_id', $kecamatan_id);
        }

        if ($kelurahan_id) {
            $query->where('kelurahan_id', $kelurahan_id);
        }

        if ($specialist_id) {
            $query->whereHas('specialists', function ($q) use ($specialist_id) {
                $q->where('specialist_id', $specialist_id);
            });
        }

        $bengkels = $query->get();

        return ResponseFormatter::success($bengkels, 'Data bengkel berhasil diambil.');
    }

    public function getKecamatan()
    {
        $kecamatans = Kecamatan::all();
        return ResponseFormatter::success($kecamatans, 'Data kecamatan berhasil diambil.');
    }

    public function getKelurahans($kecamatan_id)
    {
        $kelurahans = Kelurahan::where('kecamatan_id', $kecamatan_id)->get();
        return ResponseFormatter::success($kelurahans, 'Data kelurahan berhasil diambil.');
    }

    public function detailBengkel($id)
    {
        $bengkel = Bengkel::with(['layanans', 'jadwals', 'products', 'kecamatan', 'kelurahan', 'specialists'])
            ->find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Data bengkel tidak ditemukan', 404);
        }

        return ResponseFormatter::success($bengkel, 'Detail bengkel berhasil diambil.');
    }
}
