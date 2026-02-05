# API Dokumentasi: Merk Mobil Bengkel

API untuk mengelola merk mobil yang dapat ditangani oleh bengkel. Hanya owner yang terautentikasi yang dapat mengelola merk mobil bengkelnya.

## Base URL

```
/api
```

## Authentication

Semua endpoint memerlukan autentikasi menggunakan **Sanctum Bearer Token** dengan guard `owner-api`.

```
Authorization: Bearer {token}
```

---

## 📋 Endpoints

### 1. Get Merk Mobil Bengkel

Mendapatkan daftar merk mobil yang dapat ditangani oleh bengkel tertentu.

**Endpoint:** `GET /bengkel/{id}/merk-mobil`

**Headers:**

```
Authorization: Bearer {owner_token}
Content-Type: application/json
```

**Response Success (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Daftar merk mobil bengkel berhasil diambil"
    },
    "data": [
        {
            "id": 1,
            "nama_merk": "Toyota",
            "logo": "1738742507_toyota.png",
            "logo_url": "http://localhost:8000/storage/merk_mobil/1738742507_toyota.png",
            "deskripsi": "Merk mobil Jepang",
            "created_at": "2026-02-05T07:15:07.000000Z",
            "updated_at": "2026-02-05T07:15:07.000000Z"
        },
        {
            "id": 2,
            "nama_merk": "Honda",
            "logo": "1738742520_honda.png",
            "logo_url": "http://localhost:8000/storage/merk_mobil/1738742520_honda.png",
            "deskripsi": "Merk mobil Jepang",
            "created_at": "2026-02-05T07:15:20.000000Z",
            "updated_at": "2026-02-05T07:15:20.000000Z"
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
        "message": "Bengkel tidak ditemukan"
    },
    "data": null
}
```

---

### 2. Add Merk Mobil ke Bengkel

Menambahkan satu atau beberapa merk mobil ke bengkel (tidak menghapus yang sudah ada).

**Endpoint:** `POST /bengkel/{id}/merk-mobil`

**Headers:**

```
Authorization: Bearer {owner_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "merk_mobil_ids": [1, 2, 3]
}
```

**Validasi:**

- `merk_mobil_ids`: required, array
- `merk_mobil_ids.*`: harus ada di tabel `merk_mobils`

**Response Success (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merk mobil berhasil ditambahkan ke bengkel"
    },
    "data": {
        "id": 1,
        "name": "Bengkel ABC",
        "image": "bengkel1.jpg",
        "description": "Bengkel terpercaya",
        "alamat": "Jl. Raya No. 123",
        "latitude": "-6.200000",
        "longitude": "106.816666",
        "pemilik_id": 1,
        "kecamatan_id": 1,
        "kelurahan_id": 1,
        "created_at": "2026-01-01T00:00:00.000000Z",
        "updated_at": "2026-02-05T07:30:00.000000Z",
        "merk_mobils": [
            {
                "id": 1,
                "nama_merk": "Toyota",
                "logo": "1738742507_toyota.png",
                "logo_url": "http://localhost:8000/storage/merk_mobil/1738742507_toyota.png",
                "deskripsi": "Merk mobil Jepang",
                "created_at": "2026-02-05T07:15:07.000000Z",
                "updated_at": "2026-02-05T07:15:07.000000Z",
                "pivot": {
                    "bengkel_id": 1,
                    "merk_mobil_id": 1,
                    "created_at": "2026-02-05T07:30:00.000000Z",
                    "updated_at": "2026-02-05T07:30:00.000000Z"
                }
            },
            {
                "id": 2,
                "nama_merk": "Honda",
                "logo": "1738742520_honda.png",
                "logo_url": "http://localhost:8000/storage/merk_mobil/1738742520_honda.png",
                "deskripsi": "Merk mobil Jepang",
                "created_at": "2026-02-05T07:15:20.000000Z",
                "updated_at": "2026-02-05T07:15:20.000000Z",
                "pivot": {
                    "bengkel_id": 1,
                    "merk_mobil_id": 2,
                    "created_at": "2026-02-05T07:30:00.000000Z",
                    "updated_at": "2026-02-05T07:30:00.000000Z"
                }
            }
        ]
    }
}
```

**Response Error (403):**

```json
{
    "meta": {
        "code": 403,
        "status": "error",
        "message": "Anda tidak memiliki akses ke bengkel ini"
    },
    "data": null
}
```

**Response Error (422):**

```json
{
    "meta": {
        "code": 422,
        "status": "error",
        "message": "Validasi gagal"
    },
    "data": {
        "errors": {
            "merk_mobil_ids": ["The merk mobil ids field is required."],
            "merk_mobil_ids.0": ["The selected merk mobil ids.0 is invalid."]
        }
    }
}
```

---

### 3. Update Merk Mobil Bengkel (Replace All)

Mengganti semua merk mobil bengkel dengan yang baru. Merk mobil lama yang tidak ada dalam list akan dihapus.

