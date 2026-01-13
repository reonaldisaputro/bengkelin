<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\ResponseFormatter;

class BookingController extends Controller
{
    // POST /api/bookings
    public function store(Request $request)
    {
        $request->validate([
            'bengkel_id' => 'required|exists:bengkels,id',
            'tanggal_booking' => 'required|date|after_or_equal:today',
            'waktu_booking' => 'required|date_format:H:i',
            'brand' => 'required|string',
            'model' => 'required|string',
            'plat' => 'required|string',
            'tahun_pembuatan' => 'required|integer',
            'kilometer' => 'required|numeric',
            'transmisi' => 'required|string|in:manual,automatic',
            'catatan_tambahan' => 'nullable|string',
        ]);

        $bengkel = Bengkel::with('jadwals')->findOrFail($request->bengkel_id);

        $tanggal = Carbon::createFromFormat('Y-m-d', $request->tanggal_booking);
        $waktu = Carbon::createFromFormat('H:i', $request->waktu_booking);

        if ($tanggal->isToday() && $waktu->lt(Carbon::now())) {
            return ResponseFormatter::error(null, 'Waktu booking tidak boleh lebih kecil dari waktu sekarang.', 422);
        }

        $hariInggrisKeIndonesia = [
            'monday' => 'senin',
            'tuesday' => 'selasa',
            'wednesday' => 'rabu',
            'thursday' => 'kamis',
            'friday' => 'jumat',
            'saturday' => 'sabtu',
            'sunday' => 'minggu',
        ];

        $hari = strtolower($tanggal->format('l'));
        $hariIndonesia = $hariInggrisKeIndonesia[$hari] ?? null;

        $jadwal = $bengkel->jadwals->first();

        if (!$jadwal || empty($jadwal->{$hariIndonesia . '_buka'}) || empty($jadwal->{$hariIndonesia . '_tutup'})) {
            return ResponseFormatter::error(null, 'Jam operasional bengkel tidak valid.', 422);
        }

        $jam_buka = Carbon::createFromFormat('H:i:s', $jadwal->{$hariIndonesia . '_buka'});
        $jam_tutup = Carbon::createFromFormat('H:i:s', $jadwal->{$hariIndonesia . '_tutup'});

        if ($waktu->lt($jam_buka) || $waktu->gt($jam_tutup)) {
            return ResponseFormatter::error(null, 'Waktu booking harus dalam jam operasional bengkel.', 422);
        }

        $conflict = Booking::where('bengkel_id', $request->bengkel_id)
            ->where('tanggal_booking', $request->tanggal_booking)
            ->whereRaw('HOUR(waktu_booking) = ?', [$waktu->format('H')])
            ->exists();

        if ($conflict) {
            return ResponseFormatter::error(null, 'Jam booking sudah diambil. Silakan pilih jam lain.', 409);
        }

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'bengkel_id' => $request->bengkel_id,
            'tanggal_booking' => $request->tanggal_booking,
            'waktu_booking' => $request->waktu_booking,
            'brand' => $request->brand,
            'model' => $request->model,
            'plat' => $request->plat,
            'tahun_pembuatan' => $request->tahun_pembuatan,
            'kilometer' => $request->kilometer,
            'transmisi' => $request->transmisi,
            'catatan_tambahan' => $request->catatan_tambahan,
            'status' => 'Pending',
        ]);

        return ResponseFormatter::success($booking, 'Booking berhasil dibuat.');
    }

    // GET /api/bookings/{bengkel_id}/booked-times?date=yyyy-mm-dd
    public function getBookedTimes(Request $request, $bengkel_id)
    {
        $request->validate([
            'date' => 'required|date'
        ]);

        $bookings = Booking::where('bengkel_id', $bengkel_id)
            ->whereDate('tanggal_booking', $request->query('date'))
            ->pluck('waktu_booking');

        return ResponseFormatter::success($bookings, 'Waktu booking yang telah terisi berhasil diambil.');
    }

    public function userBookings()
    {
        $bookings = Booking::with(['bengkel'])
            ->where('user_id', Auth::id())
            ->orderBy('tanggal_booking', 'desc')
            ->get();

        return ResponseFormatter::success($bookings, 'List booking user berhasil diambil.');
    }

    public function showUserBooking($id)
    {
        $booking = Booking::with(['bengkel', 'user', 'transactions'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$booking) {
            return ResponseFormatter::error(null, 'Booking tidak ditemukan', 404);
        }

        return ResponseFormatter::success($booking, 'Detail booking berhasil diambil.');
    }

}
