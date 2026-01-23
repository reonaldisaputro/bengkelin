<?php

namespace App\Services\Chat\Handlers;

use App\Models\Bengkel;
use App\Models\Booking;
use App\Models\User;
use App\Services\Chat\ContextManager;
use App\Services\Chat\ResponseBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingFlowHandler
{
    private ContextManager $contextManager;

    // Flow steps
    const STEP_INIT = 'init';
    const STEP_SELECT_BENGKEL = 'select_bengkel';
    const STEP_CONFIRM_BENGKEL = 'confirm_bengkel';
    const STEP_SELECT_DATE = 'select_date';
    const STEP_SELECT_TIME = 'select_time';
    const STEP_VEHICLE_BRAND = 'vehicle_brand';
    const STEP_VEHICLE_MODEL = 'vehicle_model';
    const STEP_VEHICLE_PLAT = 'vehicle_plat';
    const STEP_VEHICLE_YEAR = 'vehicle_year';
    const STEP_VEHICLE_KM = 'vehicle_km';
    const STEP_VEHICLE_TRANSMISI = 'vehicle_transmisi';
    const STEP_NOTES = 'notes';
    const STEP_CONFIRM = 'confirm';

    public function __construct(ContextManager $contextManager)
    {
        $this->contextManager = $contextManager;
    }

    /**
     * Handle booking flow based on current step
     */
    public function handle(User $user, string $message, ?string $payload, array $matches = []): array
    {
        $context = $this->contextManager->getContext($user->id);
        $currentStep = $context['step'] ?? self::STEP_INIT;

        return match ($currentStep) {
            self::STEP_INIT => $this->handleInit($user),
            self::STEP_SELECT_BENGKEL => $this->handleSelectBengkel($user, $message, $payload, $matches),
            self::STEP_CONFIRM_BENGKEL => $this->handleConfirmBengkel($user, $message, $payload),
            self::STEP_SELECT_DATE => $this->handleSelectDate($user, $message, $payload),
            self::STEP_SELECT_TIME => $this->handleSelectTime($user, $message, $payload),
            self::STEP_VEHICLE_BRAND => $this->handleVehicleBrand($user, $message, $payload),
            self::STEP_VEHICLE_MODEL => $this->handleVehicleModel($user, $message),
            self::STEP_VEHICLE_PLAT => $this->handleVehiclePlat($user, $message),
            self::STEP_VEHICLE_YEAR => $this->handleVehicleYear($user, $message, $payload),
            self::STEP_VEHICLE_KM => $this->handleVehicleKm($user, $message),
            self::STEP_VEHICLE_TRANSMISI => $this->handleVehicleTransmisi($user, $message, $payload),
            self::STEP_NOTES => $this->handleNotes($user, $message),
            self::STEP_CONFIRM => $this->handleConfirm($user, $message, $payload),
            default => $this->handleInit($user),
        };
    }

    /**
     * Start booking flow
     */
    public function handleInit(User $user): array
    {
        $this->contextManager->updateFlow($user->id, 'booking', self::STEP_SELECT_BENGKEL);

        return ResponseBuilder::make()
            ->text("🔧 *Booking Bengkel*\n\nMari mulai booking bengkel untuk kendaraan Anda.")
            ->text("Silakan pilih cara mencari bengkel:")
            ->quickReplies([
                ['title' => '📍 Bengkel Terdekat', 'payload' => 'booking_nearby'],
                ['title' => '🔍 Cari Nama Bengkel', 'payload' => 'booking_search'],
                ['title' => '📋 Pilih dari Daftar', 'payload' => 'booking_list'],
                ['title' => '❌ Batal', 'payload' => 'cancel'],
            ])
            ->flow('booking', self::STEP_SELECT_BENGKEL)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle bengkel selection
     */
    private function handleSelectBengkel(User $user, string $message, ?string $payload, array $matches = []): array
    {
        // Handle direct bengkel selection from payload
        if ($payload && preg_match('/^select_bengkel_(\d+)$/', $payload, $m)) {
            return $this->selectBengkel($user, (int) $m[1]);
        }

        // Handle search/nearby/list options
        if ($payload === 'booking_nearby') {
            return $this->showNearbyBengkels($user);
        }

        if ($payload === 'booking_search') {
            return ResponseBuilder::make()
                ->text("Ketik nama bengkel yang ingin dicari:")
                ->cancelQuickReply()
                ->flow('booking', self::STEP_SELECT_BENGKEL)
                ->context((string) $user->id)
                ->build();
        }

        if ($payload === 'booking_list') {
            return $this->showBengkelList($user);
        }

        // Handle search by name
        if ($message) {
            return $this->searchBengkel($user, $message);
        }

        return $this->handleInit($user);
    }

    /**
     * Show nearby bengkels
     */
    private function showNearbyBengkels(User $user): array
    {
        return ResponseBuilder::make()
            ->text("Kirim lokasi Anda dengan format:\nbengkel terdekat -6.2,106.8")
            ->text("(Ganti koordinat dengan lokasi Anda)")
            ->quickReplies([
                ['title' => 'Lihat Semua Bengkel', 'payload' => 'booking_list'],
                ['title' => '❌ Batal', 'payload' => 'cancel'],
            ])
            ->flow('booking', self::STEP_SELECT_BENGKEL)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Show bengkel list
     */
    private function showBengkelList(User $user, int $page = 1): array
    {
        $perPage = 5;
        $bengkels = Bengkel::orderBy('name')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $total = Bengkel::count();
        $totalPages = ceil($total / $perPage);

        $builder = ResponseBuilder::make()
            ->text("📋 *Daftar Bengkel* (Hal {$page}/{$totalPages})\n\nPilih bengkel untuk booking:");

        foreach ($bengkels as $b) {
            $builder->bengkelCard(
                $b->id,
                $b->name,
                $b->alamat,
                $b->image_url ?? null
            );
        }

        $replies = [];
        if ($page > 1) {
            $replies[] = ['title' => '⬅️ Sebelumnya', 'payload' => 'booking_list_page_' . ($page - 1)];
        }
        if ($page < $totalPages) {
            $replies[] = ['title' => 'Selanjutnya ➡️', 'payload' => 'booking_list_page_' . ($page + 1)];
        }
        $replies[] = ['title' => '❌ Batal', 'payload' => 'cancel'];

        return $builder
            ->quickReplies($replies)
            ->flow('booking', self::STEP_SELECT_BENGKEL)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Search bengkel by name
     */
    private function searchBengkel(User $user, string $keyword): array
    {
        $bengkels = Bengkel::where('name', 'LIKE', '%' . $keyword . '%')
            ->orWhere('alamat', 'LIKE', '%' . $keyword . '%')
            ->limit(5)
            ->get();

        if ($bengkels->isEmpty()) {
            return ResponseBuilder::make()
                ->text("Bengkel tidak ditemukan untuk \"{$keyword}\".")
                ->quickReplies([
                    ['title' => 'Cari Lagi', 'payload' => 'booking_search'],
                    ['title' => 'Lihat Semua', 'payload' => 'booking_list'],
                    ['title' => '❌ Batal', 'payload' => 'cancel'],
                ])
                ->flow('booking', self::STEP_SELECT_BENGKEL)
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("Ditemukan {$bengkels->count()} bengkel:");

        foreach ($bengkels as $b) {
            $builder->bengkelCard($b->id, $b->name, $b->alamat, $b->image_url ?? null);
        }

        $builder->quickReply('❌ Batal', 'cancel');

        return $builder
            ->flow('booking', self::STEP_SELECT_BENGKEL)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Select a bengkel and show its info
     */
    private function selectBengkel(User $user, int $bengkelId): array
    {
        $bengkel = Bengkel::with(['layanans', 'jadwals'])->find($bengkelId);

        if (!$bengkel) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.bengkel_not_found'))
                ->quickReplies([
                    ['title' => 'Pilih Bengkel Lain', 'payload' => 'booking_list'],
                    ['title' => '❌ Batal', 'payload' => 'cancel'],
                ])
                ->flow('booking', self::STEP_SELECT_BENGKEL)
                ->context((string) $user->id)
                ->build();
        }

        // Save bengkel to context
        $this->contextManager->updateStep($user->id, self::STEP_CONFIRM_BENGKEL, [
            'bengkel_id' => $bengkel->id,
            'bengkel_name' => $bengkel->name,
        ]);

        $info = "🏪 *{$bengkel->name}*\n\n";
        $info .= "📍 {$bengkel->alamat}\n\n";

        if ($bengkel->layanans->isNotEmpty()) {
            $info .= "🔧 *Layanan tersedia:*\n";
            foreach ($bengkel->layanans->take(5) as $layanan) {
                $info .= "• {$layanan->name} - Rp " . number_format($layanan->price, 0, ',', '.') . "\n";
            }
            if ($bengkel->layanans->count() > 5) {
                $info .= "... dan " . ($bengkel->layanans->count() - 5) . " layanan lainnya\n";
            }
        }

        return ResponseBuilder::make()
            ->text($info)
            ->text("Lanjutkan booking di bengkel ini?")
            ->quickReplies([
                ['title' => '✅ Ya, Lanjutkan', 'payload' => 'confirm_bengkel'],
                ['title' => '🔄 Ganti Bengkel', 'payload' => 'booking_list'],
                ['title' => '❌ Batal', 'payload' => 'cancel'],
            ])
            ->flow('booking', self::STEP_CONFIRM_BENGKEL)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Confirm bengkel selection and ask for date
     */
    private function handleConfirmBengkel(User $user, string $message, ?string $payload): array
    {
        if ($payload === 'confirm_bengkel') {
            $this->contextManager->updateStep($user->id, self::STEP_SELECT_DATE);
            return $this->showDatePicker($user);
        }

        return $this->handleSelectBengkel($user, $message, $payload, []);
    }

    /**
     * Show date picker
     */
    private function showDatePicker(User $user): array
    {
        $builder = ResponseBuilder::make()
            ->text("📅 *Pilih Tanggal Booking*\n\nPilih tanggal atau ketik dengan format: YYYY-MM-DD");

        // Generate next 7 days
        $replies = [];
        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::now()->addDays($i);
            $label = $i === 0 ? 'Hari Ini' : ($i === 1 ? 'Besok' : $date->format('D, d M'));
            $replies[] = ['title' => $label, 'payload' => 'select_date_' . $date->format('Y-m-d')];
        }
        $replies[] = ['title' => '❌ Batal', 'payload' => 'cancel'];

        return $builder
            ->quickReplies($replies)
            ->flow('booking', self::STEP_SELECT_DATE)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle date selection
     */
    private function handleSelectDate(User $user, string $message, ?string $payload): array
    {
        $date = null;

        // From payload
        if ($payload && preg_match('/^select_date_(\d{4}-\d{2}-\d{2})$/', $payload, $m)) {
            $date = $m[1];
        }
        // From message
        elseif (preg_match('/^(\d{4}-\d{2}-\d{2})$/', trim($message), $m)) {
            $date = $m[1];
        }
        // Try DD/MM/YYYY format
        elseif (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', trim($message), $m)) {
            $date = "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        if (!$date) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.invalid_date'))
                ->text("Gunakan format: YYYY-MM-DD atau pilih dari opsi.")
                ->cancelQuickReply()
                ->flow('booking', self::STEP_SELECT_DATE)
                ->context((string) $user->id)
                ->build();
        }

        // Validate date
        try {
            $dateObj = Carbon::createFromFormat('Y-m-d', $date);
            if ($dateObj->lt(Carbon::today())) {
                return ResponseBuilder::make()
                    ->text(config('chat.errors.past_date'))
                    ->cancelQuickReply()
                    ->flow('booking', self::STEP_SELECT_DATE)
                    ->context((string) $user->id)
                    ->build();
            }
        } catch (\Exception $e) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.invalid_date'))
                ->cancelQuickReply()
                ->flow('booking', self::STEP_SELECT_DATE)
                ->context((string) $user->id)
                ->build();
        }

        // Check if bengkel is open on this day
        $data = $this->contextManager->getFlowData($user->id);
        $bengkel = Bengkel::with('jadwals')->find($data['bengkel_id']);
        $jadwal = $bengkel->jadwals->first();

        if ($jadwal) {
            $dayMap = ['Sunday' => 'minggu', 'Monday' => 'senin', 'Tuesday' => 'selasa',
                       'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu'];
            $dayName = $dayMap[$dateObj->format('l')];
            $openField = $dayName . '_buka';
            $closeField = $dayName . '_tutup';

            if (empty($jadwal->$openField) || empty($jadwal->$closeField)) {
                return ResponseBuilder::make()
                    ->text(config('chat.errors.bengkel_closed') . " ({$dayName})")
                    ->quickReplies([
                        ['title' => 'Pilih Tanggal Lain', 'payload' => 'back_to_date'],
                        ['title' => '❌ Batal', 'payload' => 'cancel'],
                    ])
                    ->flow('booking', self::STEP_SELECT_DATE)
                    ->context((string) $user->id)
                    ->build();
            }
        }

        // Save date and move to time selection
        $this->contextManager->updateStep($user->id, self::STEP_SELECT_TIME, [
            'tanggal_booking' => $date,
        ]);

        return $this->showTimePicker($user, $date);
    }

    /**
     * Show time picker
     */
    private function showTimePicker(User $user, string $date): array
    {
        $data = $this->contextManager->getFlowData($user->id);
        $bengkel = Bengkel::with('jadwals')->find($data['bengkel_id']);
        $jadwal = $bengkel->jadwals->first();

        // Get booked times
        $bookedTimes = Booking::where('bengkel_id', $data['bengkel_id'])
            ->where('tanggal_booking', $date)
            ->pluck('waktu_booking')
            ->map(fn($t) => substr($t, 0, 5))
            ->toArray();

        // Generate available slots
        $dateObj = Carbon::createFromFormat('Y-m-d', $date);
        $dayMap = ['Sunday' => 'minggu', 'Monday' => 'senin', 'Tuesday' => 'selasa',
                   'Wednesday' => 'rabu', 'Thursday' => 'kamis', 'Friday' => 'jumat', 'Saturday' => 'sabtu'];
        $dayName = $dayMap[$dateObj->format('l')];

        $openTime = $jadwal ? substr($jadwal->{$dayName . '_buka'} ?? '08:00:00', 0, 5) : '08:00';
        $closeTime = $jadwal ? substr($jadwal->{$dayName . '_tutup'} ?? '17:00:00', 0, 5) : '17:00';

        $slots = [];
        $current = Carbon::createFromFormat('H:i', $openTime);
        $end = Carbon::createFromFormat('H:i', $closeTime);
        $now = Carbon::now();

        while ($current->lt($end)) {
            $slot = $current->format('H:i');

            // Skip past times if booking for today
            if ($date === Carbon::today()->format('Y-m-d') && $current->lt($now)) {
                $current->addHour();
                continue;
            }

            // Mark as available or booked
            if (!in_array($slot, $bookedTimes)) {
                $slots[] = $slot;
            }

            $current->addHour();
        }

        if (empty($slots)) {
            return ResponseBuilder::make()
                ->text("Tidak ada slot waktu tersedia untuk tanggal ini.")
                ->quickReplies([
                    ['title' => 'Pilih Tanggal Lain', 'payload' => 'back_to_date'],
                    ['title' => '❌ Batal', 'payload' => 'cancel'],
                ])
                ->flow('booking', self::STEP_SELECT_DATE)
                ->context((string) $user->id)
                ->build();
        }

        $builder = ResponseBuilder::make()
            ->text("🕐 *Pilih Waktu Booking*\n\nTanggal: " . ResponseBuilder::formatDate($date))
            ->text("Jam operasional: {$openTime} - {$closeTime}")
            ->timePicker($date, $slots, $bookedTimes);

        // Add time slots as quick replies (max 6)
        $replies = [];
        foreach (array_slice($slots, 0, 6) as $slot) {
            $replies[] = ['title' => $slot, 'payload' => 'select_time_' . $slot];
        }
        $replies[] = ['title' => '📅 Ganti Tanggal', 'payload' => 'back_to_date'];
        $replies[] = ['title' => '❌ Batal', 'payload' => 'cancel'];

        return $builder
            ->quickReplies($replies)
            ->flow('booking', self::STEP_SELECT_TIME)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle time selection
     */
    private function handleSelectTime(User $user, string $message, ?string $payload): array
    {
        if ($payload === 'back_to_date') {
            $this->contextManager->updateStep($user->id, self::STEP_SELECT_DATE);
            return $this->showDatePicker($user);
        }

        $time = null;

        if ($payload && preg_match('/^select_time_(\d{2}:\d{2})$/', $payload, $m)) {
            $time = $m[1];
        } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', trim($message), $m)) {
            $time = sprintf('%02d:%02d', $m[1], $m[2]);
        }

        if (!$time) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.invalid_time'))
                ->cancelQuickReply()
                ->flow('booking', self::STEP_SELECT_TIME)
                ->context((string) $user->id)
                ->build();
        }

        // Save time and move to vehicle info
        $this->contextManager->updateStep($user->id, self::STEP_VEHICLE_BRAND, [
            'waktu_booking' => $time,
        ]);

        return $this->showBrandPicker($user);
    }

    /**
     * Show vehicle brand picker
     */
    private function showBrandPicker(User $user): array
    {
        $brands = config('chat.booking.brands', ['Honda', 'Toyota', 'Suzuki', 'Yamaha', 'Lainnya']);

        $replies = [];
        foreach (array_slice($brands, 0, 6) as $brand) {
            $replies[] = ['title' => $brand, 'payload' => 'select_brand_' . $brand];
        }
        $replies[] = ['title' => '❌ Batal', 'payload' => 'cancel'];

        return ResponseBuilder::make()
            ->text("🚗 *Data Kendaraan*\n\nPilih merk kendaraan atau ketik manual:")
            ->quickReplies($replies)
            ->flow('booking', self::STEP_VEHICLE_BRAND)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle brand selection
     */
    private function handleVehicleBrand(User $user, string $message, ?string $payload): array
    {
        $brand = null;

        if ($payload && preg_match('/^select_brand_(.+)$/', $payload, $m)) {
            $brand = $m[1];
        } elseif (!empty(trim($message))) {
            $brand = trim($message);
        }

        if (!$brand) {
            return $this->showBrandPicker($user);
        }

        $this->contextManager->updateStep($user->id, self::STEP_VEHICLE_MODEL, ['brand' => $brand]);

        return ResponseBuilder::make()
            ->text("Merk: *{$brand}*\n\nKetik model kendaraan (contoh: Vario, Avanza, Beat):")
            ->cancelQuickReply()
            ->flow('booking', self::STEP_VEHICLE_MODEL)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle vehicle model
     */
    private function handleVehicleModel(User $user, string $message): array
    {
        $model = trim($message);

        if (empty($model)) {
            return ResponseBuilder::make()
                ->text("Ketik model kendaraan Anda:")
                ->cancelQuickReply()
                ->flow('booking', self::STEP_VEHICLE_MODEL)
                ->context((string) $user->id)
                ->build();
        }

        $this->contextManager->updateStep($user->id, self::STEP_VEHICLE_PLAT, ['model' => $model]);

        return ResponseBuilder::make()
            ->text("Model: *{$model}*\n\nKetik plat nomor kendaraan (contoh: B 1234 ABC):")
            ->cancelQuickReply()
            ->flow('booking', self::STEP_VEHICLE_PLAT)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle vehicle plate
     */
    private function handleVehiclePlat(User $user, string $message): array
    {
        $plat = strtoupper(trim($message));

        if (empty($plat)) {
            return ResponseBuilder::make()
                ->text("Ketik plat nomor kendaraan:")
                ->cancelQuickReply()
                ->flow('booking', self::STEP_VEHICLE_PLAT)
                ->context((string) $user->id)
                ->build();
        }

        $this->contextManager->updateStep($user->id, self::STEP_VEHICLE_YEAR, ['plat' => $plat]);

        // Generate year options
        $currentYear = (int) date('Y');
        $years = range($currentYear, $currentYear - 10);
        $replies = [];
        foreach (array_slice($years, 0, 6) as $year) {
            $replies[] = ['title' => (string) $year, 'payload' => 'select_year_' . $year];
        }
        $replies[] = ['title' => '❌ Batal', 'payload' => 'cancel'];

        return ResponseBuilder::make()
            ->text("Plat: *{$plat}*\n\nPilih tahun pembuatan kendaraan:")
            ->quickReplies($replies)
            ->flow('booking', self::STEP_VEHICLE_YEAR)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle vehicle year
     */
    private function handleVehicleYear(User $user, string $message, ?string $payload): array
    {
        $year = null;

        if ($payload && preg_match('/^select_year_(\d{4})$/', $payload, $m)) {
            $year = (int) $m[1];
        } elseif (preg_match('/^\d{4}$/', trim($message))) {
            $year = (int) trim($message);
        }

        if (!$year || $year < 1990 || $year > (int) date('Y') + 1) {
            return ResponseBuilder::make()
                ->text("Tahun tidak valid. Masukkan tahun antara 1990 - " . date('Y'))
                ->cancelQuickReply()
                ->flow('booking', self::STEP_VEHICLE_YEAR)
                ->context((string) $user->id)
                ->build();
        }

        $this->contextManager->updateStep($user->id, self::STEP_VEHICLE_KM, ['tahun_pembuatan' => $year]);

        return ResponseBuilder::make()
            ->text("Tahun: *{$year}*\n\nKetik kilometer kendaraan saat ini (contoh: 15000):")
            ->cancelQuickReply()
            ->flow('booking', self::STEP_VEHICLE_KM)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle vehicle kilometer
     */
    private function handleVehicleKm(User $user, string $message): array
    {
        $km = preg_replace('/[^0-9]/', '', trim($message));

        if (empty($km) || !is_numeric($km)) {
            return ResponseBuilder::make()
                ->text("Masukkan angka kilometer (contoh: 15000):")
                ->cancelQuickReply()
                ->flow('booking', self::STEP_VEHICLE_KM)
                ->context((string) $user->id)
                ->build();
        }

        $this->contextManager->updateStep($user->id, self::STEP_VEHICLE_TRANSMISI, ['kilometer' => (int) $km]);

        return ResponseBuilder::make()
            ->text("Kilometer: *" . number_format((int) $km, 0, ',', '.') . " km*\n\nPilih jenis transmisi:")
            ->quickReplies([
                ['title' => 'Manual', 'payload' => 'select_transmisi_manual'],
                ['title' => 'Automatic', 'payload' => 'select_transmisi_automatic'],
                ['title' => '❌ Batal', 'payload' => 'cancel'],
            ])
            ->flow('booking', self::STEP_VEHICLE_TRANSMISI)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle transmission selection
     */
    private function handleVehicleTransmisi(User $user, string $message, ?string $payload): array
    {
        $transmisi = null;

        if ($payload && preg_match('/^select_transmisi_(manual|automatic)$/', $payload, $m)) {
            $transmisi = $m[1];
        } elseif (preg_match('/^(manual|automatic|matic|at|mt)$/i', trim($message), $m)) {
            $transmisi = in_array(strtolower($m[1]), ['automatic', 'matic', 'at']) ? 'automatic' : 'manual';
        }

        if (!$transmisi) {
            return ResponseBuilder::make()
                ->text("Pilih jenis transmisi:")
                ->quickReplies([
                    ['title' => 'Manual', 'payload' => 'select_transmisi_manual'],
                    ['title' => 'Automatic', 'payload' => 'select_transmisi_automatic'],
                    ['title' => '❌ Batal', 'payload' => 'cancel'],
                ])
                ->flow('booking', self::STEP_VEHICLE_TRANSMISI)
                ->context((string) $user->id)
                ->build();
        }

        $this->contextManager->updateStep($user->id, self::STEP_NOTES, ['transmisi' => $transmisi]);

        return ResponseBuilder::make()
            ->text("Transmisi: *" . ucfirst($transmisi) . "*\n\nAda catatan tambahan untuk bengkel? (ketik 'skip' jika tidak ada)")
            ->quickReplies([
                ['title' => 'Skip', 'payload' => 'skip_notes'],
                ['title' => '❌ Batal', 'payload' => 'cancel'],
            ])
            ->flow('booking', self::STEP_NOTES)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle notes
     */
    private function handleNotes(User $user, string $message): array
    {
        $notes = null;

        if (strtolower(trim($message)) !== 'skip' && !empty(trim($message))) {
            $notes = trim($message);
        }

        $this->contextManager->updateStep($user->id, self::STEP_CONFIRM, ['catatan_tambahan' => $notes]);

        return $this->showConfirmation($user);
    }

    /**
     * Show booking confirmation
     */
    private function showConfirmation(User $user): array
    {
        $data = $this->contextManager->getFlowData($user->id);

        return ResponseBuilder::make()
            ->bookingSummary([
                'bengkel_name' => $data['bengkel_name'],
                'tanggal' => ResponseBuilder::formatDate($data['tanggal_booking']),
                'waktu' => $data['waktu_booking'],
                'brand' => $data['brand'],
                'model' => $data['model'],
                'plat' => $data['plat'],
                'tahun' => $data['tahun_pembuatan'],
                'kilometer' => number_format($data['kilometer'], 0, ',', '.'),
                'transmisi' => ucfirst($data['transmisi']),
                'catatan' => $data['catatan_tambahan'] ?? null,
            ])
            ->text("Apakah data di atas sudah benar?")
            ->quickReplies([
                ['title' => '✅ Konfirmasi Booking', 'payload' => 'confirm_booking'],
                ['title' => '✏️ Edit Data', 'payload' => 'booking_prompt'],
                ['title' => '❌ Batal', 'payload' => 'cancel'],
            ])
            ->flow('booking', self::STEP_CONFIRM)
            ->context((string) $user->id)
            ->build();
    }

    /**
     * Handle confirmation
     */
    private function handleConfirm(User $user, string $message, ?string $payload): array
    {
        if ($payload !== 'confirm_booking') {
            if ($payload === 'booking_prompt') {
                return $this->handleInit($user);
            }
            return $this->showConfirmation($user);
        }

        // Create booking
        $data = $this->contextManager->getFlowData($user->id);

        try {
            $booking = Booking::create([
                'user_id' => $user->id,
                'bengkel_id' => $data['bengkel_id'],
                'tanggal_booking' => $data['tanggal_booking'],
                'waktu_booking' => $data['waktu_booking'],
                'brand' => $data['brand'],
                'model' => $data['model'],
                'plat' => $data['plat'],
                'tahun_pembuatan' => $data['tahun_pembuatan'],
                'kilometer' => $data['kilometer'],
                'transmisi' => $data['transmisi'],
                'catatan_tambahan' => $data['catatan_tambahan'],
                'booking_status' => 'Pending',
            ]);

            // Clear flow
            $this->contextManager->clearFlow($user->id);

            return ResponseBuilder::make()
                ->text("✅ *Booking Berhasil!*\n\nBooking ID: #{$booking->id}")
                ->text("Silakan tunggu konfirmasi dari bengkel. Anda akan mendapat notifikasi saat booking dikonfirmasi.")
                ->bookingCard(
                    $booking->id,
                    ['name' => $data['bengkel_name']],
                    ResponseBuilder::formatDate($data['tanggal_booking']),
                    $data['waktu_booking'],
                    [
                        'brand' => $data['brand'],
                        'model' => $data['model'],
                        'plat' => $data['plat'],
                    ],
                    '🟡 Pending'
                )
                ->quickReplies([
                    ['title' => 'Lihat Booking Saya', 'payload' => 'booking_history_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();

        } catch (\Exception $e) {
            return ResponseBuilder::make()
                ->text(config('chat.errors.booking_failed'))
                ->text("Error: " . $e->getMessage())
                ->quickReplies([
                    ['title' => 'Coba Lagi', 'payload' => 'booking_prompt'],
                    ['title' => 'Menu Utama', 'payload' => 'menu'],
                ])
                ->context((string) $user->id)
                ->build();
        }
    }
}
