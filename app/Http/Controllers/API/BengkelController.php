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

    /**
     * Get all bengkels with filters
     *
     * Query params:
     * - keyword: string (search by name, description, alamat)
     * - specialist_id: int (filter by specialist)
     * - kecamatan_id: int (filter by kecamatan)
     * - kelurahan_id: int (filter by kelurahan)
     * - sort_by: string (name, created_at)
     * - sort_order: string (asc, desc)
     * - per_page: int (default: all, set to paginate)
     */
    public function all(Request $request)
    {
        $query = Bengkel::with(['specialists', 'kecamatan', 'kelurahan']);

        // Search by keyword (name, description, alamat)
        if ($request->filled('keyword')) {
            $keyword = $request->query('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('description', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('alamat', 'LIKE', '%' . $keyword . '%');
            });
        }

        // Filter by specialist
        if ($request->filled('specialist_id')) {
            $specialistId = $request->query('specialist_id');
            $query->whereHas('specialists', function ($q) use ($specialistId) {
                $q->where('specialists.id', $specialistId);
            });
        }

        // Filter by kecamatan
        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->query('kecamatan_id'));
        }

        // Filter by kelurahan
        if ($request->filled('kelurahan_id')) {
            $query->where('kelurahan_id', $request->query('kelurahan_id'));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'name');
        $sortOrder = $request->query('sort_order', 'asc');
        $allowedSorts = ['name', 'created_at'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        // Pagination (optional)
        if ($request->filled('per_page')) {
            $bengkels = $query->paginate($request->query('per_page'));
        } else {
            $bengkels = $query->get();
        }

        return ResponseFormatter::success($bengkels, 'Daftar semua bengkel berhasil diambil');
    }

    // GET /api/bengkel
    public function index()
    {
        $bengkels = Bengkel::where('pemilik_id', Auth::id())->with('specialists')->get();
        return ResponseFormatter::success($bengkels, 'Data bengkel berhasil diambil');
    }

    public function myBengkel(Request $request)
    {
        $owner = Auth::guard('owner-api')->user();

        if (!$owner) {
            return ResponseFormatter::error(null, 'Unauthorized', 401);
        }

        $bengkel = Bengkel::with([
                'specialists',
                'kecamatan',
                'kelurahan',
                'products',
                'jadwals'
            ])
            ->where('pemilik_id', $owner->id)
            ->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkelsadasds tidak ditemukan', 404);
        }

        // Tambahkan image_url
        if ($bengkel->image) {
            $bengkel->image_url = url('images/' . $bengkel->image);
        }

        return ResponseFormatter::success($bengkel, 'Data bengkel owner berhasil diambil');
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

            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

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

            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
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

            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

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
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
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

    /**
     * Get merk mobil untuk bengkel tertentu
     * GET /api/bengkel/{id}/merk-mobil
     */
    public function getMerkMobil($id)
    {
        $bengkel = Bengkel::with('merkMobils')->find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        return ResponseFormatter::success($bengkel->merkMobils, 'Daftar merk mobil bengkel berhasil diambil');
    }

    /**
     * Add merk mobil ke bengkel (hanya owner)
     * POST /api/bengkel/{id}/merk-mobil
     * Body: { merk_mobil_ids: [1, 2, 3] }
     */
    public function addMerkMobil(Request $request, $id)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        // Cek apakah user yang login adalah owner dari bengkel ini
        if ($bengkel->pemilik_id != Auth::id()) {
            return ResponseFormatter::error(null, 'Anda tidak memiliki akses ke bengkel ini', 403);
        }

        $validator = Validator::make($request->all(), [
            'merk_mobil_ids' => 'required|array',
            'merk_mobil_ids.*' => 'exists:merk_mobils,id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validasi gagal', 422);
        }

        // Attach merk mobil ke bengkel (tidak menghapus yang sudah ada)
        $bengkel->merkMobils()->syncWithoutDetaching($request->merk_mobil_ids);

        return ResponseFormatter::success(
            $bengkel->load('merkMobils'), 
            'Merk mobil berhasil ditambahkan ke bengkel'
        );
    }

    /**
     * Update merk mobil bengkel (replace all)
     * PUT /api/bengkel/{id}/merk-mobil
     * Body: { merk_mobil_ids: [1, 2, 3] }
     */
    public function updateMerkMobil(Request $request, $id)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        // Cek apakah user yang login adalah owner dari bengkel ini
        if ($bengkel->pemilik_id != Auth::id()) {
            return ResponseFormatter::error(null, 'Anda tidak memiliki akses ke bengkel ini', 403);
        }

        $validator = Validator::make($request->all(), [
            'merk_mobil_ids' => 'required|array',
            'merk_mobil_ids.*' => 'exists:merk_mobils,id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validasi gagal', 422);
        }

        // Sync akan mengganti semua relasi yang ada
        $bengkel->merkMobils()->sync($request->merk_mobil_ids);

        return ResponseFormatter::success(
            $bengkel->load('merkMobils'), 
            'Merk mobil bengkel berhasil diupdate'
        );
    }

    /**
     * Remove merk mobil dari bengkel
     * DELETE /api/bengkel/{id}/merk-mobil/{merkMobilId}
     */
    public function removeMerkMobil($id, $merkMobilId)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan', 404);
        }

        // Cek apakah user yang login adalah owner dari bengkel ini
        if ($bengkel->pemilik_id != Auth::id()) {
            return ResponseFormatter::error(null, 'Anda tidak memiliki akses ke bengkel ini', 403);
        }

        // Detach merk mobil dari bengkel
        $bengkel->merkMobils()->detach($merkMobilId);

        return ResponseFormatter::success(
            $bengkel->load('merkMobils'), 
            'Merk mobil berhasil dihapus dari bengkel'
        );
    }
}
