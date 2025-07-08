<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Booking;
use App\Models\DetailLayananBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseFormatter;

class BengkelBookingController extends Controller
{
    // GET /api/bengkel/bookings
    public function index()
    {
        try {
            $bengkels = Bengkel::where('pemilik_id', Auth::id())->get();

            if ($bengkels->isEmpty()) {
                return ResponseFormatter::success([], 'Tidak ada bengkel ditemukan untuk user ini');
            }

            $bengkel_ids = $bengkels->pluck('id');

            $bookings = Booking::with(['user', 'bengkel', 'transactions'])
                ->whereIn('bengkel_id', $bengkel_ids)
                ->orderBy('id', 'desc')
                ->get();

            return ResponseFormatter::success($bookings, 'Daftar booking berhasil diambil');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Gagal mengambil data booking: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/bengkel/bookings/{id}
    public function show($id)
    {
        $booking = Booking::with(['user', 'bengkel'])->find($id);

        if (!$booking) {
            return ResponseFormatter::error(null, 'Booking tidak ditemukan', 404);
        }

        $details = DetailLayananBooking::with(['booking', 'layanan'])
            ->where('booking_id', $id)
            ->get();

        return ResponseFormatter::success([
            'booking' => $booking,
            'detail_booking' => $details,
        ], 'Detail booking berhasil diambil');
    }

    // PUT /api/bengkel/bookings/{id}
    public function update(Request $request, $id)
    {
        $booking = Booking::with(['user', 'bengkel'])->find($id);

        if (!$booking) {
            return ResponseFormatter::error(null, 'Booking tidak ditemukan', 404);
        }

        $validated = $request->validate([
            'booking_status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $booking->booking_status = $validated['booking_status'];
        $booking->save();

        return ResponseFormatter::success($booking, 'Status booking berhasil diperbarui');
    }
}
