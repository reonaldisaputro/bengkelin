<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Kelurahan;
use App\Models\Kecamatan;
use App\Models\Specialist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BengkelController extends Controller
{
    public function findNearby(Request $request)
{
    $validator = Validator::make($request->all(), [
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        'radius' => 'nullable|numeric|min:1',
    ]);

    if ($validator->fails()) {
        return ResponseFormatter::error(['errors' => $validator->errors()], 'Input tidak valid', 400);
    }

    $latitude = $request->latitude;
    $longitude = $request->longitude;
    $radius = $request->radius ?? 10;

    $bengkels = Bengkel::select('bengkels.*')
        ->selectRaw(
            '( 6371 * acos( cos( radians(?) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians(?)
                ) + sin( radians(?) ) *
                sin( radians( latitude ) ) )
              ) AS distance',
            [$latitude, $longitude, $latitude]
        )
        ->with(['specialists', 'kecamatan', 'kelurahan'])
        ->whereNotNull(['latitude', 'longitude'])
        ->having('distance', '<=', $radius)
        ->orderBy('distance', 'asc')
        // 1. Ganti paginate(10) dengan get()
        ->get();

    // 2. Karena $bengkels sudah merupakan Collection, panggil transform langsung
    $bengkels->transform(function ($bengkel) {
        if ($bengkel->image) {
            $bengkel->image_url = url('images/' . $bengkel->image);
        }
        return $bengkel;
    });

    if ($bengkels->isEmpty()) {
        // Menggunakan ResponseFormatter::success dengan data kosong agar konsisten
        return ResponseFormatter::success([], 'Tidak ada bengkel ditemukan dalam radius ' . $radius . ' km.');
    }

    return ResponseFormatter::success($bengkels, 'Bengkel terdekat berhasil ditemukan');
}

    public function all()
    {
        $bengkels = Bengkel::with(['specialists', 'kecamatan', 'kelurahan'])->get();
        return ResponseFormatter::success($bengkels, 'Daftar semua bengkel berhasil diambil');
    }

    // GET /api/bengkel
    public function index()
    {
        $bengkels = Bengkel::where('pemilik_id', Auth::id())->with('specialists')->get();
        return ResponseFormatter::success($bengkels, 'Data bengkel berhasil diambil');
    }
    

   public function show($id)
    {
        $bengkel = Bengkel::with([
            'specialists',
            'pemilik_bengkel', // eager load pemilik bengkel
            'kecamatan',
            'kelurahan',
            'products',
            'jadwals'
        ])->find($id);
    
        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }
    
        return ResponseFormatter::success($bengkel, 'Detail bengkel berhasil diambil');
    }


    // POST /api/bengkel
    public function store(Request $request)
    {
        $request->validate([
            'bengkel_name' => 'required|string|max:255',
            'bengkel_description' => 'required|string',
            'bengkel_address' => 'required|string',
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'kelurahan_id' => 'required|exists:kelurahans,id',
            'image' => 'required|image|max:2048',
            'specialist_ids' => 'required|array',
            'specialist_ids.*' => 'exists:specialists,id',
        ]);

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);

        $bengkel = Bengkel::create([
            'name' => $request->bengkel_name,
            'description' => $request->bengkel_description,
            'alamat' => $request->bengkel_address,
            'image' => $imageName,
            'pemilik_id' => Auth::id(),
            'kecamatan_id' => $request->kecamatan_id,
            'kelurahan_id' => $request->kelurahan_id,
        ]);

        $bengkel->specialists()->sync($request->specialist_ids);

        return ResponseFormatter::success($bengkel->load('specialists'), 'Bengkel berhasil ditambahkan');
    }

    // PUT /api/bengkel/{id}
    public function update(Request $request, $id)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        $request->validate([
            'bengkel_name' => 'required|string|max:255',
            'bengkel_description' => 'required|string',
            'bengkel_address' => 'required|string',
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'kelurahan_id' => 'required|exists:kelurahans,id',
            'image' => 'nullable|image|max:2048',
            'specialist_ids' => 'required|array',
            'specialist_ids.*' => 'exists:specialists,id',
        ]);

        if ($request->hasFile('image')) {
            if ($bengkel->image) {
                File::delete(public_path('images/' . $bengkel->image));
            }
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $bengkel->image = $imageName;
        }

        $bengkel->update([
            'name' => $request->bengkel_name,
            'description' => $request->bengkel_description,
            'alamat' => $request->bengkel_address,
            'pemilik_id' => Auth::id(),
            'kecamatan_id' => $request->kecamatan_id,
            'kelurahan_id' => $request->kelurahan_id,
        ]);

        $bengkel->specialists()->sync($request->specialist_ids);

        return ResponseFormatter::success($bengkel->load('specialists'), 'Bengkel berhasil diperbarui');
    }

    // DELETE /api/bengkel/{id}
    public function destroy($id)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        if ($bengkel->image) {
            File::delete(public_path('images/' . $bengkel->image));
        }

        $bengkel->specialists()->detach();
        $bengkel->delete();

        return ResponseFormatter::success(null, 'Bengkel berhasil dihapus');
    }

    // GET /api/bengkel/kelurahan/{kecamatan_id}
    public function getKelurahans($kecamatan_id)
    {
        $kelurahans = Kelurahan::where('kecamatan_id', $kecamatan_id)->get();
        return ResponseFormatter::success($kelurahans, 'Daftar kelurahan berhasil diambil');
    }
}
