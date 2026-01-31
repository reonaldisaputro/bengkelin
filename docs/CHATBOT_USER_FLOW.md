# User Flow Fitur Chatbot Aplikasi Bengkelin

## Daftar Isi

1. [Pendahuluan](#1-pendahuluan)
2. [Deskripsi Umum Sistem](#2-deskripsi-umum-sistem)
3. [Arsitektur Chatbot](#3-arsitektur-chatbot)
4. [Use Case Diagram](#4-use-case-diagram)
5. [Activity Diagram](#5-activity-diagram)
6. [Alur Pengguna (User Flow)](#6-alur-pengguna-user-flow)
7. [Algoritma Intent Recognition](#7-algoritma-intent-recognition)
8. [Manajemen State dan Context](#8-manajemen-state-dan-context)
9. [Spesifikasi Komponen Response](#9-spesifikasi-komponen-response)

---

## 1. Pendahuluan

### 1.1 Latar Belakang

Fitur chatbot pada aplikasi Bengkelin dikembangkan untuk memberikan kemudahan bagi pengguna dalam mengakses layanan bengkel dan marketplace spare part melalui antarmuka percakapan (conversational interface). Chatbot ini mengimplementasikan konsep Natural Language Processing (NLP) sederhana dengan pendekatan rule-based dan fuzzy matching untuk memahami maksud (intent) pengguna.

### 1.2 Tujuan

Tujuan pengembangan fitur chatbot adalah sebagai berikut:
1. Menyediakan antarmuka alternatif yang lebih intuitif bagi pengguna untuk mengakses fitur-fitur aplikasi
2. Memudahkan proses booking bengkel melalui alur percakapan yang terstruktur
3. Memberikan kemudahan pencarian produk spare part
4. Menyediakan informasi status pesanan dan riwayat transaksi
5. Memberikan layanan bantuan (FAQ) secara otomatis

### 1.3 Ruang Lingkup

Fitur chatbot mencakup fungsi-fungsi berikut:
- Booking bengkel (multi-step conversation flow)
- Pencarian dan pembelian produk spare part
- Pengecekan status pesanan/transaksi
- Pencarian bengkel terdekat berbasis lokasi
- Pengelolaan riwayat booking
- Pemberian rating dan ulasan
- Layanan FAQ (Frequently Asked Questions)

---

## 2. Deskripsi Umum Sistem

### 2.1 Teknologi yang Digunakan

| Komponen | Teknologi | Versi | Fungsi |
|----------|-----------|-------|--------|
| Framework Chatbot | BotMan | 2.8 | Framework utama untuk membangun chatbot |
| Web Driver | BotMan Web Driver | 1.5 | Driver untuk komunikasi via web/API |
| Backend Framework | Laravel | 10.x | Framework aplikasi backend |
| Autentikasi | Laravel Sanctum | - | Autentikasi API berbasis token |
| State Management | Laravel Cache | - | Penyimpanan state percakapan |

### 2.2 Karakteristik Chatbot

Chatbot Bengkelin memiliki karakteristik sebagai berikut:

1. **Rule-Based dengan Fuzzy Matching**: Sistem menggunakan pendekatan berbasis aturan (rule-based) yang dikombinasikan dengan fuzzy matching untuk mengenali intent pengguna
2. **Multi-Turn Conversation**: Mendukung percakapan multi-langkah dengan manajemen state
3. **Rich Response**: Mendukung berbagai tipe response seperti teks, kartu, carousel, dan time picker
4. **Bilingual Support**: Mendukung kata kunci dalam Bahasa Indonesia dan Bahasa Inggris

---

## 3. Arsitektur Chatbot

### 3.1 Diagram Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              PRESENTATION LAYER                              │
│                                                                              │
│    ┌────────────────────┐         ┌────────────────────┐                    │
│    │   Mobile App       │         │    Web Interface   │                    │
│    │   (Flutter)        │         │                    │                    │
│    └─────────┬──────────┘         └─────────┬──────────┘                    │
└──────────────┼──────────────────────────────┼───────────────────────────────┘
               │                              │
               └──────────────┬───────────────┘
                              │ HTTP Request
                              ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              API LAYER                                       │
│                                                                              │
│    ┌────────────────────────────────────────────────────────────────────┐   │
│    │                    ChatApiController                                │   │
│    │                    POST /api/chat/send                              │   │
│    │    Input: message, payload, latitude, longitude, radius             │   │
│    └─────────────────────────────┬──────────────────────────────────────┘   │
└──────────────────────────────────┼──────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              SERVICE LAYER                                   │
│                                                                              │
│    ┌────────────────────────────────────────────────────────────────────┐   │
│    │                         ChatService                                 │   │
│    │                    (Main Orchestrator)                              │   │
│    └─────────────────────────────┬──────────────────────────────────────┘   │
│                                  │                                          │
│         ┌────────────────────────┼────────────────────────┐                 │
│         │                        │                        │                 │
│         ▼                        ▼                        ▼                 │
│    ┌──────────────┐       ┌──────────────┐       ┌──────────────┐          │
│    │IntentMatcher │       │ContextManager│       │ResponseBuilder│          │
│    │(NLP Engine)  │       │(State Mgmt)  │       │(Output Builder)│         │
│    └──────────────┘       └──────────────┘       └──────────────┘          │
└─────────────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              HANDLER LAYER                                   │
│                                                                              │
│    ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐             │
│    │MenuHandler │ │ FaqHandler │ │StatusHandler│ │RatingHandler│            │
│    └────────────┘ └────────────┘ └────────────┘ └────────────┘             │
│                                                                              │
│    ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐             │
│    │NearbyHandler│ │ProductSearch│ │BookingHistory│ │BookingFlow │           │
│    └────────────┘ └────────────┘ └────────────┘ └────────────┘             │
└─────────────────────────────────────────────────────────────────────────────┘
                                   │
                                   ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              DATA LAYER                                      │
│                                                                              │
│    ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐        │
│    │  User    │ │ Bengkel  │ │ Booking  │ │ Product  │ │Transaction│       │
│    └──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘        │
└─────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Deskripsi Komponen

| Komponen | Deskripsi |
|----------|-----------|
| **ChatApiController** | Controller yang menangani request HTTP dari client. Melakukan validasi input dan memanggil ChatService |
| **ChatService** | Komponen utama yang mengorkestrasi alur percakapan. Menentukan handler yang sesuai berdasarkan intent |
| **IntentMatcher** | Komponen yang bertugas mengenali intent (maksud) pengguna dari input yang diberikan |
| **ContextManager** | Komponen yang mengelola state percakapan menggunakan cache. Menyimpan data flow dan step aktif |
| **ResponseBuilder** | Komponen yang membangun response dalam format JSON yang sesuai untuk client |
| **Handler** | Komponen-komponen yang menangani logika bisnis untuk setiap fitur chatbot |

---

## 4. Use Case Diagram

### 4.1 Diagram Use Case

```
                              ┌─────────────────────────────────────────┐
                              │            SISTEM CHATBOT               │
                              │                                         │
    ┌──────┐                  │   ┌─────────────────────────────┐      │
    │      │                  │   │                             │      │
    │      │──────────────────┼──►│   UC01: Melihat Menu Utama  │      │
    │      │                  │   │                             │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │      │──────────────────┼──►│   UC02: Melakukan Booking   │      │
    │      │                  │   │         Bengkel             │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │ USER │──────────────────┼──►│   UC03: Mencari Produk      │      │
    │      │                  │   │                             │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │      │──────────────────┼──►│   UC04: Mengecek Status     │      │
    │      │                  │   │         Pesanan             │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │      │──────────────────┼──►│   UC05: Mencari Bengkel     │      │
    │      │                  │   │         Terdekat            │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │      │──────────────────┼──►│   UC06: Melihat Riwayat     │      │
    │      │                  │   │         Booking             │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │      │──────────────────┼──►│   UC07: Memberikan Rating   │      │
    │      │                  │   │                             │      │
    │      │                  │   └─────────────────────────────┘      │
    │      │                  │                                         │
    │      │                  │   ┌─────────────────────────────┐      │
    │      │──────────────────┼──►│   UC08: Melihat FAQ         │      │
    │      │                  │   │                             │      │
    └──────┘                  │   └─────────────────────────────┘      │
                              │                                         │
                              └─────────────────────────────────────────┘
```

### 4.2 Deskripsi Use Case

| Kode | Nama Use Case | Deskripsi | Aktor |
|------|---------------|-----------|-------|
| UC01 | Melihat Menu Utama | Pengguna dapat melihat daftar menu fitur yang tersedia pada chatbot | User |
| UC02 | Melakukan Booking Bengkel | Pengguna dapat melakukan reservasi layanan bengkel melalui alur percakapan multi-langkah | User |
| UC03 | Mencari Produk | Pengguna dapat mencari produk spare part dan menambahkannya ke keranjang | User |
| UC04 | Mengecek Status Pesanan | Pengguna dapat melihat status transaksi/pesanan yang sedang aktif | User |
| UC05 | Mencari Bengkel Terdekat | Pengguna dapat mencari bengkel berdasarkan lokasi GPS dengan perhitungan jarak | User |
| UC06 | Melihat Riwayat Booking | Pengguna dapat melihat daftar booking yang pernah dibuat | User |
| UC07 | Memberikan Rating | Pengguna dapat memberikan rating dan ulasan untuk item yang telah dibeli | User |
| UC08 | Melihat FAQ | Pengguna dapat melihat daftar pertanyaan yang sering ditanyakan beserta jawabannya | User |

---

## 5. Activity Diagram

### 5.1 Activity Diagram: Alur Utama Chatbot

```
                    ┌───────────────────┐
                    │      START        │
                    └─────────┬─────────┘
                              │
                              ▼
                    ┌───────────────────┐
                    │ User mengirim     │
                    │ pesan/payload     │
                    └─────────┬─────────┘
                              │
                              ▼
                    ┌───────────────────┐
                    │ Sistem menerima   │
                    │ request           │
                    └─────────┬─────────┘
                              │
                              ▼
                    ◇─────────────────────◇
                   ╱                       ╲
                  ╱   Apakah user dalam     ╲
                 ╱    flow aktif?            ╲
                 ╲                           ╱
                  ╲                         ╱
                   ╲                       ╱
                    ◇─────────┬───────────◇
                    │ Ya      │ Tidak
                    ▼         ▼
        ┌───────────────┐   ┌───────────────┐
        │ Cek intent    │   │ IntentMatcher │
        │ pembatalan    │   │ mendeteksi    │
        └───────┬───────┘   │ intent        │
                │           └───────┬───────┘
                ▼                   │
        ◇───────────────◇          │
       ╱                 ╲         │
      ╱  Intent = cancel? ╲        │
      ╲                   ╱        │
       ╲                 ╱         │
        ◇───────┬───────◇          │
        │ Ya    │ Tidak            │
        ▼       ▼                  │
┌───────────┐ ┌───────────┐        │
│ Clear     │ │ Lanjutkan │        │
│ flow,     │ │ ke handler│        │
│ tampilkan │ │ flow aktif│        │
│ menu      │ └─────┬─────┘        │
└─────┬─────┘       │              │
      │             │              │
      │             ▼              ▼
      │     ┌───────────────────────────────┐
      │     │ Route ke Handler yang sesuai  │
      │     │ (Menu/FAQ/Status/Rating/      │
      │     │  Nearby/Product/Booking)      │
      │     └───────────────┬───────────────┘
      │                     │
      │                     ▼
      │             ┌───────────────┐
      │             │ Handler       │
      │             │ memproses     │
      │             │ request       │
      │             └───────┬───────┘
      │                     │
      │                     ▼
      │             ┌───────────────┐
      │             │ ResponseBuilder│
      │             │ membuat       │
      │             │ response      │
      │             └───────┬───────┘
      │                     │
      └─────────────────────┤
                            ▼
                    ┌───────────────┐
                    │ Kirim response│
                    │ ke user       │
                    └───────┬───────┘
                            │
                            ▼
                    ┌───────────────┐
                    │     END       │
                    └───────────────┘
```

### 5.2 Activity Diagram: Booking Bengkel (UC02)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    ACTIVITY DIAGRAM: BOOKING BENGKEL                        │
└─────────────────────────────────────────────────────────────────────────────┘

                              ┌───────────┐
                              │   START   │
                              └─────┬─────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ User memilih menu "Booking    │
                    │ Bengkel" atau mengetik        │
                    │ kata kunci booking            │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ Sistem menampilkan pilihan    │
                    │ cara pencarian bengkel:       │
                    │ - Bengkel Terdekat            │
                    │ - Cari Nama Bengkel           │
                    │ - Pilih dari Daftar           │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ User memilih salah satu       │
                    │ metode pencarian              │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ Sistem menampilkan daftar     │
                    │ bengkel dalam bentuk card     │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ User memilih bengkel          │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ Sistem menampilkan detail     │
                    │ bengkel dan konfirmasi        │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                            ◇───────────────◇
                           ╱                 ╲
                          ╱  User konfirmasi? ╲
                          ╲                   ╱
                           ╲                 ╱
                            ◇───────┬───────◇
                      Ya ───┘       └─── Tidak
                      │                    │
                      ▼                    ▼
┌───────────────────────────────┐  ┌───────────────────┐
│ Sistem menampilkan pilihan    │  │ Kembali ke        │
│ tanggal (7 hari ke depan)     │  │ daftar bengkel    │
└───────────────┬───────────────┘  └───────────────────┘
                │
                ▼
┌───────────────────────────────┐
│ User memilih tanggal          │
└───────────────┬───────────────┘
                │
                ▼
┌───────────────────────────────┐
│ Sistem mengecek jadwal        │
│ bengkel dan slot tersedia     │
└───────────────┬───────────────┘
                │
                ▼
        ◇───────────────◇
       ╱                 ╲
      ╱  Bengkel buka?    ╲
      ╲                   ╱
       ╲                 ╱
        ◇───────┬───────◇
  Ya ───┘       └─── Tidak
  │                    │
  ▼                    ▼
┌─────────────────┐  ┌─────────────────────────┐
│ Tampilkan slot  │  │ Tampilkan pesan error:  │
│ waktu tersedia  │  │ "Bengkel tutup pada     │
│ (time picker)   │  │ hari tersebut"          │
└────────┬────────┘  └─────────────────────────┘
         │
         ▼
┌───────────────────────────────┐
│ User memilih slot waktu       │
└───────────────┬───────────────┘
                │
                ▼
┌───────────────────────────────┐
│ Sistem meminta data kendaraan:│
│ 1. Merk kendaraan             │
│ 2. Model kendaraan            │
│ 3. Plat nomor                 │
│ 4. Tahun pembuatan            │
│ 5. Kilometer                  │
│ 6. Jenis transmisi            │
│ 7. Catatan (opsional)         │
└───────────────┬───────────────┘
                │
                ▼
┌───────────────────────────────┐
│ Sistem menampilkan ringkasan  │
│ booking untuk konfirmasi      │
└───────────────┬───────────────┘
                │
                ▼
        ◇───────────────◇
       ╱                 ╲
      ╱ User konfirmasi   ╲
      ╲ booking?          ╱
       ╲                 ╱
        ◇───────┬───────◇
  Ya ───┘       └─── Tidak/Edit
  │                    │
  ▼                    ▼
┌─────────────────┐  ┌─────────────────┐
│ Sistem menyimpan│  │ Kembali ke step │
│ booking ke      │  │ yang ingin      │
│ database        │  │ diedit          │
└────────┬────────┘  └─────────────────┘
         │
         ▼
┌───────────────────────────────┐
│ Sistem menampilkan pesan      │
│ sukses dan detail booking     │
│ dalam bentuk booking card     │
└───────────────┬───────────────┘
                │
                ▼
          ┌───────────┐
          │    END    │
          └───────────┘
```

### 5.3 Activity Diagram: Pencarian Produk (UC03)

```
                              ┌───────────┐
                              │   START   │
                              └─────┬─────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ User mengetik kata kunci      │
                    │ pencarian produk              │
                    │ (contoh: "cari oli mesin")    │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ IntentMatcher mendeteksi      │
                    │ intent "product_search"       │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ ProductSearchHandler mencari  │
                    │ produk di database            │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                            ◇───────────────◇
                           ╱                 ╲
                          ╱  Produk ditemukan? ╲
                          ╲                   ╱
                           ╲                 ╱
                            ◇───────┬───────◇
                      Ya ───┘       └─── Tidak
                      │                    │
                      ▼                    ▼
┌───────────────────────────────┐  ┌───────────────────────────┐
│ Sistem menampilkan daftar     │  │ Sistem menampilkan pesan  │
│ produk dalam bentuk           │  │ "Produk tidak ditemukan"  │
│ product card                  │  │ dengan saran pencarian    │
└───────────────┬───────────────┘  └───────────────────────────┘
                │
                ▼
┌───────────────────────────────┐
│ User memilih "Tambah ke       │
│ Keranjang" pada produk        │
└───────────────┬───────────────┘
                │
                ▼
        ◇───────────────────────◇
       ╱                         ╲
      ╱  Validasi keranjang:      ╲
      ╲  - Stock tersedia?        ╱
       ╲ - Bengkel sama?         ╱
        ◇───────────┬───────────◇
          Valid ────┘    └──── Invalid
            │                    │
            ▼                    ▼
┌─────────────────────┐  ┌─────────────────────────────┐
│ Produk ditambahkan  │  │ Tampilkan pesan error:      │
│ ke keranjang        │  │ - "Stock tidak tersedia"    │
│                     │  │ - "Tidak dapat menambahkan  │
│                     │  │   produk dari bengkel       │
│                     │  │   berbeda"                  │
└──────────┬──────────┘  └─────────────────────────────┘
           │
           ▼
     ┌───────────┐
     │    END    │
     └───────────┘
```

### 5.4 Activity Diagram: Pencarian Bengkel Terdekat (UC05)

```
                              ┌───────────┐
                              │   START   │
                              └─────┬─────┘
                                    │
                                    ▼
                    ┌───────────────────────────────┐
                    │ User memilih menu "Bengkel    │
                    │ Terdekat" atau mengetik       │
                    │ kata kunci                    │
                    └───────────────┬───────────────┘
                                    │
                                    ▼
                            ◇───────────────◇
                           ╱                 ╲
                          ╱  Koordinat GPS    ╲
                          ╲  tersedia?        ╱
                           ╲                 ╱
                            ◇───────┬───────◇
                      Ya ───┘       └─── Tidak
                      │                    │
                      ▼                    ▼
┌───────────────────────────┐  ┌───────────────────────────┐
│ Sistem menerima parameter │  │ Sistem meminta user untuk │
│ latitude, longitude,      │  │ mengirimkan koordinat     │
│ dan radius                │  │ lokasi                    │
└─────────────┬─────────────┘  └───────────────────────────┘
              │
              ▼
┌───────────────────────────────────────────────────────────┐
│ Sistem menghitung jarak menggunakan formula Haversine:    │
│                                                           │
│ a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlon/2)  │
│ c = 2 × atan2(√a, √(1-a))                                │
│ d = R × c                                                 │
│                                                           │
│ dimana R = 6371 km (radius bumi)                         │
└───────────────────────────┬───────────────────────────────┘
                            │
                            ▼
              ┌───────────────────────────────┐
              │ Sistem mengurutkan bengkel    │
              │ berdasarkan jarak terdekat    │
              └───────────────┬───────────────┘
                              │
                              ▼
                      ◇───────────────◇
                     ╱                 ╲
                    ╱  Bengkel ditemukan ╲
                    ╲  dalam radius?     ╱
                     ╲                 ╱
                      ◇───────┬───────◇
                Ya ───┘       └─── Tidak
                │                    │
                ▼                    ▼
┌─────────────────────────┐  ┌─────────────────────────────┐
│ Sistem menampilkan      │  │ Sistem menampilkan pesan    │
│ daftar bengkel dengan   │  │ "Tidak ada bengkel dalam    │
│ informasi jarak         │  │ radius X km"                │
└───────────┬─────────────┘  └─────────────────────────────┘
            │
            ▼
      ┌───────────┐
      │    END    │
      └───────────┘
```

---

## 6. Alur Pengguna (User Flow)

### 6.1 Menu Utama

Menu utama merupakan titik awal interaksi pengguna dengan chatbot. Pengguna dapat mengakses menu utama dengan mengirimkan pesan kosong atau mengetikkan kata kunci seperti "menu", "home", atau "start".

**Trigger:**
- Pesan kosong
- Kata kunci: `menu`, `home`, `start`, `mulai`
- Payload: `menu`

**Response:**
Sistem menampilkan pesan sapaan yang dipersonalisasi dengan nama pengguna, disertai 7 pilihan quick reply untuk mengakses fitur-fitur chatbot.

| No | Pilihan Menu | Payload | Deskripsi Fungsi |
|----|--------------|---------|------------------|
| 1 | Status Pesanan | `status_prompt` | Melihat status transaksi aktif |
| 2 | Booking Bengkel | `booking_prompt` | Memulai proses booking bengkel |
| 3 | Cari Produk | `product_search_prompt` | Mencari produk spare part |
| 4 | Bengkel Terdekat | `nearby_prompt` | Mencari bengkel berdasarkan lokasi |
| 5 | Booking Saya | `booking_history_prompt` | Melihat riwayat booking |
| 6 | Item Belum Dirating | `rate_list` | Memberikan rating untuk item yang dibeli |
| 7 | FAQ / Bantuan | `faq_prompt` | Melihat daftar FAQ |

### 6.2 Flow Booking Bengkel

Flow booking bengkel merupakan flow paling kompleks dengan 12 langkah sequential. Setiap langkah memiliki validasi dan penanganan error yang spesifik.

#### 6.2.1 Langkah-langkah Booking

| Step | Nama Step | Deskripsi | Tipe Input | Validasi |
|------|-----------|-----------|------------|----------|
| 1 | INIT | Menampilkan pilihan cara pencarian bengkel | Quick Reply | - |
| 2 | SELECT_BENGKEL | Menampilkan daftar bengkel | Card Selection | Bengkel harus ada |
| 3 | CONFIRM_BENGKEL | Konfirmasi bengkel yang dipilih | Quick Reply | - |
| 4 | SELECT_DATE | Memilih tanggal booking | Quick Reply (7 hari) | Tanggal tidak boleh di masa lalu |
| 5 | SELECT_TIME | Memilih slot waktu | Time Picker | Slot harus tersedia, bengkel harus buka |
| 6 | VEHICLE_BRAND | Memilih merk kendaraan | Quick Reply | Harus dari daftar merk valid |
| 7 | VEHICLE_MODEL | Memasukkan model kendaraan | Text Input | Tidak boleh kosong |
| 8 | VEHICLE_PLAT | Memasukkan plat nomor | Text Input | Tidak boleh kosong |
| 9 | VEHICLE_YEAR | Memilih tahun kendaraan | Quick Reply | Range: 2000 - tahun saat ini |
| 10 | VEHICLE_KM | Memasukkan kilometer | Text Input | Harus berupa angka |
| 11 | VEHICLE_TRANSMISI | Memilih jenis transmisi | Quick Reply | Manual atau Automatic |
| 12 | NOTES | Memasukkan catatan (opsional) | Text Input / Skip | - |
| 13 | CONFIRM | Konfirmasi akhir | Quick Reply | - |
| 14 | SUCCESS | Menampilkan hasil booking | - | - |

#### 6.2.2 Visualisasi Flow

```
[START]
    │
    ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 1: INIT                                                    │
│ "Mari kita booking bengkel! Pilih cara mencari:"                │
│ [Terdekat] [Cari Nama] [Dari Daftar] [Batal]                   │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 2: SELECT BENGKEL                                          │
│ Menampilkan daftar bengkel dalam bentuk card                    │
│ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐             │
│ │ Bengkel A    │ │ Bengkel B    │ │ Bengkel C    │             │
│ │ Jl. xxx      │ │ Jl. yyy      │ │ Jl. zzz      │             │
│ │ [Pilih]      │ │ [Pilih]      │ │ [Pilih]      │             │
│ └──────────────┘ └──────────────┘ └──────────────┘             │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 3: CONFIRM BENGKEL                                         │
│ Menampilkan detail bengkel: alamat, telepon, layanan            │
│ [Ya, Lanjutkan] [Ganti Bengkel] [Batal]                        │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 4: SELECT DATE                                             │
│ "Pilih tanggal booking:"                                        │
│ [Hari Ini] [Besok] [Sen, 27] [Sel, 28] [Rab, 29] ...           │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 5: SELECT TIME                                             │
│ "Pilih jam booking untuk [Tanggal]:"                            │
│ ┌───────────────────────────────────────────────────────────┐  │
│ │ TIME PICKER                                                │  │
│ │ [08:00] [09:00] [10:00] [11:00✗] [14:00] [15:00]          │  │
│ │  ✓ = tersedia   ✗ = terisi                                │  │
│ └───────────────────────────────────────────────────────────┘  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 6-11: DATA KENDARAAN                                       │
│                                                                 │
│ Step 6: Merk      → [Honda] [Toyota] [Suzuki] [Yamaha] ...     │
│ Step 7: Model     → Input teks: "Vario 150"                    │
│ Step 8: Plat      → Input teks: "B 1234 ABC"                   │
│ Step 9: Tahun     → [2024] [2023] [2022] [2021] ...            │
│ Step 10: KM       → Input teks: "15000"                        │
│ Step 11: Transmisi → [Manual] [Automatic]                       │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 12: NOTES (Opsional)                                       │
│ "Ada catatan tambahan untuk bengkel?"                           │
│ Input teks atau [Skip] [Batal]                                  │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 13: CONFIRMATION                                           │
│ ╔═══════════════════════════════════════════════════════════╗  │
│ ║              RINGKASAN BOOKING                             ║  │
│ ╠═══════════════════════════════════════════════════════════╣  │
│ ║ Bengkel   : Bengkel Jaya Motor                            ║  │
│ ║ Alamat    : Jl. Sudirman No. 123                          ║  │
│ ║ Tanggal   : Senin, 27 Januari 2025                        ║  │
│ ║ Jam       : 08:00                                          ║  │
│ ║ ─────────────────────────────────────────────────         ║  │
│ ║ Kendaraan : Honda Vario 150                               ║  │
│ ║ Plat      : B 1234 ABC                                    ║  │
│ ║ Tahun     : 2022                                          ║  │
│ ║ KM        : 15.000 km                                     ║  │
│ ║ Transmisi : Manual                                         ║  │
│ ║ Catatan   : Tolong cek rem juga                           ║  │
│ ╚═══════════════════════════════════════════════════════════╝  │
│ [Konfirmasi Booking] [Edit Data] [Batal]                       │
└───────────────────────────────┬─────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│ STEP 14: SUCCESS                                                │
│ "Booking berhasil dibuat!"                                      │
│ ┌───────────────────────────────────────────────────────────┐  │
│ │ [BOOKING CARD]                                             │  │
│ │ Bengkel Jaya Motor                                         │  │
│ │ Senin, 27 Januari 2025 • 08:00                            │  │
│ │ Honda Vario 150 • B 1234 ABC                              │  │
│ │ Status: Pending                                            │  │
│ └───────────────────────────────────────────────────────────┘  │
│ [Lihat Booking Saya] [Menu Utama]                              │
└─────────────────────────────────────────────────────────────────┘
    │
    ▼
[END]
```

### 6.3 Flow Pencarian Produk

**Trigger:**
- Kata kunci: `cari [keyword]`, `produk [nama]`, `beli [item]`
- Payload: `product_search_prompt`

**Alur:**
1. User mengetikkan kata kunci pencarian
2. Sistem mencari produk yang sesuai di database
3. Sistem menampilkan hasil dalam bentuk product card
4. User dapat menambahkan produk ke keranjang

**Validasi Keranjang:**
- Stok produk harus tersedia
- Tidak dapat menambahkan produk dari bengkel yang berbeda dalam satu keranjang
- Kuantitas tidak boleh melebihi stok

### 6.4 Flow Status Pesanan

**Trigger:**
- Kata kunci: `status`, `pesanan`, `tracking`
- Format langsung: `status TRANS-[kode]`
- Payload: `status_prompt`

**Alur:**
1. User meminta status pesanan
2. Sistem menampilkan daftar transaksi aktif
3. User memilih transaksi untuk melihat detail
4. Sistem menampilkan detail status pembayaran dan pengiriman

**Status yang Ditampilkan:**

| Indikator | Status | Deskripsi |
|-----------|--------|-----------|
| Pending | Menunggu | Transaksi menunggu pembayaran/konfirmasi |
| Paid | Dibayar | Pembayaran telah diterima |
| Processing | Diproses | Pesanan sedang diproses |
| Completed | Selesai | Pesanan telah selesai |
| Cancelled | Dibatalkan | Pesanan dibatalkan |

### 6.5 Flow Bengkel Terdekat

**Trigger:**
- Kata kunci: `bengkel terdekat`, `nearby`, `sekitar`
- Dengan koordinat: `bengkel terdekat -6.2088,106.8456`
- Payload: `nearby_prompt` dengan parameter `latitude`, `longitude`, `radius`

**Parameter Input:**

| Parameter | Tipe | Default | Keterangan |
|-----------|------|---------|------------|
| latitude | float | - | Latitude lokasi pengguna |
| longitude | float | - | Longitude lokasi pengguna |
| radius | float | 10 | Radius pencarian dalam kilometer |

**Alur:**
1. User meminta bengkel terdekat
2. Jika koordinat tidak tersedia, sistem meminta lokasi
3. Sistem menghitung jarak menggunakan formula Haversine
4. Sistem menampilkan daftar bengkel yang diurutkan berdasarkan jarak

### 6.6 Flow Riwayat Booking

**Trigger:**
- Kata kunci: `booking saya`, `riwayat booking`, `reservasi saya`
- Payload: `booking_history_prompt`

**Alur:**
1. User meminta riwayat booking
2. Sistem menampilkan 5 booking terakhir dalam bentuk booking card
3. User dapat melihat detail atau membatalkan booking (jika status Pending)

**Aksi Berdasarkan Status:**

| Status | Aksi yang Tersedia |
|--------|-------------------|
| Pending | Lihat Detail, Batalkan |
| Diterima | Lihat Detail |
| Dikerjakan | Lihat Detail |
| Selesai | Lihat Detail |
| Ditolak | Lihat Detail |

### 6.7 Flow Rating/Ulasan

**Trigger:**
- Kata kunci: `rating`, `ulasan`, `review`
- Payload: `rate_list`

**Format Rating:**
```
rate [detail_transaction_id] [bintang 1-5] "komentar opsional"
```

**Contoh:**
- `rate 123 5 "Produk sangat bagus!"`
- `rate 123 4`

**Alur:**
1. User meminta daftar item yang belum dirating
2. Sistem menampilkan item dari transaksi yang sudah selesai
3. User memilih item untuk dirating
4. User memberikan rating (1-5 bintang) dan komentar opsional
5. Sistem menyimpan rating ke database

### 6.8 Flow FAQ

**Trigger:**
- Kata kunci: `faq`, `bantuan`, `help`, `cara`
- Payload: `faq_prompt`

**Daftar FAQ:**

| Topik | Keywords | Isi Jawaban |
|-------|----------|-------------|
| Cara Booking | cara booking, bagaimana booking | Langkah-langkah booking bengkel |
| Cara Bayar | cara bayar, pembayaran | Metode pembayaran yang tersedia |
| Cara Cancel | cara cancel, batalkan | Cara membatalkan pesanan/booking |
| Jam Operasional | jam operasional, jam buka | Informasi jam operasional |
| Ongkir | ongkir, biaya kirim | Informasi biaya pengiriman |
| Cara Daftar | cara daftar, registrasi | Cara mendaftar akun baru |
| Cara Rating | cara rating, cara ulas | Cara memberikan rating |

### 6.9 Pembatalan Flow

Pengguna dapat membatalkan flow yang sedang aktif kapan saja dengan mengirimkan pesan pembatalan.

**Trigger Pembatalan:**
- Kata kunci: `batal`, `cancel`, `batalkan`, `keluar`, `menu`
- Payload: `cancel`

**Perilaku:**
- Sistem akan menghapus state flow yang aktif
- Pengguna akan dikembalikan ke menu utama
- Data yang sudah diinputkan tidak akan disimpan

---

## 7. Algoritma Intent Recognition

### 7.1 Hierarki Pencocokan Intent

Sistem menggunakan pendekatan multi-layer untuk mengenali intent pengguna. Pencocokan dilakukan secara berurutan dengan prioritas sebagai berikut:

```
┌─────────────────────────────────────────────────────────────────┐
│                    PRIORITAS PENCOCOKAN INTENT                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ┌───────────────────────────────────────────────────────┐     │
│   │ LEVEL 1: PAYLOAD MATCHING (Tertinggi)                 │     │
│   │ - Direct match dari payload quick reply button        │     │
│   │ - Contoh: payload "booking_prompt" → intent booking   │     │
│   └───────────────────────────────────────────────────────┘     │
│                            │                                     │
│                            ▼                                     │
│   ┌───────────────────────────────────────────────────────┐     │
│   │ LEVEL 2: PATTERN MATCHING                             │     │
│   │ - Regex pattern untuk format spesifik                 │     │
│   │ - Contoh: "status TRANS-xxx" → intent status_specific │     │
│   │ - Contoh: "rate 123 5" → intent rate_submit           │     │
│   └───────────────────────────────────────────────────────┘     │
│                            │                                     │
│                            ▼                                     │
│   ┌───────────────────────────────────────────────────────┐     │
│   │ LEVEL 3: KEYWORD MATCHING                             │     │
│   │ - Pencocokan kata kunci dengan synonym mapping        │     │
│   │ - Mendukung Bahasa Indonesia dan Inggris              │     │
│   │ - Contoh: "pesan", "reservasi", "book" → booking      │     │
│   └───────────────────────────────────────────────────────┘     │
│                            │                                     │
│                            ▼                                     │
│   ┌───────────────────────────────────────────────────────┐     │
│   │ LEVEL 4: FUZZY MATCHING (Terendah)                    │     │
│   │ - Menggunakan algoritma Levenshtein Distance          │     │
│   │ - Threshold similarity: 70%                           │     │
│   │ - Contoh: "boking" → booking (similarity > 70%)       │     │
│   └───────────────────────────────────────────────────────┘     │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 7.2 Daftar Intent

| Intent | Deskripsi | Handler |
|--------|-----------|---------|
| `menu` | Menampilkan menu utama | MenuHandler |
| `faq` | Menampilkan daftar FAQ | FaqHandler |
| `faq_specific` | Menampilkan jawaban FAQ spesifik | FaqHandler |
| `booking` | Memulai flow booking | BookingFlowHandler |
| `product_search` | Mencari produk | ProductSearchHandler |
| `booking_history` | Melihat riwayat booking | BookingHistoryHandler |
| `status` | Melihat status transaksi | StatusHandler |
| `status_specific` | Melihat status transaksi spesifik | StatusHandler |
| `rating` | Melihat item untuk dirating | RatingHandler |
| `rate_submit` | Submit rating | RatingHandler |
| `nearby` | Mencari bengkel terdekat | NearbyBengkelHandler |
| `nearby_with_coords` | Pencarian dengan koordinat | NearbyBengkelHandler |
| `cancel` | Membatalkan flow aktif | ChatService |

### 7.3 Pemetaan Sinonim (Synonym Mapping)

| Intent | Keywords (Indonesia) | Keywords (English) |
|--------|---------------------|-------------------|
| booking | pesan, reservasi, jadwal, servis, book | book, reserve, schedule |
| product | produk, spare part, barang, item | product, part, item |
| search | cari, temukan, lihat | find, search, look |
| cancel | batal, batalkan, keluar, stop | cancel, stop, exit |
| help | bantuan, tolong, cara, bagaimana | help, how, guide |
| nearby | dekat, terdekat, sekitar | near, nearby, around |
| status | status, pesanan, order, tracking | status, order, track |
| rating | rating, ulasan, review, nilai | rate, review |

### 7.4 Algoritma Fuzzy Matching

Sistem menggunakan algoritma Levenshtein Distance untuk menghitung kemiripan antara input pengguna dengan kata kunci yang terdaftar.

**Formula Similarity:**
```
similarity = 1 - (levenshtein_distance(str1, str2) / max(len(str1), len(str2)))
```

**Threshold:** 70% (0.7)

**Contoh Pencocokan:**
- Input: "boking" → Target: "booking" → Distance: 1 → Similarity: 85.7% → Match
- Input: "bokking" → Target: "booking" → Distance: 1 → Similarity: 85.7% → Match
- Input: "produk" → Target: "product" → Distance: 2 → Similarity: 71.4% → Match

---

## 8. Manajemen State dan Context

### 8.1 Penyimpanan Context

Sistem menggunakan Laravel Cache untuk menyimpan state percakapan dengan konfigurasi:

| Parameter | Nilai | Keterangan |
|-----------|-------|------------|
| Storage | Laravel Cache | Driver cache yang dikonfigurasi |
| TTL (Time-to-Live) | 30 menit | Durasi context tersimpan |
| Key Pattern | `chat_context_{user_id}` | Format key penyimpanan |

### 8.2 Struktur Data Context

```json
{
  "flow": "booking",
  "step": "select_time",
  "data": {
    "bengkel_id": 1,
    "bengkel_name": "Bengkel Jaya Motor",
    "date": "2025-01-27",
    "time": null,
    "brand": null,
    "model": null,
    "plat": null,
    "year": null,
    "km": null,
    "transmisi": null,
    "notes": null
  },
  "search": {
    "last_keyword": "oli mesin",
    "last_results": [1, 2, 3]
  }
}
```

### 8.3 State Transition Diagram

```
                         ┌────────────────────┐
                         │     IDLE STATE     │
                         │   (flow = null)    │
                         └──────────┬─────────┘
                                    │
                                    │ User trigger intent
                                    │ (booking/search/etc)
                                    ▼
                         ┌────────────────────┐
                 ┌───────│   ACTIVE FLOW      │───────┐
                 │       │  (flow = "name")   │       │
                 │       │  (step = "step1")  │       │
                 │       └──────────┬─────────┘       │
                 │                  │                 │
      User input │                  │ Valid input    │ Invalid input
      "cancel"   │                  │                │
                 │                  ▼                │
                 │       ┌────────────────────┐      │
                 │       │   NEXT STEP        │──────┤
                 │       │  (step = "step2")  │      │
                 │       └──────────┬─────────┘      │
                 │                  │                │
                 │                  │ ...            │
                 │                  ▼                │
                 │       ┌────────────────────┐      │
                 │       │   FINAL STEP       │      │
                 │       │  (step = "confirm")│      │
                 │       └──────────┬─────────┘      │
                 │                  │                │
                 │                  │ Confirm        │
                 │                  ▼                │
                 │       ┌────────────────────┐      │
                 │       │     SUCCESS        │      │
                 │       │   (flow cleared)   │      │
                 │       └──────────┬─────────┘      │
                 │                  │                │
                 ▼                  ▼                │
              ┌─────────────────────────────────────┐│
              │          IDLE STATE                 ││
              │         (flow = null)               │◄┘
              └─────────────────────────────────────┘
```

---

## 9. Spesifikasi Komponen Response

### 9.1 Struktur Response API

```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "ok"
  },
  "data": {
    "messages": [...],
    "quick_replies": [...],
    "context_id": "string",
    "end": false,
    "flow": {
      "active": true,
      "name": "booking",
      "step": "select_date",
      "can_cancel": true
    }
  }
}
```

### 9.2 Tipe-tipe Message

| Tipe | Deskripsi | Penggunaan |
|------|-----------|------------|
| `text` | Pesan teks biasa | Greeting, instruksi, pesan error |
| `card` | Kartu dengan title, subtitle, image, dan actions | Informasi bengkel |
| `product_card` | Kartu khusus produk dengan harga dan stok | Hasil pencarian produk |
| `booking_card` | Kartu khusus booking dengan info kendaraan | Riwayat dan detail booking |
| `carousel` | Kumpulan card yang dapat di-scroll horizontal | Multiple items |
| `time_picker` | Komponen pemilih waktu | Pemilihan slot waktu booking |
| `link` | Link yang dapat diklik | Navigasi ke halaman web |

### 9.3 Struktur Quick Reply

```json
{
  "title": "Label Tombol",
  "payload": "payload_value",
  "type": "payload"
}
```

| Field | Tipe | Deskripsi |
|-------|------|-----------|
| title | string | Teks yang ditampilkan pada tombol |
| payload | string | Nilai yang dikirim ke server saat tombol ditekan |
| type | string | Tipe: "payload" (kirim sebagai payload) atau "message" (kirim sebagai message) |

---

## Lampiran

### A. Daftar Pesan Error

| Kode | Pesan | Kondisi |
|------|-------|---------|
| E001 | "Bengkel tidak ditemukan" | Bengkel yang dipilih tidak ada di database |
| E002 | "Bengkel tutup pada hari tersebut" | Bengkel tidak beroperasi pada hari yang dipilih |
| E003 | "Slot waktu sudah terisi" | Slot waktu yang dipilih sudah dibooking |
| E004 | "Tanggal tidak valid" | Tanggal yang dipilih sudah lewat |
| E005 | "Stok produk tidak tersedia" | Stok produk habis |
| E006 | "Tidak dapat menambahkan produk dari bengkel berbeda" | Keranjang sudah berisi produk dari bengkel lain |
| E007 | "Transaksi tidak ditemukan" | Kode transaksi tidak valid |
| E008 | "Rating sudah diberikan" | Item sudah pernah dirating |

### B. Konfigurasi Sistem

| Parameter | Nilai | Keterangan |
|-----------|-------|------------|
| Max Booking Days Ahead | 30 hari | Maksimal booking ke depan |
| Context TTL | 30 menit | Durasi penyimpanan context |
| Fuzzy Match Threshold | 70% | Threshold similarity untuk fuzzy matching |
| Default Search Radius | 10 km | Radius default pencarian bengkel |
| Earth Radius (Haversine) | 6371 km | Konstanta radius bumi |

---

*Dokumen ini merupakan bagian dari dokumentasi teknis Aplikasi Bengkelin untuk keperluan penulisan skripsi.*
