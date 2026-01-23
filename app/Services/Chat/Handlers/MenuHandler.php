<?php

namespace App\Services\Chat\Handlers;

use App\Models\User;
use App\Services\Chat\ResponseBuilder;

class MenuHandler
{
    /**
     * Handle menu display
     */
    public function handle(User $user): array
    {
        return ResponseBuilder::make()
            ->text("Hai {$user->name}! Ada yang bisa saya bantu?")
            ->text("Pilih layanan di bawah atau ketik pertanyaan Anda:")
            ->quickReplies([
                ['title' => 'Status Pesanan', 'payload' => 'status_prompt'],
                ['title' => 'Booking Bengkel', 'payload' => 'booking_prompt'],
                ['title' => 'Cari Produk', 'payload' => 'product_search_prompt'],
                ['title' => 'Bengkel Terdekat', 'payload' => 'nearby_prompt'],
                ['title' => 'Booking Saya', 'payload' => 'booking_history_prompt'],
                ['title' => 'Item Belum Dirating', 'payload' => 'rate_list'],
                ['title' => 'FAQ / Bantuan', 'payload' => 'faq_prompt'],
            ])
            ->context((string) $user->id)
            ->build();
    }
}
