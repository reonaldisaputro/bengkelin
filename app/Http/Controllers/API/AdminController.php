<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Models
use App\Models\User;
use App\Models\Bengkel;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\WithdrawRequest;
use App\Models\PemilikBengkel;
use App\Models\DetailLayananBooking;

// Response Formatter
use App\Helpers\ResponseFormatter;

class AdminController extends Controller
{
    public function dashboard()
    {
        try {
            return ResponseFormatter::success([
                'user_count' => User::count(),
                'bengkel_count' => Bengkel::count(),
                'owner_count' => PemilikBengkel::count(),
                'booking_count' => Booking::count(),
                'transaction_count' => Transaction::count(),
                'pencairan_count' => WithdrawRequest::count(),
            ], 'Dashboard data fetched successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, $e->getMessage(), 500);
        }
    }

    public function listUser()
    {
        return ResponseFormatter::success(User::latest()->get(), 'User list fetched successfully');
    }

    public function detailUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseFormatter::error(null, 'User not found', 404);
        }

        return ResponseFormatter::success($user, 'User details fetched successfully');
    }

    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseFormatter::error(null, 'User not found', 404);
        }

        $user->delete();
        return ResponseFormatter::success(null, 'User deleted successfully');
    }

    public function listOwner()
    {
        return ResponseFormatter::success(PemilikBengkel::latest()->get(), 'Owner list fetched successfully');
    }

    public function detailOwner($id)
    {
        $owner = PemilikBengkel::find($id);
        if (!$owner) {
            return ResponseFormatter::error(null, 'Owner not found', 404);
        }

        $bengkels = Bengkel::where('pemilik_bengkel_id', $id)->get();

        return ResponseFormatter::success([
            'owner' => $owner,
            'bengkels' => $bengkels,
        ], 'Owner details fetched successfully');
    }

    public function deleteOwner($id)
    {
        $owner = PemilikBengkel::find($id);

        if (!$owner) {
            return ResponseFormatter::error(null, 'Owner not found', 404);
        }

        $owner->delete();
        return ResponseFormatter::success(null, 'Owner deleted successfully');
    }

    public function listBengkel()
    {
        return ResponseFormatter::success(Bengkel::latest()->get(), 'Bengkel list fetched successfully');
    }

    public function detailBengkel($id)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel not found', 404);
        }

        return ResponseFormatter::success($bengkel, 'Bengkel details fetched successfully');
    }

    public function deleteBengkel($id)
    {
        $bengkel = Bengkel::find($id);

        if (!$bengkel) {
            return ResponseFormatter::error(null, 'Bengkel not found', 404);
        }

        $bengkel->delete();
        return ResponseFormatter::success(null, 'Bengkel deleted successfully');
    }

    public function listBooking()
    {
        $bookings = Booking::with(['user', 'bengkel', 'layanans'])->latest()->get();
        return ResponseFormatter::success($bookings, 'Booking list fetched successfully');
    }

    public function detailBooking($id)
    {
        $booking = Booking::with(['user', 'bengkel', 'layanans'])->find($id);

        if (!$booking) {
            return ResponseFormatter::error(null, 'Booking not found', 404);
        }

        $details = DetailLayananBooking::with(['booking', 'layanan'])
                    ->where('booking_id', $id)
                    ->get();

        return ResponseFormatter::success([
            'booking' => $booking,
            'detail_booking' => $details,
        ], 'Booking details fetched successfully');
    }

    public function listTransaction()
    {
        $transactions = Transaction::latest()->get();
        return ResponseFormatter::success($transactions, 'Transaction list fetched successfully');
    }

    public function detailTransaction($id)
    {
        $transaction = Transaction::with('detail_transactions.product', 'detail_transactions.layanan', 'detail_transactions.bengkel')->find($id);

        if (!$transaction) {
            return ResponseFormatter::error(null, 'Transaction not found', 404);
        }

        return ResponseFormatter::success($transaction, 'Transaction details fetched successfully');
    }
}
