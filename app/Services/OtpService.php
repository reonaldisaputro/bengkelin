<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OtpService
{
    /**
     * Generate and store OTP for email
     */
    public function generateOtp(string $email, string $userType): string
    {
        // Delete any existing OTPs for this email and user type
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->delete();

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP with 10 minutes expiration
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp' => $otp,
            'user_type' => $userType,
            'is_verified' => false,
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $otp;
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(string $email, string $otp, string $userType): bool
    {
        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('user_type', $userType)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return false;
        }

        // Mark as verified
        DB::table('password_reset_otps')
            ->where('id', $record->id)
            ->update([
                'is_verified' => true,
                'updated_at' => Carbon::now(),
            ]);

        return true;
    }

    /**
     * Check if OTP is verified
     */
    public function isOtpVerified(string $email, string $userType): bool
    {
        $record = DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->where('is_verified', true)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $record !== null;
    }

    /**
     * Delete OTP after password reset
     */
    public function deleteOtp(string $email, string $userType): void
    {
        DB::table('password_reset_otps')
            ->where('email', $email)
            ->where('user_type', $userType)
            ->delete();
    }
}
