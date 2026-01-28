# Bengkel Transaction API Documentation

Base URL: `/api/bengkel`

> **Authentication:** Semua endpoint memerlukan token Bearer (Login sebagai Pemilik Bengkel/Mitra)

---

## Daftar Endpoint

| No | Method | Endpoint | Deskripsi |
|----|--------|----------|-----------|
| 1 | GET | `/bengkel/transactions` | Get all transactions |
| 2 | GET | `/bengkel/transactions/create/{bookingId}` | Get form data untuk membuat transaksi |
| 3 | GET | `/bengkel/transactions/{id}` | Get transaction detail |
| 4 | PUT | `/bengkel/transactions/{id}` | Update shipping status |
| 5 | POST | `/bengkel/cart/add` | Add item to cart |
| 6 | DELETE | `/bengkel/cart/{id}` | Remove item from cart |
| 7 | POST | `/bengkel/checkout` | Process checkout |

---

## 1. Get All Transactions

Mengambil semua transaksi milik bengkel.

**Endpoint:** `GET /api/bengkel/transactions`

**Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Data transaksi berhasil diambil"
  },
  "data": [
    {
      "id": 1,
      "transaction_code": "TRANS-123",
      "user_id": 5,
      "bengkel_id": 2,
      "booking_id": 10,
      "payment_status": "pending",
      "shipping_status": null,
      "ongkir": 0,
      "administrasi": 15000,
      "grand_total": 315000,
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z",
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com",
        "phone_number": "08123456789"
      }
    }
  ]
}
```

---

## 2. Get Form Data (Create Transaction)

Mengambil data yang diperlukan untuk membuat transaksi (products, services, cart).

**Endpoint:** `GET /api/bengkel/transactions/create/{bookingId}`

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| bookingId | integer | Yes | ID booking |

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Data untuk membuat transaksi berhasil diambil"
  },
  "data": {
    "booking": {
      "id": 10,
      "user_id": 5,
      "bengkel_id": 2,
      "booking_date": "2024-01-20",
      "booking_time": "10:00:00",
      "status": "confirmed"
    },
    "products": [
      {
        "id": 1,
        "name": "Oli Mesin",
        "price": 50000,
        "stock": 20
      },
      {
        "id": 2,
        "name": "Filter Udara",
        "price": 75000,
        "stock": 15
      }
    ],
    "services": [
      {
        "id": 1,
        "name": "Ganti Oli",
        "price": 200000
      },
      {
        "id": 2,
        "name": "Tune Up",
        "price": 350000
      }
    ],
    "carts": [
      {
        "id": 1,
        "booking_id": 10,
        "product_id": 1,
        "layanan_id": null,
        "quantity": 2,
        "price": 50000,
        "product": {
          "id": 1,
          "name": "Oli Mesin"
        },
        "layanan": null
      }
    ],
    "total_price": 100000
  }
}
```

**Response Error (404) - Booking tidak ditemukan:**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Booking tidak ditemukan"
  },
  "data": null
}
```

**Response Error (404) - Bengkel tidak ditemukan:**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Bengkel tidak ditemukan"
  },
  "data": null
}
```

---

## 3. Get Transaction Detail

Mengambil detail transaksi berdasarkan ID.

**Endpoint:** `GET /api/bengkel/transactions/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | ID transaksi |

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Detail transaksi berhasil diambil"
  },
  "data": {
    "id": 1,
    "transaction_code": "TRANS-123",
    "user_id": 5,
    "bengkel_id": 2,
    "booking_id": 10,
    "payment_status": "success",
    "shipping_status": "delivered",
    "ongkir": 0,
    "administrasi": 15000,
    "grand_total": 315000,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com",
      "kecamatan": {
        "id": 1,
        "nama": "Coblong"
      },
      "kelurahan": {
        "id": 1,
        "nama": "Dago"
      }
    },
    "detail_transactions": [
      {
        "id": 1,
        "transaction_id": 1,
        "product_id": 3,
        "layanan_id": null,
        "qty": 2,
        "product_price": 50000,
        "layanan_price": null,
        "product": {
          "id": 3,
          "name": "Oli Mesin",
          "price": 50000
        },
        "layanan": null,
        "bengkel": {
          "id": 2,
          "nama_bengkel": "Bengkel Jaya"
        }
      },
      {
        "id": 2,
        "transaction_id": 1,
        "product_id": null,
        "layanan_id": 5,
        "qty": 1,
        "product_price": null,
        "layanan_price": 200000,
        "product": null,
        "layanan": {
          "id": 5,
          "name": "Ganti Oli",
          "price": 200000
        }
      }
    ]
  }
}
```

**Response Error (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Transaksi tidak ditemukan"
  },
  "data": null
}
```

---

## 4. Update Shipping Status

Memperbarui status pengiriman transaksi.

**Endpoint:** `PUT /api/bengkel/transactions/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | ID transaksi |

