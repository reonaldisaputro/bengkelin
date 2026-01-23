<?php

namespace App\Services\Chat;

class ResponseBuilder
{
    private array $messages = [];
    private array $quickReplies = [];
    private ?string $contextId = null;
    private bool $end = false;
    private ?array $flow = null;

    /**
     * Create a new ResponseBuilder instance
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Add a text message
     */
    public function text(string $text): self
    {
        $this->messages[] = [
            'type' => 'text',
            'text' => $text,
        ];
        return $this;
    }

    /**
     * Add a card message
     */
    public function card(
        string $title,
        ?string $subtitle = null,
        ?string $image = null,
        array $actions = []
    ): self {
        $card = [
            'type' => 'card',
            'title' => $title,
        ];

        if ($subtitle) {
            $card['subtitle'] = $subtitle;
        }
        if ($image) {
            $card['image'] = $image;
        }
        if (!empty($actions)) {
            $card['actions'] = $actions;
        }

        $this->messages[] = $card;
        return $this;
    }

    /**
     * Add a link message
     */
    public function link(string $title, string $url): self
    {
        $this->messages[] = [
            'type' => 'link',
            'title' => $title,
            'url' => $url,
        ];
        return $this;
    }

    /**
     * Add a carousel of cards
     */
    public function carousel(array $items): self
    {
        $this->messages[] = [
            'type' => 'carousel',
            'items' => $items,
        ];
        return $this;
    }

    /**
     * Add a product card
     */
    public function productCard(
        int $id,
        string $name,
        int $price,
        string $bengkel,
        int $stock,
        ?string $image = null,
        array $actions = []
    ): self {
        $card = [
            'type' => 'product_card',
            'id' => $id,
            'name' => $name,
            'price' => $price,
            'price_formatted' => 'Rp ' . number_format($price, 0, ',', '.'),
            'bengkel' => $bengkel,
            'stock' => $stock,
        ];

        if ($image) {
            $card['image'] = $image;
        }

        if (empty($actions)) {
            $actions = [
                ['label' => 'Tambah ke Keranjang', 'payload' => 'add_to_cart_' . $id],
                ['label' => 'Detail', 'url' => url('/product/' . $id)],
            ];
        }
        $card['actions'] = $actions;

        $this->messages[] = $card;
        return $this;
    }

    /**
     * Add a booking card
     */
    public function bookingCard(
        int $id,
        array $bengkel,
        string $tanggal,
        string $waktu,
        array $vehicle,
        string $status,
        array $actions = []
    ): self {
        $card = [
            'type' => 'booking_card',
            'id' => $id,
            'bengkel' => $bengkel,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'vehicle' => $vehicle,
            'status' => $status,
        ];

        if (empty($actions)) {
            $actions = [
                ['label' => 'Lihat Detail', 'url' => url('/booking/' . $id)],
            ];
            if ($status === 'Pending') {
                $actions[] = ['label' => 'Batalkan', 'payload' => 'cancel_booking_' . $id];
            }
        }
        $card['actions'] = $actions;

        $this->messages[] = $card;
        return $this;
    }

    /**
     * Add a time picker for booking
     */
    public function timePicker(string $date, array $availableSlots, array $bookedSlots = []): self
    {
        $this->messages[] = [
            'type' => 'time_picker',
            'date' => $date,
            'available_slots' => $availableSlots,
            'booked_slots' => $bookedSlots,
        ];
        return $this;
    }

    /**
     * Add a bengkel card
     */
    public function bengkelCard(
        int $id,
        string $name,
        string $alamat,
        ?string $image = null,
        ?float $distance = null,
        array $actions = []
    ): self {
        $subtitle = $alamat;
        if ($distance !== null) {
            $subtitle .= ' • ±' . round($distance, 1) . ' km';
        }

        $card = [
            'type' => 'card',
            'id' => $id,
            'title' => $name,
            'subtitle' => $subtitle,
        ];

        if ($image) {
            $card['image'] = $image;
        }

        if (empty($actions)) {
            $actions = [
                ['label' => 'Pilih', 'payload' => 'select_bengkel_' . $id],
                ['label' => 'Detail', 'url' => url('/detailbengkelpage/' . $id)],
            ];
        }
        $card['actions'] = $actions;

        $this->messages[] = $card;
        return $this;
    }

