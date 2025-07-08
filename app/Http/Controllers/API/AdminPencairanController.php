<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Helpers\ResponseFormatter;

class AdminPencairanController extends Controller
{
    // GET /api/admin/pencairan
    public function index()
    {
        try {
            $pencairans = WithdrawRequest::with('bengkel')->get();
            return ResponseFormatter::success($pencairans, 'Daftar pencairan berhasil diambil');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Gagal mengambil data pencairan: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/admin/pencairan/{id}
    public function show($id)
    {
        $pencairan = WithdrawRequest::with('bengkel')->find($id);

        if (!$pencairan) {
            return ResponseFormatter::error(null, 'Pencairan tidak ditemukan', 404);
        }

        return ResponseFormatter::success($pencairan, 'Detail pencairan berhasil diambil');
    }

    // PUT /api/admin/pencairan/{id}
    public function update(Request $request, $id)
    {
        $pencairan = WithdrawRequest::find($id);

        if (!$pencairan) {
            return ResponseFormatter::error(null, 'Pencairan tidak ditemukan', 404);
        }

        try {
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('images'), $imageName);
                $pencairan->image = $imageName;
            }

            $pencairan->status = $request->status ?? $pencairan->status;
            $pencairan->save();

            return ResponseFormatter::success($pencairan, 'Status pencairan berhasil diperbarui');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Gagal memperbarui pencairan: ' . $e->getMessage(), 500);
        }
    }
}
