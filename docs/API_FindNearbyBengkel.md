# API Dokumentasi: Find Nearby Bengkel (Bengkel Terdekat)

## Daftar Isi
1. [Pendahuluan](#pendahuluan)
2. [Endpoint Overview](#endpoint-overview)
3. [Request](#request)
4. [Response](#response)
5. [Algoritma Haversine Formula](#algoritma-haversine-formula)
6. [Implementasi Kode](#implementasi-kode)
7. [Contoh Penggunaan](#contoh-penggunaan)
8. [Error Handling](#error-handling)
9. [Diagram Alur](#diagram-alur)

---

## Pendahuluan

API **Find Nearby Bengkel** adalah endpoint yang digunakan untuk mencari bengkel terdekat berdasarkan koordinat lokasi pengguna (latitude dan longitude). Fitur ini memanfaatkan **Haversine Formula** untuk menghitung jarak antara dua titik koordinat di permukaan bumi dengan mempertimbangkan kelengkungan bumi.

### Tujuan
- Memberikan rekomendasi bengkel terdekat kepada pengguna
- Menghitung jarak real-time berdasarkan koordinat GPS
- Mendukung pencarian dalam radius tertentu (default: 10 km)

### Teknologi yang Digunakan
- **Framework**: Laravel (PHP)
- **Database**: MySQL
- **Algoritma**: Haversine Formula
- **Response Format**: JSON (REST API)

---

## Endpoint Overview

| Attribute     | Value                          |
|---------------|--------------------------------|
| **URL**       | `/api/bengkel/nearby`          |
| **Method**    | `GET`                          |
| **Auth**      | Tidak diperlukan (Public)      |
| **Content-Type** | `application/json`          |

---

## Request

### Request Parameters (Query String)

| Parameter   | Tipe    | Wajib | Default | Deskripsi                                      |
|-------------|---------|-------|---------|------------------------------------------------|
| `latitude`  | numeric | Ya    | -       | Koordinat latitude lokasi pengguna (-90 s/d 90) |
| `longitude` | numeric | Ya    | -       | Koordinat longitude lokasi pengguna (-180 s/d 180) |
| `radius`    | numeric | Tidak | 10      | Radius pencarian dalam kilometer (min: 1)      |

### Validasi Input

```php
$validator = Validator::make($request->all(), [
    'latitude' => 'required|numeric',
    'longitude' => 'required|numeric',
    'radius' => 'nullable|numeric|min:1',
]);
```

### Contoh Request

```bash
GET /api/bengkel/nearby?latitude=-6.2088&longitude=106.8456&radius=5
```

**Dengan cURL:**
```bash
curl -X GET "https://your-domain.com/api/bengkel/nearby?latitude=-6.2088&longitude=106.8456&radius=5" \
  -H "Accept: application/json"
```

---

## Response

### Response Structure

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Bengkel terdekat berhasil ditemukan"
    },
    "data": [
        {
            "id": 1,
            "name": "Bengkel Jaya Motor",
            "image": "bengkel_jaya.jpg",
            "description": "Bengkel spesialis motor dan mobil",
            "alamat": "Jl. Sudirman No. 123",
            "latitude": "-6.2100",
            "longitude": "106.8470",
            "pemilik_id": 5,
            "kecamatan_id": 1,
            "kelurahan_id": 3,
            "created_at": "2024-01-15T10:00:00.000000Z",
            "updated_at": "2024-01-20T14:30:00.000000Z",
            "distance": 0.52,
            "image_url": "https://your-domain.com/images/bengkel_jaya.jpg",
            "specialists": [
                {
                    "id": 1,
                    "name": "Motor",
                    "icon": "motor.png"
                },
                {
                    "id": 2,
                    "name": "Mobil",
                    "icon": "mobil.png"
                }
            ],
            "kecamatan": {
                "id": 1,
                "name": "Tanah Abang"
            },
            "kelurahan": {
                "id": 3,
                "name": "Bendungan Hilir"
            }
        }
    ]
}
```

### Response Fields

#### Meta Object

| Field     | Tipe    | Deskripsi                          |
|-----------|---------|------------------------------------|
| `code`    | integer | HTTP status code                   |
| `status`  | string  | Status response (success/error)    |
| `message` | string  | Pesan deskriptif                   |

#### Data Object (Array of Bengkel)

| Field          | Tipe    | Deskripsi                                    |
|----------------|---------|----------------------------------------------|
| `id`           | integer | ID unik bengkel                              |
| `name`         | string  | Nama bengkel                                 |
| `image`        | string  | Nama file gambar                             |
| `description`  | string  | Deskripsi bengkel                            |
| `alamat`       | string  | Alamat lengkap bengkel                       |
| `latitude`     | string  | Koordinat latitude bengkel                   |
| `longitude`    | string  | Koordinat longitude bengkel                  |
| `pemilik_id`   | integer | ID pemilik bengkel                           |
| `kecamatan_id` | integer | ID kecamatan                                 |
| `kelurahan_id` | integer | ID kelurahan                                 |
| `distance`     | float   | **Jarak dari lokasi user dalam kilometer**   |
| `image_url`    | string  | URL lengkap gambar bengkel                   |
| `specialists`  | array   | Daftar spesialisasi bengkel                  |
| `kecamatan`    | object  | Data kecamatan                               |
| `kelurahan`    | object  | Data kelurahan                               |

---

## Algoritma Haversine Formula

### Penjelasan Teoritis

**Haversine Formula** adalah persamaan matematika yang digunakan untuk menghitung jarak antara dua titik di permukaan bola (bumi) berdasarkan koordinat geografis (latitude dan longitude). Formula ini memperhitungkan kelengkungan bumi sehingga menghasilkan jarak yang lebih akurat dibandingkan perhitungan jarak Euclidean.

### Rumus Matematis

```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlong/2)
c = 2 × atan2(√a, √(1-a))
d = R × c
```

**Keterangan:**
- `Δlat` = lat2 - lat1 (selisih latitude dalam radian)
- `Δlong` = long2 - long1 (selisih longitude dalam radian)
- `R` = Radius bumi (6371 km)
- `d` = Jarak antara dua titik

### Implementasi SQL

```sql
SELECT *,
    ( 6371 * acos(
        cos( radians(:user_latitude) ) *
        cos( radians( latitude ) ) *
        cos( radians( longitude ) - radians(:user_longitude) ) +
        sin( radians(:user_latitude) ) *
        sin( radians( latitude ) )
    )) AS distance
FROM bengkels
HAVING distance <= :radius
ORDER BY distance ASC
```

### Visualisasi Formula

```
                    Titik B (Bengkel)
                   /
                  /
                 / d (jarak)
                /
               /
    Titik A (User)

    Dengan:
    - Titik A: latitude_user, longitude_user
    - Titik B: latitude_bengkel, longitude_bengkel
    - R: 6371 km (radius bumi)
```

---

## Implementasi Kode

### Controller: BengkelController.php

```php
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bengkel;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class BengkelController extends Controller
{
    /**
     * Find nearby bengkel based on user's coordinates
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findNearby(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                ['errors' => $validator->errors()],
                'Input tidak valid',
                400
            );
        }

        // 2. Ambil parameter dari request
        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10 km

        // 3. Query dengan Haversine Formula
        $bengkels = Bengkel::select('bengkels.*')
            ->selectRaw(
                '( 6371 * acos( cos( radians(?) ) *
                    cos( radians( latitude ) )
                    * cos( radians( longitude ) - radians(?)
                    ) + sin( radians(?) ) *
                    sin( radians( latitude ) ) )
                ) AS distance',
                [$latitude, $longitude, $latitude]
            )
            ->with(['specialists', 'kecamatan', 'kelurahan'])
            ->whereNotNull(['latitude', 'longitude'])
            ->having('distance', '<=', $radius)
            ->orderBy('distance', 'asc')
            ->get();

        // 4. Transform hasil untuk menambahkan image_url
        $bengkels->transform(function ($bengkel) {
            if ($bengkel->image) {
                $bengkel->image_url = url('images/' . $bengkel->image);
            }
            return $bengkel;
        });

        // 5. Return response
        if ($bengkels->isEmpty()) {
            return ResponseFormatter::success(
                [],
                'Tidak ada bengkel ditemukan dalam radius ' . $radius . ' km.'
            );
        }

        return ResponseFormatter::success(
            $bengkels,
            'Bengkel terdekat berhasil ditemukan'
        );
    }
}
```

### Route Definition: routes/api.php

```php
Route::prefix('bengkel')->group(function () {
    Route::get('/nearby', [BengkelController::class, 'findNearby']);
});
```

### Model: Bengkel.php

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bengkel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'alamat',
        'latitude',
        'longitude',
        'pemilik_id',
        'kecamatan_id',
        'kelurahan_id',
    ];

    // Relasi dengan Specialist (many-to-many)
    public function specialists()
    {
        return $this->belongsToMany(Specialist::class, 'bengkel_specialist');
    }

    // Relasi dengan Kecamatan
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class);
    }

    // Relasi dengan Kelurahan
    public function kelurahan()
    {
        return $this->belongsTo(Kelurahan::class);
    }
}
```

---

## Contoh Penggunaan

### 1. Mencari Bengkel dalam Radius 5 km

**Request:**
```bash
GET /api/bengkel/nearby?latitude=-6.2088&longitude=106.8456&radius=5
```

**Response (Success):**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Bengkel terdekat berhasil ditemukan"
    },
    "data": [
        {
            "id": 1,
            "name": "Bengkel Maju Jaya",
            "distance": 1.23,
            "alamat": "Jl. Kebon Sirih No. 10",
            ...
        },
        {
            "id": 5,
            "name": "Bengkel Sejahtera",
            "distance": 3.45,
            "alamat": "Jl. Thamrin No. 50",
            ...
        }
    ]
}
```

### 2. Menggunakan Radius Default (10 km)

**Request:**
```bash
GET /api/bengkel/nearby?latitude=-6.2088&longitude=106.8456
```

### 3. Tidak Ditemukan Bengkel dalam Radius

**Response:**
```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Tidak ada bengkel ditemukan dalam radius 10 km."
    },
    "data": []
}
```

---

## Error Handling

### 1. Validasi Error (400 Bad Request)

**Kondisi:** Parameter latitude atau longitude tidak diberikan atau format tidak valid.

**Response:**
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Input tidak valid"
    },
    "data": {
        "errors": {
            "latitude": ["The latitude field is required."],
            "longitude": ["The longitude field is required."]
        }
    }
}
```

### 2. Radius Invalid (400 Bad Request)

**Kondisi:** Nilai radius kurang dari 1.

**Response:**
```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Input tidak valid"
    },
    "data": {
        "errors": {
            "radius": ["The radius must be at least 1."]
        }
    }
}
```

---

## Diagram Alur

### Flowchart API Find Nearby Bengkel

```
┌─────────────────────────────────────────────────────────────┐
│                         START                                │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│  Client mengirim request GET /api/bengkel/nearby            │
│  dengan parameter: latitude, longitude, radius (optional)   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    Validasi Input                            │
│  - latitude: required, numeric                               │
│  - longitude: required, numeric                              │
│  - radius: optional, numeric, min:1                          │
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┴───────────────┐
              │                               │
        [Valid]                         [Invalid]
              │                               │
              ▼                               ▼
┌─────────────────────────┐    ┌─────────────────────────────┐
│  Set radius default     │    │  Return Error Response      │
│  jika tidak ada (10 km) │    │  HTTP 400 Bad Request       │
└─────────────────────────┘    └─────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│              Query Database dengan Haversine Formula         │
│                                                              │
│  SELECT bengkels.*,                                          │
│    (6371 * acos(cos(radians(user_lat)) *                    │
│     cos(radians(latitude)) *                                 │
│     cos(radians(longitude) - radians(user_long)) +          │
│     sin(radians(user_lat)) *                                │
│     sin(radians(latitude)))) AS distance                    │
│  FROM bengkels                                               │
│  WHERE latitude IS NOT NULL AND longitude IS NOT NULL       │
│  HAVING distance <= radius                                   │
│  ORDER BY distance ASC                                       │
└─────────────────────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│              Eager Load Relations                            │
│  - specialists                                               │
│  - kecamatan                                                 │
│  - kelurahan                                                 │
└─────────────────────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│         Transform: Tambahkan image_url ke setiap bengkel    │
└─────────────────────────────────────────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│                   Cek hasil query                            │
└─────────────────────────────────────────────────────────────┘
              │
     ┌────────┴────────┐
     │                 │
 [Empty]           [Found]
     │                 │
     ▼                 ▼
┌──────────────┐  ┌──────────────────────────────────────────┐
│  Return      │  │  Return Success Response                  │
│  Empty Array │  │  dengan data bengkel terurut by distance │
│  + Message   │  └──────────────────────────────────────────┘
└──────────────┘
              │
              ▼
┌─────────────────────────────────────────────────────────────┐
│                          END                                 │
└─────────────────────────────────────────────────────────────┘
```

### Sequence Diagram

```
┌──────┐          ┌──────────┐          ┌────────────┐          ┌──────────┐
│Client│          │API Server│          │ Controller │          │ Database │
└──┬───┘          └────┬─────┘          └─────┬──────┘          └────┬─────┘
   │                   │                      │                      │
   │  GET /api/bengkel/nearby                 │                      │
   │  ?latitude=-6.2088                       │                      │
   │  &longitude=106.8456                     │                      │
   │  &radius=5                               │                      │
   │──────────────────>│                      │                      │
   │                   │                      │                      │
   │                   │   findNearby()       │                      │
   │                   │─────────────────────>│                      │
   │                   │                      │                      │
   │                   │                      │   Validate Input     │
   │                   │                      │──────────┐           │
   │                   │                      │<─────────┘           │
   │                   │                      │                      │
   │                   │                      │  SELECT with         │
   │                   │                      │  Haversine Formula   │
   │                   │                      │─────────────────────>│
   │                   │                      │                      │
   │                   │                      │   Return bengkels    │
   │                   │                      │   with distance      │
   │                   │                      │<─────────────────────│
   │                   │                      │                      │
   │                   │                      │  Transform data      │
   │                   │                      │  (add image_url)     │
   │                   │                      │──────────┐           │
   │                   │                      │<─────────┘           │
   │                   │                      │                      │
   │                   │  JSON Response       │                      │
   │                   │<─────────────────────│                      │
   │                   │                      │                      │
   │  Response 200 OK  │                      │                      │
   │  {meta, data}     │                      │                      │
   │<──────────────────│                      │                      │
   │                   │                      │                      │
```

---

## Catatan untuk Implementasi Mobile (Flutter/Android)

### Mendapatkan Lokasi User

```dart
import 'package:geolocator/geolocator.dart';

Future<Position> getCurrentLocation() async {
  bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
  if (!serviceEnabled) {
    return Future.error('Location services are disabled.');
  }

  LocationPermission permission = await Geolocator.checkPermission();
  if (permission == LocationPermission.denied) {
    permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied) {
      return Future.error('Location permissions are denied');
    }
  }

  return await Geolocator.getCurrentPosition();
}
```

### Memanggil API

```dart
Future<List<Bengkel>> findNearbyBengkel(double lat, double long, {int radius = 10}) async {
  final response = await http.get(
    Uri.parse('$baseUrl/api/bengkel/nearby?latitude=$lat&longitude=$long&radius=$radius'),
  );

  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    return (data['data'] as List).map((e) => Bengkel.fromJson(e)).toList();
  } else {
    throw Exception('Failed to load nearby bengkel');
  }
}
```

---

## Referensi

1. **Haversine Formula**: https://en.wikipedia.org/wiki/Haversine_formula
2. **Laravel Documentation**: https://laravel.com/docs
3. **MySQL Spatial Functions**: https://dev.mysql.com/doc/refman/8.0/en/spatial-analysis-functions.html

---

**Dokumen ini dibuat untuk keperluan skripsi**

*Terakhir diperbarui: Februari 2026*