**Request Body:**
```json
{
  "shipping_status": "delivered"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| shipping_status | string | Yes | Status pengiriman |

**Shipping Status Options:**
- `processing` - Sedang diproses
- `shipped` - Sedang dikirim
- `delivered` - Sudah diterima

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Status pengiriman berhasil diperbarui"
  },
  "data": {
    "id": 1,
    "transaction_code": "TRANS-123",
    "shipping_status": "delivered",
    "payment_status": "success",
    "grand_total": 315000,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T12:00:00.000000Z"
  }
}
```

**Response Error (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Transaksi tidak ditemukan"
  },
  "data": null
}
```

---

## 5. Add Item to Cart

Menambahkan product atau layanan ke keranjang.

**Endpoint:** `POST /api/bengkel/cart/add`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Request Body untuk Product:
```json
{
  "booking_id": 10,
  "product_id": 1,
  "quantity": 2
}
```

### Request Body untuk Layanan/Service:
```json
{
  "booking_id": 10,
  "layanan_id": 5,
  "quantity": 1
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| booking_id | integer | Yes | ID booking |
| product_id | integer | No* | ID product (jika menambahkan product) |
| layanan_id | integer | No* | ID layanan (jika menambahkan layanan) |
| quantity | integer | Yes | Jumlah item (min: 1) |

> **Note:** Salah satu dari `product_id` atau `layanan_id` harus diisi

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Item berhasil ditambahkan ke keranjang"
  },
  "data": null
}
```

**Response Error (409) - Layanan sudah ada:**
```json
{
  "meta": {
    "code": 409,
    "status": "error",
    "message": "Layanan sudah ada di keranjang"
  },
  "data": null
}
```

**Response Error (422) - Validation Error:**
```json
{
  "message": "The booking_id field is required.",
  "errors": {
    "booking_id": ["The booking_id field is required."]
  }
}
```

---

## 6. Remove Item from Cart

Menghapus item dari keranjang.

**Endpoint:** `DELETE /api/bengkel/cart/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | ID cart item |

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Item berhasil dihapus dari keranjang"
  },
  "data": null
}
```

**Response Error (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Item tidak ditemukan di keranjang"
  },
  "data": null
}
```

---

## 7. Checkout

Memproses checkout dan membuat transaksi dengan pembayaran Midtrans.

**Endpoint:** `POST /api/bengkel/checkout`

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:** Tidak ada (menggunakan data cart yang sudah ada)

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Transaksi berhasil dibuat, lanjutkan pembayaran"
  },
  "data": {
    "payment_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/xxxxx"
  }
}
```

> **Note:** Redirect/buka `payment_url` di WebView untuk melakukan pembayaran melalui Midtrans

**Response Error (400) - Cart Kosong:**
```json
{
  "meta": {
    "code": 400,
    "status": "error",
    "message": "Keranjang kosong"
  },
  "data": null
}
```

**Response Error (404) - Bengkel Tidak Ditemukan:**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Bengkel tidak ditemukan"
  },
  "data": null
}
```

---

## Flow Penggunaan

```
┌─────────────────────────────────────────────────────────────────┐
│  1. GET /bengkel/transactions/create/{bookingId}                │
│     → Ambil data form (products, services, current cart)        │
└─────────────────────────┬───────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│  2. POST /bengkel/cart/add                                      │
│     → Tambah item ke cart (bisa dipanggil berkali-kali)         │
└─────────────────────────┬───────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│  3. DELETE /bengkel/cart/{id}  (Opsional)                       │
│     → Hapus item dari cart jika diperlukan                      │
└─────────────────────────┬───────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│  4. POST /bengkel/checkout                                      │
│     → Proses checkout, dapat payment_url                        │
└─────────────────────────┬───────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│  5. Open payment_url di WebView                                 │
│     → User melakukan pembayaran via Midtrans                    │
└─────────────────────────┬───────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│  6. GET /bengkel/transactions                                   │
│     → Lihat daftar semua transaksi                              │
└─────────────────────────┬───────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────────────┐
│  7. PUT /bengkel/transactions/{id}                              │
│     → Update shipping status setelah pembayaran sukses          │
└─────────────────────────────────────────────────────────────────┘
```

---

## Catatan untuk Flutter Developer

1. **Authentication**: Pastikan token Bearer disertakan di setiap request
2. **Payment URL**: Gunakan WebView untuk membuka `payment_url` dari Midtrans
3. **Cart Management**:
   - Product bisa ditambahkan berkali-kali (quantity akan bertambah)
   - Layanan hanya bisa ditambahkan sekali per booking
4. **Biaya Administrasi**: Secara otomatis dihitung 5% dari total harga saat checkout