**Endpoint:** `PUT /bengkel/{id}/merk-mobil`

**Headers:**

```
Authorization: Bearer {owner_token}
Content-Type: application/json
```

**Request Body:**

```json
{
    "merk_mobil_ids": [1, 3, 5]
}
```

**Validasi:**

- `merk_mobil_ids`: required, array
- `merk_mobil_ids.*`: harus ada di tabel `merk_mobils`

**Response Success (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merk mobil bengkel berhasil diupdate"
    },
    "data": {
        "id": 1,
        "name": "Bengkel ABC",
        "merk_mobils": [
            {
                "id": 1,
                "nama_merk": "Toyota"
            },
            {
                "id": 3,
                "nama_merk": "Suzuki"
            },
            {
                "id": 5,
                "nama_merk": "Mitsubishi"
            }
        ]
    }
}
```

---

### 4. Remove Merk Mobil dari Bengkel

Menghapus satu merk mobil dari bengkel.

**Endpoint:** `DELETE /bengkel/{id}/merk-mobil/{merkMobilId}`

**Headers:**

```
Authorization: Bearer {owner_token}
Content-Type: application/json
```

**URL Parameters:**

- `id`: ID bengkel
- `merkMobilId`: ID merk mobil yang akan dihapus

**Response Success (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Merk mobil berhasil dihapus dari bengkel"
    },
    "data": {
        "id": 1,
        "name": "Bengkel ABC",
        "merk_mobils": [
            {
                "id": 2,
                "nama_merk": "Honda"
            }
        ]
    }
}
```

**Response Error (403):**

```json
{
    "meta": {
        "code": 403,
        "status": "error",
        "message": "Anda tidak memiliki akses ke bengkel ini"
    },
    "data": null
}
```

---

## 🔐 Authorization Rules

1. **Owner Validation**: Hanya owner yang memiliki bengkel yang dapat mengelola merk mobil bengkelnya
2. **Authentication**: Semua endpoint memerlukan token autentikasi owner dengan guard `owner-api`
3. **Token**: Token didapat dari endpoint `POST /api/login-owner`

---

## 📝 Use Cases

### Use Case 1: Owner Menambahkan Merk Mobil Pertama Kali

```bash
POST /api/bengkel/1/merk-mobil
{
    "merk_mobil_ids": [1, 2, 3, 4, 5]
}
```

Owner menambahkan 5 merk mobil yang dapat ditangani bengkelnya.

### Use Case 2: Owner Menambahkan Merk Mobil Baru Tanpa Menghapus yang Lama

```bash
POST /api/bengkel/1/merk-mobil
{
    "merk_mobil_ids": [6, 7]
}
```

Owner menambahkan 2 merk mobil baru, merk mobil sebelumnya (1-5) tetap ada.

### Use Case 3: Owner Mengubah Total Merk Mobil yang Ditangani

```bash
PUT /api/bengkel/1/merk-mobil
{
    "merk_mobil_ids": [1, 2, 3]
}
```

Owner hanya ingin menangani 3 merk mobil saja, merk 4-7 akan dihapus.

### Use Case 4: Owner Menghapus Satu Merk Mobil

```bash
DELETE /api/bengkel/1/merk-mobil/2
```

Owner menghapus Honda (ID: 2) dari daftar merk yang ditangani.

---

## 🧪 Testing dengan Postman/Thunder Client

### Step 1: Login Owner

```bash
POST /api/login-owner
{
    "email": "owner@example.com",
    "password": "password"
}
```

Simpan `token` dari response.

### Step 2: Add Merk Mobil

```bash
POST /api/bengkel/1/merk-mobil
Headers:
  Authorization: Bearer {token}
Body:
{
    "merk_mobil_ids": [1, 2, 3]
}
```

### Step 3: Get Merk Mobil Bengkel

```bash
GET /api/bengkel/1/merk-mobil
Headers:
  Authorization: Bearer {token}
```

---

## ⚠️ Error Handling

| Code | Message                                  | Deskripsi                                                            |
| ---- | ---------------------------------------- | -------------------------------------------------------------------- |
| 403  | Anda tidak memiliki akses ke bengkel ini | Owner mencoba mengakses bengkel milik owner lain                     |
| 404  | Bengkel tidak ditemukan                  | ID bengkel tidak ada di database                                     |
| 422  | Validasi gagal                           | Input tidak valid (ID merk mobil tidak ada, format array salah, dll) |
| 401  | Unauthenticated                          | Token tidak valid atau tidak ada                                     |

---

## 💡 Tips

1. Gunakan **POST** untuk menambahkan merk mobil tanpa menghapus yang lama
2. Gunakan **PUT** untuk mengganti seluruh daftar merk mobil
3. Gunakan **DELETE** untuk menghapus satu merk mobil spesifik
4. Pastikan token owner yang digunakan sesuai dengan pemilik bengkel
