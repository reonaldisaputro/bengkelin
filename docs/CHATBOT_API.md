# Bengkelin Chatbot API Documentation

## Overview

Chatbot API untuk aplikasi Bengkelin - platform booking bengkel dan marketplace spare part. Chatbot mendukung multi-turn conversations, natural language understanding, dan rich responses.

---

## Base URL

```
https://bengkelin.pkumionline.cloud/api
```

## Authentication

Semua request ke chatbot memerlukan authentication menggunakan **Laravel Sanctum Bearer Token**.

```http
Authorization: Bearer {your_token}
```

---

## Endpoint

### Send Message

Mengirim pesan ke chatbot dan menerima response.

```http
POST /api/chat/send
```

#### Request Headers

| Header | Value | Required |
|--------|-------|----------|
| Authorization | Bearer {token} | Yes |
| Content-Type | application/json | Yes |
| Accept | application/json | Yes |

#### Request Body

```json
{
  "message": "string",
  "payload": "string",
  "latitude": 0.0,
  "longitude": 0.0,
  "radius": 10
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| message | string | No | Pesan teks dari user |
| payload | string | No | Payload dari quick reply button |
| latitude | float | No | Latitude untuk pencarian bengkel terdekat |
| longitude | float | No | Longitude untuk pencarian bengkel terdekat |
| radius | float | No | Radius pencarian dalam km (default: 10) |

> **Note:**
> - Minimal salah satu dari `message` atau `payload` harus diisi. Jika keduanya kosong, chatbot akan menampilkan menu utama.
> - `latitude` dan `longitude` adalah **optional parameters** khusus untuk fitur **Bengkel Terdekat**. Jika disertakan, chatbot akan langsung mencari bengkel dalam radius tertentu tanpa perlu parsing dari text.

#### Response

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

---

## Response Structure

### Main Response Fields

| Field | Type | Description |
|-------|------|-------------|
| messages | array | Array of message objects to display |
| quick_replies | array | Array of quick reply buttons |
| context_id | string | User context ID (untuk tracking) |
| end | boolean | Apakah conversation selesai |
| flow | object\|null | Info flow aktif (jika ada multi-turn conversation) |

### Flow Object

Muncul ketika user sedang dalam multi-turn conversation (seperti booking flow).

| Field | Type | Description |
|-------|------|-------------|
| active | boolean | Flow sedang aktif |
| name | string | Nama flow (booking, etc.) |
| step | string | Step saat ini dalam flow |
| can_cancel | boolean | User bisa cancel flow |

---

## Message Types

### 1. Text Message

Pesan teks biasa.

```json
{
  "type": "text",
  "text": "Hai John! Ada yang bisa saya bantu?"
}
```

**Flutter Implementation:**
```dart
if (message['type'] == 'text') {
  return Text(message['text']);
}
```

---

### 2. Card Message

Kartu dengan title, subtitle, image (optional), dan actions.

```json
{
  "type": "card",
  "id": 1,
  "title": "Bengkel Jaya Motor",
  "subtitle": "Jl. Sudirman No. 123 • ±2.5 km",
  "image": "https://example.com/image.jpg",
  "actions": [
    {"label": "Pilih", "payload": "select_bengkel_1"},
    {"label": "Detail", "url": "https://bengkelin.com/bengkel/1"}
  ]
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| type | string | Yes | "card" |
| id | int | No | ID item |
| title | string | Yes | Judul kartu |
| subtitle | string | No | Subtitle/deskripsi |
| image | string | No | URL gambar |
| actions | array | No | Array tombol aksi |

**Action Object:**

| Field | Type | Description |
|-------|------|-------------|
| label | string | Text tombol |
| payload | string | Payload untuk dikirim ke chatbot (internal action) |
| url | string | URL untuk dibuka (external link) |

---

### 3. Link Message

Link yang bisa diklik.

```json
{
  "type": "link",
  "title": "Lihat Detail Lengkap",
  "url": "https://bengkelin.com/transaction/123"
}
```

---

### 4. Carousel Message

Horizontal scrollable cards.

```json
{
  "type": "carousel",
  "items": [
    {
      "type": "card",
      "title": "Bengkel A",
      "subtitle": "...",
      "actions": [...]
    },
    {
      "type": "card",
      "title": "Bengkel B",
      "subtitle": "...",
      "actions": [...]
    }
  ]
}
```

**Flutter Implementation:**
```dart
if (message['type'] == 'carousel') {
  return SizedBox(
    height: 200,
    child: ListView.builder(
      scrollDirection: Axis.horizontal,
      itemCount: message['items'].length,
      itemBuilder: (context, index) {
        return buildCard(message['items'][index]);
      },
    ),
  );
}
```

---

### 5. Product Card

Kartu khusus untuk produk dengan harga dan stock.

```json
{
  "type": "product_card",
  "id": 1,
  "name": "Oli Mesin Premium 1L",
  "price": 75000,
  "price_formatted": "Rp 75.000",
  "bengkel": "Bengkel Jaya Motor",
  "stock": 10,
  "image": "https://example.com/product.jpg",
  "actions": [
    {"label": "Tambah ke Keranjang", "payload": "add_to_cart_1"},
    {"label": "Detail", "url": "https://bengkelin.com/product/1"}
  ]
}
```

| Field | Type | Description |
|-------|------|-------------|
| id | int | Product ID |
| name | string | Nama produk |
| price | int | Harga dalam rupiah (number) |
| price_formatted | string | Harga formatted (display) |
| bengkel | string | Nama bengkel penjual |
| stock | int | Jumlah stock tersedia |
| image | string | URL gambar produk |
| actions | array | Tombol aksi |

---

### 6. Booking Card

Kartu khusus untuk informasi booking.

```json
{
  "type": "booking_card",
  "id": 123,
  "bengkel": {
    "name": "Bengkel Jaya Motor",
    "image": "https://example.com/bengkel.jpg"
  },
  "tanggal": "Senin, 27 Januari 2025",
  "waktu": "10:00",
  "vehicle": {
    "brand": "Honda",
    "model": "Vario 150",
    "plat": "B 1234 ABC"
  },
  "status": "🟡 Pending",
  "actions": [
    {"label": "Lihat Detail", "url": "https://bengkelin.com/booking/123"},
    {"label": "Batalkan", "payload": "cancel_booking_123"}
  ]
}
```

| Field | Type | Description |
|-------|------|-------------|
| id | int | Booking ID |
| bengkel | object | Info bengkel (name, image) |
| tanggal | string | Tanggal booking (formatted) |
| waktu | string | Jam booking (HH:mm) |
| vehicle | object | Info kendaraan (brand, model, plat) |
| status | string | Status dengan emoji |
| actions | array | Tombol aksi |

---

### 7. Time Picker

Komponen untuk memilih waktu booking.

```json
{
  "type": "time_picker",
  "date": "2025-01-27",
  "available_slots": ["08:00", "09:00", "10:00", "14:00", "15:00", "16:00"],
  "booked_slots": ["11:00", "12:00", "13:00"]
}
```

| Field | Type | Description |
|-------|------|-------------|
| date | string | Tanggal (YYYY-MM-DD) |
| available_slots | array | Slot waktu yang tersedia |
| booked_slots | array | Slot waktu yang sudah dibooking |

**Flutter Implementation:**
```dart
if (message['type'] == 'time_picker') {
  return Wrap(
    children: (message['available_slots'] as List).map((slot) {
      bool isBooked = message['booked_slots'].contains(slot);
      return ChoiceChip(
        label: Text(slot),
        selected: false,
        onSelected: isBooked ? null : (_) => sendPayload('select_time_$slot'),
      );
    }).toList(),
  );
}
```

---

## Quick Reply Types

Quick replies adalah tombol aksi yang ditampilkan di bawah messages.

### Structure

```json
{
  "title": "Menu Utama",
  "payload": "menu",
  "type": "payload"
}
```

| Field | Type | Description |
|-------|------|-------------|
| title | string | Text yang ditampilkan di tombol |
| payload | string | Value yang dikirim ke API |
| type | string | "payload" atau "message" |

### Types

| Type | Behavior |
|------|----------|
| payload | Kirim value `payload` ke field `payload` di request |
| message | Kirim value `payload` atau `message` ke field `message` di request |

**Flutter Implementation:**
```dart
Widget buildQuickReplies(List<dynamic> replies) {
  return Wrap(
    spacing: 8,
    children: replies.map((reply) {
      return ElevatedButton(
        onPressed: () {
          if (reply['type'] == 'message') {
            sendMessage(message: reply['message'] ?? reply['payload']);
          } else {
            sendMessage(payload: reply['payload']);
          }
        },
        child: Text(reply['title']),
      );
    }).toList(),
  );
}
```

---

## Features & Flows

### 1. Menu Utama

Menampilkan menu utama chatbot.

**Trigger:**
```json
{ "payload": "menu" }
// atau
{ "message": "", "payload": "" }
```

**Quick Replies yang tersedia:**
- Status Pesanan
- Booking Bengkel
- Cari Produk
- Bengkel Terdekat
- Booking Saya
- Item Belum Dirating
- FAQ / Bantuan

---

### 2. FAQ (Frequently Asked Questions)

**Trigger:**
```json
{ "payload": "faq_prompt" }
// atau
{ "message": "bantuan" }
{ "message": "cara booking" }
```

**FAQ Topics:**
| Topic | Keywords |
|-------|----------|
| Cara Booking | cara booking, bagaimana booking |
| Cara Bayar | cara bayar, pembayaran |
| Cara Cancel | cara cancel, batalkan |
| Jam Operasional | jam operasional, jam buka |
| Ongkir | ongkir, biaya kirim |
| Cara Daftar | cara daftar, registrasi |
| Cara Rating | cara rating, cara ulas |

**Flow:**
```
User: "faq" atau payload faq_prompt
Bot: Menampilkan list FAQ sebagai quick replies
User: Klik salah satu FAQ
Bot: Menampilkan jawaban
```

---

### 3. Status Pesanan

Cek status transaksi/pesanan.

**Trigger:**
```json
{ "payload": "status_prompt" }
// atau
{ "message": "status" }
{ "message": "status TRANS-ABC123" }
```

**Flow:**
```
User: payload status_prompt
Bot: Menampilkan pesanan aktif sebagai quick replies
User: Klik kode transaksi
Bot: Menampilkan detail status (pembayaran, pengiriman, items, total)
```

**Direct Query:**
```json
{ "message": "status TRANS-ABC123" }
```

---

### 4. Booking Bengkel (Multi-turn Flow)

Flow lengkap untuk booking bengkel via chat.

**Trigger:**
```json
{ "payload": "booking_prompt" }
// atau
{ "message": "booking" }
{ "message": "pesan bengkel" }
{ "message": "reservasi" }
```

**Complete Flow:**

```
Step 1: Init
Request:  { "message": "booking" }
Response: Pilih cara cari bengkel
Quick Replies: [Bengkel Terdekat, Cari Nama Bengkel, Pilih dari Daftar, Batal]

Step 2: Select Bengkel
Request:  { "payload": "booking_list" }
Response: List bengkel dengan cards
User klik: { "payload": "select_bengkel_1" }

Step 3: Confirm Bengkel
Response: Info bengkel + layanan tersedia
Quick Replies: [Ya Lanjutkan, Ganti Bengkel, Batal]
Request:  { "payload": "confirm_bengkel" }

Step 4: Select Date
Response: Pilih tanggal (7 hari ke depan)
Quick Replies: [Hari Ini, Besok, Sen 27 Jan, ...]
Request:  { "payload": "select_date_2025-01-27" }

Step 5: Select Time
Response: Time picker dengan available/booked slots
Quick Replies: [08:00, 09:00, 10:00, ...]
Request:  { "payload": "select_time_10:00" }

Step 6: Vehicle Brand
Response: Pilih merk kendaraan
Quick Replies: [Honda, Toyota, Suzuki, Yamaha, ...]
Request:  { "payload": "select_brand_Honda" }

Step 7: Vehicle Model
Response: Ketik model kendaraan
Request:  { "message": "Vario 150" }

Step 8: Vehicle Plat
Response: Ketik plat nomor
Request:  { "message": "B 1234 ABC" }

Step 9: Vehicle Year
Response: Pilih tahun pembuatan
Quick Replies: [2024, 2023, 2022, ...]
Request:  { "payload": "select_year_2022" }

Step 10: Vehicle KM
Response: Ketik kilometer
Request:  { "message": "15000" }

Step 11: Vehicle Transmisi
Response: Pilih transmisi
Quick Replies: [Manual, Automatic]
Request:  { "payload": "select_transmisi_automatic" }

Step 12: Notes (Optional)
Response: Ada catatan tambahan?
Quick Replies: [Skip, Batal]
Request:  { "message": "Tolong cek rem juga" }
// atau
Request:  { "payload": "skip_notes" }

Step 13: Confirmation
Response: Ringkasan booking lengkap
Quick Replies: [Konfirmasi Booking, Edit Data, Batal]
Request:  { "payload": "confirm_booking" }

Step 14: Success
Response: Booking berhasil! + Booking Card
Quick Replies: [Lihat Booking Saya, Menu Utama]
```

**Cancel Flow:**
Kapan saja selama flow, user bisa cancel:
```json
{ "payload": "cancel" }
// atau
{ "message": "batal" }
{ "message": "menu" }
```

---

### 5. Cari Produk

Pencarian produk spare part.

**Trigger:**
```json
{ "payload": "product_search_prompt" }
// atau
{ "message": "cari oli mesin" }
{ "message": "produk ban motor" }
{ "message": "beli aki" }
```

**Response:**
```json
{
  "messages": [
    { "type": "text", "text": "Ditemukan 3 produk untuk \"oli mesin\":" },
    {
      "type": "product_card",
      "id": 1,
      "name": "Oli Mesin Premium",
      "price": 75000,
      "price_formatted": "Rp 75.000",
      "bengkel": "Bengkel Jaya",
      "stock": 10,
      "actions": [
        {"label": "Tambah ke Keranjang", "payload": "add_to_cart_1"},
        {"label": "Detail", "url": "..."}
      ]
    }
  ],
  "quick_replies": [
    {"title": "Cari Lagi", "payload": "product_search_prompt"},
    {"title": "Menu Utama", "payload": "menu"}
  ]
}
```

**Add to Cart:**
```json
{ "payload": "add_to_cart_1" }
```

---

### 6. Bengkel Terdekat

Cari bengkel berdasarkan lokasi.

**Trigger (Ask for location):**
```json
{ "payload": "nearby_prompt" }
// atau
{ "message": "bengkel terdekat" }
```

**Method 1: With Coordinates in Message (Text-based)**
```json
{ "message": "bengkel terdekat -6.2088,106.8456" }
```

**Method 2: With Parameters (Recommended for Flutter)** ⭐
```json
{
  "payload": "nearby_prompt",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "radius": 5
}
```

> **Keuntungan Method 2:**
> - Lebih mudah untuk Flutter developer (langsung kirim dari GPS)
> - Tidak perlu parse text
> - Bisa custom radius pencarian (default: 10 km)
> - Sama seperti API `/api/bengkel/nearby`

**Response:**
Cards dengan bengkel terdekat + jarak dalam km, sorted by distance.

---

### 7. Riwayat Booking

Lihat booking yang sudah dibuat.

**Trigger:**
```json
{ "payload": "booking_history_prompt" }
// atau
{ "message": "booking saya" }
{ "message": "riwayat booking" }
```

**Response:**
Booking cards dengan status:
- 🟡 Pending
- 🟢 Diterima
- 🔵 Dikerjakan
- ✅ Selesai
- 🔴 Ditolak

**Cancel Booking:**
```json
{ "payload": "cancel_booking_123" }
```
> Hanya bisa cancel booking dengan status "Pending"

---

### 8. Rating / Ulasan

Beri rating untuk produk/layanan yang sudah dibeli.

**Trigger:**
```json
{ "payload": "rate_list" }
// atau
{ "message": "rating" }
{ "message": "ulasan" }
```

**Flow:**
```
Step 1: List unrated items
Response: List item yang belum dirating
Quick Replies: [Ulas: Oli Mesin..., Ulas: Servis AC..., Menu Utama]

Step 2: Select item
Request:  { "payload": "rate_prompt_123" }
Response: Instruksi format rating
Quick Replies: [⭐5 - Sangat Puas, ⭐4 - Puas, ⭐3 - Cukup, Menu Utama]

Step 3: Submit rating
Request:  { "message": "rate 123 5 \"Sangat memuaskan!\"" }
// atau klik quick reply
Request:  { "message": "rate 123 5 \"Sangat memuaskan!\"" }

Response: Terima kasih! Ulasan tersimpan.
```

**Rating Format:**
```
rate {detail_id} {stars 1-5} "komentar"
```

---

## Natural Language Understanding

Chatbot mendukung berbagai variasi input:

### Synonyms

| Intent | Accepted Words |
|--------|---------------|
| booking | pesan, reservasi, jadwal, book, servis |
| product | produk, spare part, barang, item |
| search | cari, temukan, lihat, find |
| cancel | batal, batalkan, stop, keluar |
| help | bantuan, tolong, cara, bagaimana |

### Fuzzy Matching

Chatbot bisa memahami typo dengan similarity threshold 70%:
- "boking" → booking
- "bokking" → booking
- "produk" → product

### Question Detection

Pertanyaan otomatis diarahkan ke FAQ:
- "cara booking gimana?"
- "bagaimana cara bayar?"
- "apa itu ongkir?"

---

## Error Handling

### Error Response Format

```json
{
  "meta": {
    "code": 500,
    "status": "error",
    "message": "Terjadi kesalahan pada chatbot"
  },
  "data": {
    "error": "Error message details"
  }
}
```

### Common Errors

| Code | Message | Description |
|------|---------|-------------|
| 401 | Unauthorized | Token tidak valid/expired |
| 500 | Terjadi kesalahan | Server error |

### User-Friendly Error Messages

Chatbot menampilkan pesan error yang user-friendly:
- "Bengkel tidak ditemukan. Silakan pilih bengkel lain."
- "Maaf, slot waktu ini sudah terisi. Silakan pilih waktu lain."
- "Bengkel tutup pada hari tersebut. Silakan pilih hari lain."
- "Maaf, produk tidak tersedia. Stok habis."

---

## Flutter Integration Example

### Chat Service

```dart
class ChatService {
  final String baseUrl = 'https://bengkelin.pkumionline.cloud/api';
  final String token;

  ChatService({required this.token});

  Future<ChatResponse> sendMessage({
    String? message,
    String? payload,
    double? latitude,
    double? longitude,
    double? radius,
  }) async {
    final Map<String, dynamic> body = {
      'message': message ?? '',
      'payload': payload ?? '',
    };

    // Add location params if provided (for nearby bengkel feature)
    if (latitude != null) body['latitude'] = latitude;
    if (longitude != null) body['longitude'] = longitude;
    if (radius != null) body['radius'] = radius;

    final response = await http.post(
      Uri.parse('$baseUrl/chat/send'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(body),
    );

    if (response.statusCode == 200) {
      return ChatResponse.fromJson(jsonDecode(response.body));
    } else {
      throw Exception('Failed to send message');
    }
  }
}
```

### Chat Response Model

```dart
class ChatResponse {
  final List<ChatMessage> messages;
  final List<QuickReply> quickReplies;
  final String? contextId;
  final bool end;
  final ChatFlow? flow;

  ChatResponse({
    required this.messages,
    required this.quickReplies,
    this.contextId,
    required this.end,
    this.flow,
  });

  factory ChatResponse.fromJson(Map<String, dynamic> json) {
    final data = json['data'];
    return ChatResponse(
      messages: (data['messages'] as List)
          .map((m) => ChatMessage.fromJson(m))
          .toList(),
      quickReplies: (data['quick_replies'] as List)
          .map((r) => QuickReply.fromJson(r))
          .toList(),
      contextId: data['context_id'],
      end: data['end'] ?? false,
      flow: data['flow'] != null ? ChatFlow.fromJson(data['flow']) : null,
    );
  }
}

class ChatMessage {
  final String type;
  final Map<String, dynamic> data;

  ChatMessage({required this.type, required this.data});

  factory ChatMessage.fromJson(Map<String, dynamic> json) {
    return ChatMessage(
      type: json['type'],
      data: json,
    );
  }
}

class QuickReply {
  final String title;
  final String payload;
  final String type;
  final String? message;

  QuickReply({
    required this.title,
    required this.payload,
    required this.type,
    this.message,
  });

  factory QuickReply.fromJson(Map<String, dynamic> json) {
    return QuickReply(
      title: json['title'],
      payload: json['payload'],
      type: json['type'] ?? 'payload',
      message: json['message'],
    );
  }
}

class ChatFlow {
  final bool active;
  final String name;
  final String step;
  final bool canCancel;

  ChatFlow({
    required this.active,
    required this.name,
    required this.step,
    required this.canCancel,
  });

  factory ChatFlow.fromJson(Map<String, dynamic> json) {
    return ChatFlow(
      active: json['active'],
      name: json['name'],
      step: json['step'],
      canCancel: json['can_cancel'],
    );
  }
}
```

### Message Widget Builder

```dart
Widget buildMessage(ChatMessage message) {
  switch (message.type) {
    case 'text':
      return TextMessageWidget(text: message.data['text']);

    case 'card':
      return CardWidget(
        title: message.data['title'],
        subtitle: message.data['subtitle'],
        image: message.data['image'],
        actions: message.data['actions'],
      );

    case 'carousel':
      return CarouselWidget(items: message.data['items']);

    case 'product_card':
      return ProductCardWidget(
        id: message.data['id'],
        name: message.data['name'],
        price: message.data['price_formatted'],
        bengkel: message.data['bengkel'],
        stock: message.data['stock'],
        image: message.data['image'],
        actions: message.data['actions'],
      );

    case 'booking_card':
      return BookingCardWidget(
        id: message.data['id'],
        bengkel: message.data['bengkel'],
        tanggal: message.data['tanggal'],
        waktu: message.data['waktu'],
        vehicle: message.data['vehicle'],
        status: message.data['status'],
        actions: message.data['actions'],
      );

    case 'time_picker':
      return TimePickerWidget(
        date: message.data['date'],
        availableSlots: message.data['available_slots'],
        bookedSlots: message.data['booked_slots'],
      );

    case 'link':
      return LinkWidget(
        title: message.data['title'],
        url: message.data['url'],
      );

    default:
      return Text('Unknown message type: ${message.type}');
  }
}
```

### Practical Usage Examples

#### Example 1: Nearby Bengkel with GPS Location

```dart
// Get user's current location
Position position = await Geolocator.getCurrentPosition();

// Send to chatbot with location params
final response = await chatService.sendMessage(
  payload: 'nearby_prompt',
  latitude: position.latitude,
  longitude: position.longitude,
  radius: 5, // Search within 5km
);

// Display response
setState(() {
  messages.add(response);
});
```

#### Example 2: Quick Reply Button Click

```dart
// User clicks a quick reply button
void onQuickReplyTap(QuickReply reply) {
  if (reply.type == 'message') {
    sendMessage(message: reply.message ?? reply.payload);
  } else {
    sendMessage(payload: reply.payload);
  }
}
```

#### Example 3: Text Input from User

```dart
// User types and sends a message
void onSendText(String text) async {
  final response = await chatService.sendMessage(
    message: text,
  );

  setState(() {
    messages.add(response);
  });
}
```

#### Example 4: Booking Flow with Cancel Button

```dart
// Show cancel button when in active flow
Widget buildChatInput() {
  return Row(
    children: [
      // Show cancel button if flow is active
      if (currentResponse?.flow?.active == true)
        IconButton(
          icon: Icon(Icons.close),
          onPressed: () => sendMessage(payload: 'cancel'),
        ),

      Expanded(child: TextField(...)),
      IconButton(
        icon: Icon(Icons.send),
        onPressed: () => onSendText(_controller.text),
      ),
    ],
  );
}
```

---

## Testing Checklist

### Basic Features
- [ ] Menu utama tampil dengan semua quick replies
- [ ] FAQ list tampil dan bisa dipilih
- [ ] Status pesanan menampilkan transaksi aktif
- [ ] Cek status dengan kode TRANS-xxx

### Booking Flow
- [ ] Trigger booking dengan "booking" atau payload
- [ ] Pilih bengkel dari list
- [ ] Pilih tanggal (validasi tanggal lampau)
- [ ] Pilih waktu (slot yang terisi disabled)
- [ ] Input semua data kendaraan
- [ ] Konfirmasi dan booking berhasil dibuat
- [ ] Cancel flow di tengah jalan

### Product Search
- [ ] Cari dengan "cari [keyword]"
- [ ] Product cards tampil dengan benar
- [ ] Add to cart berfungsi
- [ ] Handle produk tidak ditemukan

### Booking History
- [ ] List booking tampil
- [ ] Status dengan emoji yang benar
- [ ] Cancel booking (hanya Pending)

### Nearby Bengkel
- [ ] Request dengan payload nearby_prompt tanpa params menampilkan instruksi
- [ ] Request dengan latitude & longitude params menampilkan bengkel terdekat
- [ ] Bengkel sorted by distance dengan tampilan jarak
- [ ] Custom radius berfungsi (default 10km)
- [ ] Handle tidak ada bengkel dalam radius
- [ ] Bengkel cards clickable dengan actions

### Rating
- [ ] List item belum dirating
- [ ] Submit rating dengan format benar
- [ ] Handle item sudah dirating

### Error Handling
- [ ] Handle unauthorized (401)
- [ ] Handle server error (500)
- [ ] Fallback message untuk input tidak dikenali

---

## Changelog

### v2.0.0 (Current)
- Added multi-turn booking flow
- Added product search via chat
- Added booking history
- Added FAQ handler
- Added rich response types (carousel, product_card, booking_card, time_picker)
- Added natural language understanding with fuzzy matching
- Added context management for conversations
- Added **latitude, longitude, radius** parameters for nearby bengkel (direct GPS support)
- Refactored to service-based architecture

### v1.0.0 (Previous)
- Basic menu navigation
- Status pesanan
- Nearby bengkel
- Rating submission

---

## Support

Jika ada pertanyaan atau issue, hubungi:
- Backend Developer: [Your Contact]
- GitHub Issues: https://github.com/your-repo/bengkelin/issues
