<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PemilikBengkel;
use App\Services\OtpService;
use App\Notifications\PasswordResetOtpNotification;
use App\Helpers\ResponseFormatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordOwnerController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Send OTP to owner email
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                $validator->errors(),
                'Validation Error',
                422
            );
        }

        $owner = PemilikBengkel::where('email', $request->email)->first();

        if (!$owner) {
            return ResponseFormatter::error(
                null,
                'Email tidak terdaftar',
                404
            );
        }

        // Generate OTP
        $otp = $this->otpService->generateOtp($request->email, 'owner');

        // Send OTP via email
        try {
            $owner->notify(new PasswordResetOtpNotification($otp));

            return ResponseFormatter::success(
                [
                    'email' => $request->email,
                ],
                'Kode OTP telah dikirim ke email Anda'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Gagal mengirim email: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                $validator->errors(),
                'Validation Error',
                422
            );
        }

        $isValid = $this->otpService->verifyOtp(
            $request->email,
            $request->otp,
            'owner'
        );

        if (!$isValid) {
            return ResponseFormatter::error(
                null,
                'Kode OTP tidak valid atau sudah kedaluwarsa',
                400
            );
        }

        return ResponseFormatter::success(
            [
                'email' => $request->email,
                'verified' => true,
            ],
            'OTP berhasil diverifikasi'
        );
    }

    /**
     * Reset password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                $validator->errors(),
                'Validation Error',
                422
            );
        }

        // Check if OTP is verified
        $isVerified = $this->otpService->isOtpVerified($request->email, 'owner');

        if (!$isVerified) {
            return ResponseFormatter::error(
                null,
                'OTP belum diverifikasi atau sudah kedaluwarsa',
                400
            );
        }

        $owner = PemilikBengkel::where('email', $request->email)->first();

        if (!$owner) {
            return ResponseFormatter::error(
                null,
                'Owner tidak ditemukan',
                404
            );
        }

        // Update password
        $owner->password = Hash::make($request->password);
        $owner->save();

        // Delete OTP record
        $this->otpService->deleteOtp($request->email, 'owner');

        return ResponseFormatter::success(
            null,
            'Password berhasil direset'
        );
    }
}
