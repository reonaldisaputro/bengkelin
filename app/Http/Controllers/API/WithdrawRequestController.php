<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use App\Models\Transaction;
use App\Models\Bengkel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WithdrawRequestController extends Controller
{
    public function index()
    {
        $pemilik = Auth::user();
        $bengkel = Bengkel::where('pemilik_id', $pemilik->id)->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan.', 404);
        }

        $pencairans = WithdrawRequest::where('bengkel_id', $bengkel->id)->get();

        return ResponseFormatter::success($pencairans, 'Data pencairan berhasil diambil.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank' => 'required|string',
            'number' => 'required|string',
            'name' => 'required|string',
        ]);

        $pemilik = Auth::user();
        $bengkel = Bengkel::where('pemilik_id', $pemilik->id)->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan.', 404);
        }

        $bengkel_id = $bengkel->id;

        $totalTransaksi = Transaction::where('bengkel_id', $bengkel_id)
            ->where('payment_status', 'success')
            ->whereNull('withdrawn_at')
            ->sum(DB::raw('(grand_total + ongkir) - administrasi'));

        if ($totalTransaksi < 50000) {
            return ResponseFormatter::error(null, 'Minimal saldo untuk penarikan adalah 50 ribu.', 400);
        }

        DB::transaction(function () use ($request, $bengkel_id, $totalTransaksi) {
            WithdrawRequest::create([
                'bengkel_id' => $bengkel_id,
                'amount' => $totalTransaksi,
                'bank' => $request->bank,
                'number' => $request->number,
                'name' => $request->name,
                'status' => 'pending',
            ]);

            Transaction::where('bengkel_id', $bengkel_id)
                ->where('payment_status', 'success')
                ->whereNull('withdrawn_at')
                ->update(['withdrawn_at' => now()]);
        });

        return ResponseFormatter::success(null, 'Permintaan pencairan berhasil dibuat.');
    }

    public function show($id)
    {
        $pemilik = Auth::user();
        $bengkel = Bengkel::where('pemilik_id', $pemilik->id)->first();

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel tidak ditemukan.', 404);
        }

        $pencairan = WithdrawRequest::where('bengkel_id', $bengkel->id)->find($id);

        if (!$pencairan) {
            return ResponseFormatter::error(null, 'Data pencairan tidak ditemukan.', 404);
        }

        return ResponseFormatter::success($pencairan, 'Detail pencairan berhasil diambil.');
    }
}
