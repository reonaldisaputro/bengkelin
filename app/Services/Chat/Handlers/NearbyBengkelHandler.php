<?php

namespace App\Services\Chat\Handlers;

use App\Models\Bengkel;
use App\Models\User;
use App\Services\Chat\ResponseBuilder;

class NearbyBengkelHandler
{
    /**
     * Handle nearby prompt - ask for location
     */
    public function handle(User $user): array
    {
        return ResponseBuilder::make()
            ->text('Untuk mencari bengkel terdekat, saya butuh lokasi Anda.')
            ->text('Kirim lokasi dengan format: bengkel terdekat -6.2,106.8')
            ->text('(Ganti koordinat dengan lokasi Anda)')
            ->quickReplies([
                ['title' => 'Lihat Semua Bengkel', 'payload' => 'nearby_list'],
                ['title' => 'Menu Utama', 'payload' => 'menu'],
            ])
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle nearby with coordinates
     */
    public function handleWithCoords(User $user, float $lat, float $lng, int $limit = 3): array
    {
        $bengkels = Bengkel::select('*')
            ->selectRaw(
                '(111.045 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(latitude)))))) AS distance_km',
                [$lat, $lng, $lat]
            )
            ->orderBy('distance_km')
            ->limit($limit)
            ->get();

        if ($bengkels->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Tidak ada bengkel terdekat yang ditemukan.')
                ->quickReplies([
                    ['title' => 'Coba Lokasi Lain', 'payload' => 'nearby_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("Ditemukan {$bengkels->count()} bengkel terdekat dari lokasi Anda:");

        foreach ($bengkels as $b) {
            $builder->bengkelCard(
                $b->id,
                $b->name,
                $b->alamat,
                $b->image_url ?? null,
                $b->distance_km
            );
        }

        $builder->quickReplies([
            ['title' => 'Cari Lokasi Lain', 'payload' => 'nearby_prompt'],
            ['title' => 'Menu Utama', 'payload' => 'menu'],
        ]);

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle nearby list - show all bengkels (paginated)
     */
    public function handleList(User $user, int $page = 1, int $perPage = 5): array
    {
        $bengkels = Bengkel::orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = Bengkel::count();
        $totalPages = ceil($total / $perPage);

        if ($bengkels->isEmpty()) {
            return ResponseBuilder::make()
                ->text('Tidak ada bengkel yang ditemukan.')
                ->menuQuickReply()
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("Daftar Bengkel (Halaman {$page} dari {$totalPages}):");

        foreach ($bengkels as $b) {
            $builder->bengkelCard(
                $b->id,
                $b->name,
                $b->alamat,
                $b->image_url ?? null
            );
        }

        // Pagination quick replies
        $replies = [];
        if ($page > 1) {
            $replies[] = ['title' => '⬅️ Sebelumnya', 'payload' => 'nearby_list_page_' . ($page - 1)];
        }
        if ($page < $totalPages) {
            $replies[] = ['title' => 'Selanjutnya ➡️', 'payload' => 'nearby_list_page_' . ($page + 1)];
        }
        $replies[] = ['title' => 'Cari Terdekat', 'payload' => 'nearby_prompt'];
        $replies[] = ['title' => 'Menu Utama', 'payload' => 'menu'];

        $builder->quickReplies($replies);

        return $builder
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Parse coordinates from message
     */
    public function parseCoordinates(string $message): ?array
    {
        if (preg_match('/(-?\d+\.?\d*),\s*(-?\d+\.?\d*)/', $message, $matches)) {
            return [
                'lat' => (float) $matches[1],
                'lng' => (float) $matches[2],
            ];
        }
        return null;
    }
}
