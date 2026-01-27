# Withdraw Request API Documentation

API untuk mengelola permintaan penarikan dana (withdraw) bengkel.

## Base URL
```
/api
```

## Authentication
Semua endpoint memerlukan autentikasi menggunakan **Sanctum Bearer Token**.

**Header yang diperlukan:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## Endpoints

### 1. Get All Withdraw Requests

Mengambil semua data permintaan penarikan dana milik bengkel yang sedang login.

**Endpoint:**
```
GET /
```

**Authentication:** Required (auth:sanctum)

**Request Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Data pencairan berhasil diambil."
  },
  "data": [
    {
      "id": 1,
      "bengkel_id": 1,
      "amount": 150000,
      "bank": "BCA",
      "number": "1234567890",
      "name": "John Doe",
      "status": "pending",
      "image": null,
      "created_at": "2026-01-27T10:00:00.000000Z",
      "updated_at": "2026-01-27T10:00:00.000000Z"
    }
  ]
}
```

**Response Error (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Bengkel tidak ditemukan."
  },
  "data": null
}
```

**Keterangan:**
- Endpoint ini menampilkan semua riwayat permintaan penarikan dana dari bengkel yang dimiliki oleh user yang sedang login
- Data diurutkan berdasarkan waktu pembuatan

---

### 2. Create Withdraw Request

Membuat permintaan penarikan dana baru.

**Endpoint:**
```
POST /
```

**Authentication:** Required (auth:sanctum)

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "bank": "BCA",
  "number": "1234567890",
  "name": "John Doe"
}
```

**Field Validation:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| bank | string | Yes | Nama bank tujuan transfer |
| number | string | Yes | Nomor rekening bank |
| name | string | Yes | Nama pemilik rekening |

**Business Rules:**
- Minimal saldo yang dapat ditarik adalah **Rp 50.000**
- Sistem akan menghitung total dari transaksi dengan status `payment_status = 'success'` dan `withdrawn_at = null`
- Perhitungan amount: `(grand_total + ongkir) - administrasi`
- Setelah permintaan dibuat, semua transaksi yang dihitung akan ditandai dengan `withdrawn_at = now()`
- Status awal permintaan adalah `pending`

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Permintaan pencairan berhasil dibuat."
  },
  "data": null
}
```

**Response Error - Bengkel Not Found (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Bengkel tidak ditemukan."
  },
  "data": null
}
```

**Response Error - Insufficient Balance (400):**
```json
{
  "meta": {
    "code": 400,
    "status": "error",
    "message": "Minimal saldo untuk penarikan adalah 50 ribu."
  },
  "data": null
}
```

**Response Error - Validation (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "bank": ["The bank field is required."],
    "number": ["The number field is required."],
    "name": ["The name field is required."]
  }
}
```

**Keterangan:**
- Proses pembuatan permintaan dan update transaksi menggunakan database transaction untuk menjaga konsistensi data
- Hanya transaksi yang belum pernah ditarik (`withdrawn_at = null`) yang akan dihitung

---

### 3. Get Withdraw Request Detail

Mengambil detail permintaan penarikan dana berdasarkan ID.

**Endpoint:**
```
GET /{id}
```

**Authentication:** Required (auth:sanctum)

**URL Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| id | integer | ID permintaan penarikan |

**Request Headers:**
```
Authorization: Bearer {token}
```

**Response Success (200):**
```json
{
  "meta": {
    "code": 200,
    "status": "success",
    "message": "Detail pencairan berhasil diambil."
  },
  "data": {
    "id": 1,
    "bengkel_id": 1,
    "amount": 150000,
    "bank": "BCA",
    "number": "1234567890",
    "name": "John Doe",
    "status": "pending",
    "image": null,
    "created_at": "2026-01-27T10:00:00.000000Z",
    "updated_at": "2026-01-27T10:00:00.000000Z",
    "bengkel": {
      "id": 1,
      "nama": "Bengkel Jaya",
      "alamat": "Jl. Raya No. 123"
    }
  }
}
```

**Response Error - Bengkel Not Found (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Bengkel tidak ditemukan."
  },
  "data": null
}
```

**Response Error - Withdraw Request Not Found (404):**
```json
{
  "meta": {
    "code": 404,
    "status": "error",
    "message": "Data pencairan tidak ditemukan."
  },
  "data": null
}
```

**Keterangan:**
- Endpoint ini hanya menampilkan detail permintaan penarikan yang dimiliki oleh bengkel user yang sedang login
- Jika ID tidak ditemukan atau bukan milik bengkel user, akan mengembalikan error 404

---

## Data Models

### WithdrawRequest Model

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Primary key |
| bengkel_id | integer | Foreign key ke tabel bengkel |
| amount | decimal | Jumlah dana yang ditarik |
| bank | string | Nama bank tujuan |
| number | string | Nomor rekening |
| name | string | Nama pemilik rekening |
| status | enum | Status permintaan (pending, approved, rejected, completed) |
| image | string | URL bukti transfer (nullable) |
| created_at | timestamp | Waktu pembuatan |
| updated_at | timestamp | Waktu update terakhir |

### Status Values
- `pending` - Permintaan baru, menunggu persetujuan admin
- `approved` - Permintaan disetujui, menunggu transfer
- `rejected` - Permintaan ditolak
- `completed` - Transfer telah selesai dilakukan

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad Request - Saldo tidak mencukupi |
| 404 | Not Found - Bengkel atau data pencairan tidak ditemukan |
| 422 | Validation Error - Input tidak valid |
| 401 | Unauthorized - Token tidak valid atau expired |

---

## Example Usage

### cURL Example - Create Withdraw Request

```bash
curl -X POST https://your-domain.com/api/ \
  -H "Authorization: Bearer your-token-here" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "bank": "BCA",
    "number": "1234567890",
    "name": "John Doe"
  }'
```

### JavaScript Example - Get All Withdraw Requests

```javascript
fetch('https://your-domain.com/api/', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer your-token-here',
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

### PHP Example - Get Withdraw Request Detail

```php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://your-domain.com/api/1',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer your-token-here',
    'Accept: application/json',
    'Content-Type: application/json'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);
print_r($data);
```

---

## Notes

1. **Security:**
   - Semua endpoint memerlukan autentikasi dengan token Sanctum
   - User hanya dapat melihat dan membuat permintaan untuk bengkel yang mereka miliki

2. **Business Logic:**
   - Minimal penarikan: Rp 50.000
   - Hanya transaksi yang sudah sukses dan belum ditarik yang akan dihitung
   - Setelah permintaan dibuat, transaksi akan ditandai sebagai "withdrawn" untuk mencegah penarikan ganda

3. **Rate Limiting:**
   - Implementasi rate limiting dapat ditambahkan sesuai kebutuhan

4. **Permissions:**
   - User harus memiliki bengkel yang terdaftar untuk dapat mengakses endpoint ini
   - User hanya dapat mengakses data milik bengkel mereka sendiri

---

## Related Endpoints

- [Transaction API Documentation](API_Transaction.md) - Untuk melihat detail transaksi
- [Bengkel API Documentation](API_Bengkel.md) - Untuk manajemen bengkel
- [Authentication API Documentation](API_Auth.md) - Untuk autentikasi owner
