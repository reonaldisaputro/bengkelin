<?php

namespace App\Services\Chat\Handlers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\Chat\ResponseBuilder;

class StatusHandler
{
    /**
     * Handle status prompt - show active transactions
     */
    public function handle(User $user): array
    {
        $activeTrx = Transaction::where('user_id', $user->id)
            ->whereIn('payment_status', ['pending', 'unpaid', 'processing'])
            ->latest()
            ->take(5)
            ->get();

        if ($activeTrx->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Anda tidak memiliki pesanan aktif saat ini.')
                ->text('Jika punya kode transaksi, ketik: status TRANS-123')
                ->menuQuickReply()
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text('Anda memiliki beberapa pesanan aktif:');

        // Add quick replies for each transaction
        foreach ($activeTrx as $trx) {
            $builder->quickReply(
                $trx->transaction_code,
                'status ' . $trx->transaction_code,
                'message'
            );
        }

        $builder->menuQuickReply();

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle specific transaction status
     */
    public function handleSpecific(User $user, string $transactionCode): array
    {
        $trx = Transaction::with('detail_transactions.product', 'detail_transactions.layanan', 'bengkel')
            ->where('transaction_code', $transactionCode)
            ->where('user_id', $user->id)
            ->first();

        if (!$trx) {
            return ResponseBuilder::make()
                ->text("Pesanan {$transactionCode} tidak ditemukan.")
                ->text('Pastikan kode transaksi benar dan milik Anda.')
                ->quickReplies([
                    ['title' => 'Cek Pesanan Lain', 'payload' => 'status_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        // Get items from transaction
        $items = $trx->detail_transactions
            ->map(function ($detail) {
                return $detail->product?->name ?? $detail->layanan?->name;
            })
            ->filter()
            ->implode(', ');

        // Build status message
        $statusText = "📦 *Status {$trx->transaction_code}*\n\n";
        $statusText .= "🏪 Bengkel: " . ($trx->bengkel?->name ?? '-') . "\n";
        $statusText .= "💳 Pembayaran: {$trx->payment_status}\n";
        $statusText .= "🚚 Pengiriman: " . ($trx->shipping_status ?? '-') . "\n";
        $statusText .= "📝 Item: {$items}\n";
        $statusText .= "💰 Total: Rp " . number_format($trx->grand_total, 0, ',', '.');

        return ResponseBuilder::make()
            ->text($statusText)
            ->link('Lihat Detail Lengkap', url('/profile-transaction/' . $trx->id))
            ->quickReplies([
                ['title' => 'Cek Pesanan Lain', 'payload' => 'status_prompt'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle status prompt (initial - ask for code)
     */
    public function handlePrompt(User $user): array
    {
        return ResponseBuilder::make()
            ->text('Ketik kode transaksi Anda untuk cek status.')
            ->text('Contoh: status TRANS-123')
            ->quickReplies([
                ['title' => 'Lihat Pesanan Aktif', 'payload' => 'status_prompt'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }
}
