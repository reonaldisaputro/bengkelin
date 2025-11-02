<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileUserController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load('kecamatan', 'kelurahan');
        return ResponseFormatter::success($user, 'Data profil berhasil diambil.');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'          => 'nullable|string|max:255',
            'email'         => 'nullable|email|max:255',
            'phone_number'  => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:255',
            'kecamatan_id'  => 'nullable|exists:kecamatans,id',
            'kelurahan_id'  => 'nullable|exists:kelurahans,id',
        ]);

        $user->update(array_filter([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone_number'  => $request->phone_number,
            'alamat'        => $request->alamat,
            'kecamatan_id'  => $request->kecamatan_id,
            'kelurahan_id'  => $request->kelurahan_id,
        ]));

        return ResponseFormatter::success($user->fresh('kecamatan', 'kelurahan'), 'Profil berhasil diperbarui.');
    }

    public function bookingList()
    {
        $bookings = Booking::with(['user', 'bengkel'])
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return ResponseFormatter::success($bookings, 'Data booking berhasil diambil.');
    }

    public function bookingDetail($id)
    {
        $booking = Booking::with(['bengkel', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return ResponseFormatter::success($booking, 'Detail booking berhasil diambil.');
    }

    public function transactionList()
    {
        $transactions = Transaction::with(['user', 'bengkel'])
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return ResponseFormatter::success($transactions, 'Data transaksi berhasil diambil.');
    }

    // App\Http\Controllers\API\ProfileUserController.php

    public function transactionDetail($id)
    {
        $userId = Auth::id();

        $transaction = Transaction::with([
            // detail + product + rating milik user ini
            'detail_transactions' => function ($q) use ($userId) {
                $q->with([
                    'product',
                    'rating' => function ($r) use ($userId) {
                        $r->where('user_id', $userId);
                    },
                ]);
            },
            'bengkel',
            'layanan'
        ])
        ->where('user_id', $userId)
        ->findOrFail($id);

        return ResponseFormatter::success($transaction, 'Detail transaksi berhasil diambil.');
    }

}
