<?php

namespace App\Services\Chat\Handlers;

use App\Models\Booking;
use App\Models\User;
use App\Services\Chat\ResponseBuilder;

class BookingHistoryHandler
{
    /**
     * Handle booking history - show recent bookings
     */
    public function handle(User $user, int $limit = 5): array
    {
        $bookings = Booking::with('bengkel')
            ->where('user_id', $user->id)
            ->orderBy('tanggal_booking', 'desc')
            ->orderBy('waktu_booking', 'desc')
            ->take($limit)
            ->get();

        if ($bookings->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Anda belum memiliki booking.')
                ->text('Mulai booking bengkel sekarang!')
                ->quickReplies([
                    ['title' => 'Booking Sekarang', 'payload' => 'booking_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("📋 *Riwayat Booking Anda*\n\nBerikut {$bookings->count()} booking terakhir:");

        foreach ($bookings as $booking) {
            $statusEmoji = $this->getStatusEmoji($booking->booking_status);

            $builder->bookingCard(
                $booking->id,
                [
                    'name' => $booking->bengkel?->name ?? 'Bengkel',
                    'image' => $booking->bengkel?->image_url ?? null,
                ],
                ResponseBuilder::formatDate($booking->tanggal_booking),
                substr($booking->waktu_booking, 0, 5),
                [
                    'brand' => $booking->brand,
                    'model' => $booking->model,
                    'plat' => $booking->plat,
                ],
                "{$statusEmoji} {$booking->booking_status}"
            );
        }

        $builder->quickReplies([
            ['title' => 'Booking Baru', 'payload' => 'booking_prompt'],
            ['title' => 'Menu Utama', 'payload' => 'menu'],
        ]);

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle specific booking detail
     */
    public function handleDetail(User $user, int $bookingId): array
    {
        $booking = Booking::with(['bengkel', 'detail_layanan_bookings.layanan'])
            ->where('user_id', $user->id)
            ->find($bookingId);

        if (!$booking) {
            return ResponseBuilder::make()
                ->text('Booking tidak ditemukan atau bukan milik Anda.')
                ->quickReplies([
                    ['title' => 'Lihat Semua Booking', 'payload' => 'booking_history_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $statusEmoji = $this->getStatusEmoji($booking->booking_status);

        $detailText = "📋 *Detail Booking #{$booking->id}*\n\n";
        $detailText .= "🏪 *Bengkel:* {$booking->bengkel?->name}\n";
        $detailText .= "📍 {$booking->bengkel?->alamat}\n\n";
        $detailText .= "📅 *Jadwal:*\n";
        $detailText .= ResponseBuilder::formatDate($booking->tanggal_booking) . "\n";
        $detailText .= "🕐 Pukul " . substr($booking->waktu_booking, 0, 5) . "\n\n";
        $detailText .= "🚗 *Kendaraan:*\n";
        $detailText .= "• Merk: {$booking->brand}\n";
        $detailText .= "• Model: {$booking->model}\n";
        $detailText .= "• Plat: {$booking->plat}\n";
        $detailText .= "• Tahun: {$booking->tahun_pembuatan}\n";
        $detailText .= "• KM: " . number_format($booking->kilometer, 0, ',', '.') . "\n";
        $detailText .= "• Transmisi: {$booking->transmisi}\n\n";

        // Show services if any
        if ($booking->detail_layanan_bookings->isNotEmpty()) {
            $detailText .= "🔧 *Layanan:*\n";
            foreach ($booking->detail_layanan_bookings as $detail) {
                $detailText .= "• " . ($detail->layanan?->name ?? 'Layanan') . "\n";
            }
            $detailText .= "\n";
        }

        if ($booking->catatan_tambahan) {
            $detailText .= "📝 *Catatan:* {$booking->catatan_tambahan}\n\n";
        }

        $detailText .= "{$statusEmoji} *Status:* {$booking->booking_status}";

        $replies = [
            ['title' => 'Lihat Booking Lain', 'payload' => 'booking_history_prompt'],
        ];

        // Add cancel option if pending
        if ($booking->booking_status === 'Pending') {
            $replies[] = ['title' => 'Batalkan Booking', 'payload' => 'cancel_booking_' . $booking->id];
        }

        $replies[] = ['title' => 'Menu Utama', 'payload' => 'menu'];

        return ResponseBuilder::make()
            ->text($detailText)
            ->quickReplies($replies)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle cancel booking
     */
    public function handleCancel(User $user, int $bookingId): array
    {
        $booking = Booking::where('user_id', $user->id)
            ->where('booking_status', 'Pending')
            ->find($bookingId);

        if (!$booking) {
            return ResponseBuilder::make()
                ->text('Booking tidak ditemukan atau tidak dapat dibatalkan.')
                ->text('Hanya booking dengan status "Pending" yang bisa dibatalkan.')
                ->quickReplies([
                    ['title' => 'Lihat Booking Saya', 'payload' => 'booking_history_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $booking->update(['booking_status' => 'Ditolak']);

        return ResponseBuilder::make()
            ->text("Booking #{$booking->id} telah dibatalkan.")
            ->quickReplies([
                ['title' => 'Booking Ulang', 'payload' => 'booking_prompt'],
                ['title' => 'Lihat Booking Lain', 'payload' => 'booking_history_prompt'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Get status emoji
     */
    private function getStatusEmoji(string $status): string
    {
        return match ($status) {
            'Pending' => '🟡',
            'Diterima' => '🟢',
            'Dikerjakan' => '🔵',
            'Selesai' => '✅',
            'Ditolak' => '🔴',
            default => '⚪',
        };
    }
}
