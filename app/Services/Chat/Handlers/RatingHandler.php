<?php

namespace App\Services\Chat\Handlers;

use App\Models\DetailTransaction;
use App\Models\Rating;
use App\Models\User;
use App\Services\Chat\ResponseBuilder;
use Illuminate\Support\Str;

class RatingHandler
{
    /**
     * Handle rating list - show unrated items
     */
    public function handle(User $user): array
    {
        $items = DetailTransaction::with(['product', 'layanan', 'transaction'])
            ->whereHas('transaction', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->whereIn('payment_status', ['success', 'paid', 'completed']);
            })
            ->whereDoesntHave('rating', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->take(5)
            ->get();

        if ($items->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Tidak ada item yang perlu dirating.')
                ->text('Semua transaksi sudah diulas!')
                ->menuQuickReply()
                ->context((string) $user->id)
                ->build();
        }

        // Build item list
        $itemListText = "Anda punya " . $items->count() . " item/layanan yang belum diulas:\n";

        $builder = ResponseBuilder::make();

        foreach ($items as $it) {
            $name = $it->product?->name ?? $it->layanan?->name ?? 'Item (ID: ' . $it->id . ')';
            $itemListText .= "\n• {$name}\n  (Order: {$it->transaction->transaction_code})";

            // Add quick reply for each item
            $builder->quickReply(
                "Ulas: " . Str::limit($name, 20),
                'rate_prompt_' . $it->id
            );
        }

        $builder->menuQuickReply();

        return ResponseBuilder::make()
            ->text($itemListText)
            ->text("Silakan pilih item di bawah untuk memberi ulasan.")
            ->quickReplies($builder->build()['quick_replies'])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle rate prompt - show instructions for specific item
     */
    public function handlePrompt(User $user, int $detailId): array
    {
        $detail = DetailTransaction::with('product', 'layanan')
            ->whereHas('transaction', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->find($detailId);

        if (!$detail) {
            return ResponseBuilder::make()
                ->text('Item tidak valid atau bukan milik Anda.')
                ->quickReplies([
                    ['title' => 'Lihat Item Lain', 'payload' => 'rate_list'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $name = $detail->product?->name ?? $detail->layanan?->name ?? 'Item';

        return ResponseBuilder::make()
            ->text("Anda akan mengulas:\n*{$name}*")
            ->text("Silakan ketik balasan dengan format:\nrate {$detail->id} [bintang 1-5] \"komentar\"")
            ->text("Contoh:\nrate {$detail->id} 5 \"Sangat memuaskan!\"")
            ->quickReplies([
                ['title' => '⭐ 5 - Sangat Puas', 'payload' => "rate {$detail->id} 5 \"Sangat memuaskan!\"", 'type' => 'message'],
                ['title' => '⭐ 4 - Puas', 'payload' => "rate {$detail->id} 4 \"Memuaskan\"", 'type' => 'message'],
                ['title' => '⭐ 3 - Cukup', 'payload' => "rate {$detail->id} 3 \"Cukup baik\"", 'type' => 'message'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle rate submission
     */
    public function handleSubmit(User $user, int $detailId, int $stars, ?string $comment = null): array
    {
        $detail = DetailTransaction::with('transaction', 'product', 'layanan')
            ->find($detailId);

        if (!$detail || $detail->transaction->user_id !== $user->id) {
            return ResponseBuilder::make()
                ->text('Item tidak valid.')
                ->menuQuickReply()
                ->context((string) $user->id)
                ->build();
        }

        // Check if already rated
        $already = Rating::where([
            'user_id' => $user->id,
            'detail_transaction_id' => $detail->id,
        ])->exists();

        if ($already) {
            return ResponseBuilder::make()
                ->text('Item ini sudah pernah diberi ulasan.')
                ->quickReplies([
                    ['title' => 'Lihat Item Lain', 'payload' => 'rate_list'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        // Create rating
        Rating::create([
            'user_id' => $user->id,
            'product_id' => $detail->product_id,
            'layanan_id' => $detail->layanan_id,
            'transaction_id' => $detail->transaction_id,
            'detail_transaction_id' => $detail->id,
            'stars' => $stars,
            'comment' => $comment,
        ]);

        $itemName = $detail->product?->name ?? $detail->layanan?->name ?? 'Item';
        $starEmoji = str_repeat('⭐', $stars);

        return ResponseBuilder::make()
            ->text("Terima kasih! Ulasan Anda tersimpan.\n\n{$itemName}\n{$starEmoji}" . ($comment ? "\n\"{$comment}\"" : ''))
            ->quickReplies([
                ['title' => 'Ulas Item Lain', 'payload' => 'rate_list'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Parse rate submission message
     * Format: rate {id} {stars} "comment"
     */
    public function parseRateMessage(string $message): ?array
    {
        if (preg_match('/^rate\s+(\d+)\s+([1-5])(?:\s+"(.*)")?$/i', $message, $matches)) {
            return [
                'detail_id' => (int) $matches[1],
                'stars' => (int) $matches[2],
                'comment' => $matches[3] ?? null,
            ];
        }
        return null;
    }
}
