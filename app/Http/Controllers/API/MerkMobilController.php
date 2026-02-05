<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MerkMobil;
use Illuminate\Http\Request;

class MerkMobilController extends Controller
{
    /**
     * Get all merk mobil
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $merkMobils = MerkMobil::all()->map(function ($merk) {
                return [
                    'id' => $merk->id,
                    'nama_merk' => $merk->nama_merk,
                    'logo' => $merk->logo_url,
                    'deskripsi' => $merk->deskripsi,
                    'created_at' => $merk->created_at,
                    'updated_at' => $merk->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Data merk mobil berhasil diambil',
                'data' => $merkMobils,
                'count' => count($merkMobils),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detail merk mobil by id
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $merk = MerkMobil::with('bengkels')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Detail merk mobil berhasil diambil',
                'data' => [
                    'id' => $merk->id,
                    'nama_merk' => $merk->nama_merk,
                    'logo' => $merk->logo_url,
                    'deskripsi' => $merk->deskripsi,
                    'bengkels_count' => $merk->bengkels->count(),
                    'bengkels' => $merk->bengkels->map(function ($bengkel) {
                        return [
                            'id' => $bengkel->id,
                            'name' => $bengkel->name,
                            'alamat' => $bengkel->alamat,
                            'image' => $bengkel->image_url,
                            'pemilik_name' => $bengkel->pemilik_bengkel->name ?? null,
                        ];
                    }),
                    'created_at' => $merk->created_at,
                    'updated_at' => $merk->updated_at,
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Merk mobil tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
