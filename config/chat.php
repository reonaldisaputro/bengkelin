<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'prefix' => 'chat_context_',
        'ttl' => 1800, // 30 minutes in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Intent Matching Configuration
    |--------------------------------------------------------------------------
    */
    'intent' => [
        'fuzzy_threshold' => 70, // minimum similarity percentage for fuzzy matching
    ],

    /*
    |--------------------------------------------------------------------------
    | Synonym Mappings
    |--------------------------------------------------------------------------
    | Maps alternative words to canonical intents for better matching
    */
    'synonyms' => [
        'booking' => ['pesan', 'reservasi', 'jadwal', 'book', 'servis', 'service', 'daftar'],
        'product' => ['produk', 'spare part', 'barang', 'item', 'part', 'sparepart'],
        'search' => ['cari', 'temukan', 'lihat', 'find', 'tampilkan'],
        'buy' => ['beli', 'order', 'checkout'],
        'cancel' => ['batal', 'batalkan', 'stop', 'cancel', 'keluar'],
        'help' => ['bantuan', 'tolong', 'cara', 'bagaimana', 'gimana', 'how'],
        'history' => ['riwayat', 'histori', 'daftar', 'list'],
        'status' => ['status', 'pesanan', 'order', 'transaksi', 'tracking'],
        'rating' => ['rating', 'ulas', 'ulasan', 'review', 'nilai'],
        'nearby' => ['terdekat', 'dekat', 'sekitar', 'nearby', 'lokasi'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking Configuration
    |--------------------------------------------------------------------------
    */
    'booking' => [
        'max_days_ahead' => 30,
        'slot_duration' => 60, // minutes per slot
        'brands' => [
            'Honda',
            'Toyota',
            'Suzuki',
            'Yamaha',
            'Kawasaki',
            'Daihatsu',
            'Mitsubishi',
            'Nissan',
            'Hyundai',
            'Mazda',
            'Lainnya',
        ],
        'transmissions' => [
            'manual' => 'Manual',
            'automatic' => 'Automatic',
        ],
        'year_range' => [
            'min' => 2000,
            'max' => 2025,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | FAQ Content
    |--------------------------------------------------------------------------
    */
    'faqs' => [
        'cara_booking' => [
            'keywords' => ['cara booking', 'bagaimana booking', 'cara pesan', 'how to book', 'gimana booking', 'cara reservasi'],
            'question' => 'Bagaimana cara melakukan booking?',
            'answer' => "Untuk melakukan booking bengkel:\n\n1. Pilih bengkel yang diinginkan\n2. Pilih tanggal dan waktu yang tersedia\n3. Isi data kendaraan (merk, model, plat, dll)\n4. Konfirmasi booking\n5. Tunggu konfirmasi dari bengkel\n\nAnda juga bisa booking langsung via chat dengan mengetik 'booking'.",
        ],
        'cara_bayar' => [
            'keywords' => ['cara bayar', 'pembayaran', 'metode bayar', 'payment', 'bayar gimana', 'cara membayar'],
            'question' => 'Bagaimana cara melakukan pembayaran?',
            'answer' => "Pembayaran dapat dilakukan melalui:\n\n1. Transfer Bank (BCA, BNI, BRI, Mandiri)\n2. E-Wallet (GoPay, OVO, Dana, ShopeePay)\n3. Virtual Account\n4. Kartu Kredit/Debit\n\nSetelah checkout, Anda akan diarahkan ke halaman pembayaran Midtrans untuk menyelesaikan transaksi.",
        ],
        'cara_cancel' => [
            'keywords' => ['cara cancel', 'batalkan', 'batal booking', 'refund', 'cara membatalkan', 'cancel booking'],
            'question' => 'Bagaimana cara membatalkan booking?',
            'answer' => "Untuk membatalkan booking:\n\n1. Buka menu 'Booking Saya' atau ketik 'booking saya'\n2. Pilih booking yang ingin dibatalkan\n3. Klik tombol 'Batalkan'\n\nCatatan: Pembatalan hanya bisa dilakukan jika status booking masih 'Pending'. Booking yang sudah 'Diterima' tidak dapat dibatalkan melalui aplikasi.",
        ],
        'jam_operasional' => [
            'keywords' => ['jam operasional', 'jam buka', 'jam tutup', 'waktu buka', 'buka jam berapa', 'operasional'],
            'question' => 'Kapan jam operasional bengkel?',
            'answer' => "Jam operasional berbeda untuk setiap bengkel. Anda dapat melihat jadwal lengkap di halaman detail bengkel masing-masing.\n\nUmumnya bengkel buka:\n- Senin-Sabtu: 08:00 - 17:00\n- Minggu: Tutup (beberapa bengkel tetap buka)\n\nPastikan cek jadwal sebelum booking!",
        ],
        'ongkir' => [
            'keywords' => ['ongkir', 'ongkos kirim', 'biaya kirim', 'shipping', 'pengiriman', 'delivery'],
            'question' => 'Berapa biaya pengiriman?',
            'answer' => "Biaya pengiriman (ongkir) dihitung berdasarkan:\n\n1. Jarak pengiriman\n2. Berat total produk\n3. Layanan kurir yang dipilih\n\nAnda dapat melihat estimasi ongkir saat checkout setelah memasukkan alamat pengiriman.",
        ],
        'cara_daftar' => [
            'keywords' => ['cara daftar', 'registrasi', 'buat akun', 'sign up', 'register', 'mendaftar'],
            'question' => 'Bagaimana cara mendaftar akun?',
            'answer' => "Untuk mendaftar akun baru:\n\n1. Buka halaman Register/Daftar\n2. Isi nama lengkap, email, dan nomor HP\n3. Buat password yang kuat\n4. Klik tombol 'Daftar'\n5. Verifikasi email (jika diperlukan)\n\nSetelah terdaftar, Anda bisa langsung booking bengkel atau belanja spare part!",
        ],
        'cara_rating' => [
            'keywords' => ['cara rating', 'cara ulas', 'beri rating', 'review', 'kasih bintang', 'ulasan'],
            'question' => 'Bagaimana cara memberikan rating/ulasan?',
            'answer' => "Untuk memberikan rating:\n\n1. Ketik 'rating' atau pilih menu 'Item Belum Dirating'\n2. Pilih item yang ingin diulas\n3. Berikan bintang (1-5) dan komentar\n\nRating hanya bisa diberikan untuk transaksi yang sudah selesai. Ulasan Anda sangat membantu pengguna lain!",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    */
    'errors' => [
        'bengkel_not_found' => 'Bengkel tidak ditemukan. Silakan pilih bengkel lain.',
        'slot_taken' => 'Maaf, slot waktu ini sudah terisi. Silakan pilih waktu lain.',
        'invalid_date' => 'Format tanggal tidak valid. Gunakan format YYYY-MM-DD atau pilih dari opsi.',
        'invalid_time' => 'Format waktu tidak valid. Pilih dari slot yang tersedia.',
        'bengkel_closed' => 'Bengkel tutup pada hari tersebut. Silakan pilih hari lain.',
        'past_date' => 'Tidak bisa booking untuk tanggal yang sudah lewat.',
        'past_time' => 'Tidak bisa booking untuk waktu yang sudah lewat.',
        'out_of_stock' => 'Maaf, produk tidak tersedia. Stok habis.',
        'product_not_found' => 'Produk tidak ditemukan.',
        'cart_bengkel_conflict' => 'Keranjang Anda berisi produk dari bengkel lain. Kosongkan keranjang terlebih dahulu atau checkout dulu.',
        'booking_failed' => 'Gagal membuat booking. Silakan coba lagi.',
        'session_expired' => 'Sesi percakapan telah berakhir. Mari mulai dari awal.',
        'invalid_input' => 'Input tidak valid. Silakan coba lagi.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Success Messages
    |--------------------------------------------------------------------------
    */
    'messages' => [
        'booking_created' => 'Booking berhasil dibuat! Silakan tunggu konfirmasi dari bengkel.',
        'added_to_cart' => 'Produk berhasil ditambahkan ke keranjang.',
        'flow_cancelled' => 'Dibatalkan. Kembali ke menu utama.',
        'welcome' => 'Hai %s! Ada yang bisa saya bantu?',
    ],
];