    /**
     * Add a booking summary card
     */
    public function bookingSummary(array $data): self
    {
        $summary = "📋 *Ringkasan Booking*\n\n";
        $summary .= "🏪 Bengkel: {$data['bengkel_name']}\n";
        $summary .= "📅 Tanggal: {$data['tanggal']}\n";
        $summary .= "🕐 Waktu: {$data['waktu']}\n\n";
        $summary .= "*Data Kendaraan:*\n";
        $summary .= "• Merk: {$data['brand']}\n";
        $summary .= "• Model: {$data['model']}\n";
        $summary .= "• Plat: {$data['plat']}\n";
        $summary .= "• Tahun: {$data['tahun']}\n";
        $summary .= "• Kilometer: {$data['kilometer']}\n";
        $summary .= "• Transmisi: {$data['transmisi']}\n";

        if (!empty($data['catatan'])) {
            $summary .= "\n📝 Catatan: {$data['catatan']}";
        }

        $this->messages[] = [
            'type' => 'text',
            'text' => $summary,
        ];

        return $this;
    }

    /**
     * Add a quick reply
     */
    public function quickReply(string $title, string $payload, string $type = 'payload'): self
    {
        $reply = [
            'title' => $title,
            'payload' => $payload,
            'type' => $type,
        ];

        if ($type === 'message') {
            $reply['message'] = $payload;
        }

        $this->quickReplies[] = $reply;
        return $this;
    }

    /**
     * Add multiple quick replies at once
     */
    public function quickReplies(array $replies): self
    {
        foreach ($replies as $reply) {
            $this->quickReply(
                $reply['title'],
                $reply['payload'],
                $reply['type'] ?? 'payload'
            );
        }
        return $this;
    }

    /**
     * Add menu quick reply
     */
    public function menuQuickReply(): self
    {
        return $this->quickReply('Menu Utama', 'menu');
    }

    /**
     * Add cancel quick reply
     */
    public function cancelQuickReply(): self
    {
        return $this->quickReply('Batal', 'cancel');
    }

    /**
     * Set context ID
     */
    public function context(?string $contextId): self
    {
        $this->contextId = $contextId;
        return $this;
    }

    /**
     * Set end flag
     */
    public function end(bool $end = true): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * Set flow information
     */
    public function flow(?string $name, ?string $step, bool $canCancel = true): self
    {
        if ($name) {
            $this->flow = [
                'active' => true,
                'name' => $name,
                'step' => $step,
                'can_cancel' => $canCancel,
            ];
        } else {
            $this->flow = null;
        }
        return $this;
    }

    /**
     * Build the response array
     */
    public function build(): array
    {
        $response = [
            'messages' => $this->messages,
            'quick_replies' => $this->quickReplies,
            'context_id' => $this->contextId,
            'end' => $this->end,
        ];

        if ($this->flow) {
            $response['flow'] = $this->flow;
        }

        return $response;
    }

    /**
     * Reset the builder
     */
    public function reset(): self
    {
        $this->messages = [];
        $this->quickReplies = [];
        $this->contextId = null;
        $this->end = false;
        $this->flow = null;
        return $this;
    }

    /**
     * Helper: Create action for card
     */
    public static function action(string $label, ?string $payload = null, ?string $url = null): array
    {
        $action = ['label' => $label];
        if ($payload) {
            $action['payload'] = $payload;
        }
        if ($url) {
            $action['url'] = $url;
        }
        return $action;
    }

    /**
     * Helper: Format price in Rupiah
     */
    public static function formatPrice(int $price): string
    {
        return 'Rp ' . number_format($price, 0, ',', '.');
    }

    /**
     * Helper: Format date in Indonesian
     */
    public static function formatDate(string $date): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                   'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $timestamp = strtotime($date);
        $dayName = $days[date('w', $timestamp)];
        $day = date('j', $timestamp);
        $month = $months[(int)date('n', $timestamp)];
        $year = date('Y', $timestamp);

        return "{$dayName}, {$day} {$month} {$year}";
    }
}
