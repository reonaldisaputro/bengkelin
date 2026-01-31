# Forgot Password API Documentation

## Overview

API untuk forgot password dengan flow OTP verification untuk User dan Owner (Pemilik Bengkel).

## Flow

1. User/Owner input email
2. Sistem generate OTP 6 digit dan kirim ke email
3. User/Owner verify OTP yang diterima
4. Setelah OTP terverifikasi, user dapat reset password

---

## User Forgot Password Endpoints

### 1. Send OTP to User Email

Mengirim kode OTP ke email user.

**Endpoint:** `POST /api/user/forgot-password/send-otp`

**Request Body:**

```json
{
    "email": "user@example.com"
}
```

**Success Response (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Kode OTP telah dikirim ke email Anda"
    },
    "data": {
        "email": "user@example.com"
    }
}
```

**Error Response (404 - Email not found):**

```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Email tidak terdaftar"
    },
    "data": null
}
```

---

### 2. Verify OTP - User

Memverifikasi kode OTP yang dikirim ke email user.

**Endpoint:** `POST /api/user/forgot-password/verify-otp`

**Request Body:**

```json
{
    "email": "user@example.com",
    "otp": "123456"
}
```

**Success Response (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "OTP berhasil diverifikasi"
    },
    "data": {
        "email": "user@example.com",
        "verified": true
    }
}
```

**Error Response (400 - Invalid OTP):**

```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Kode OTP tidak valid atau sudah kedaluwarsa"
    },
    "data": null
}
```

**Notes:**

- OTP berlaku selama 10 menit
- Setelah OTP diverifikasi, status akan disimpan untuk proses reset password

---

### 3. Reset Password - User

Reset password setelah OTP terverifikasi.

**Endpoint:** `POST /api/user/forgot-password/reset-password`

**Request Body:**

```json
{
    "email": "user@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Success Response (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Password berhasil direset"
    },
    "data": null
}
```

**Error Response (400 - OTP not verified):**

```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "OTP belum diverifikasi atau sudah kedaluwarsa"
    },
    "data": null
}
```

**Validation Rules:**

- `password`: required, minimum 8 characters, must match password_confirmation

---

## Owner Forgot Password Endpoints

### 1. Send OTP to Owner Email

Mengirim kode OTP ke email owner (pemilik bengkel).

**Endpoint:** `POST /api/owner/forgot-password/send-otp`

**Request Body:**

```json
{
    "email": "owner@example.com"
}
```

**Success Response (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Kode OTP telah dikirim ke email Anda"
    },
    "data": {
        "email": "owner@example.com"
    }
}
```

**Error Response (404 - Email not found):**

```json
{
    "meta": {
        "code": 404,
        "status": "error",
        "message": "Email tidak terdaftar"
    },
    "data": null
}
```

---

### 2. Verify OTP - Owner

Memverifikasi kode OTP yang dikirim ke email owner.

**Endpoint:** `POST /api/owner/forgot-password/verify-otp`

**Request Body:**

```json
{
    "email": "owner@example.com",
    "otp": "123456"
}
```

**Success Response (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "OTP berhasil diverifikasi"
    },
    "data": {
        "email": "owner@example.com",
        "verified": true
    }
}
```

**Error Response (400 - Invalid OTP):**

```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "Kode OTP tidak valid atau sudah kedaluwarsa"
    },
    "data": null
}
```

---

### 3. Reset Password - Owner

Reset password setelah OTP terverifikasi.

**Endpoint:** `POST /api/owner/forgot-password/reset-password`

**Request Body:**

```json
{
    "email": "owner@example.com",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**Success Response (200):**

```json
{
    "meta": {
        "code": 200,
        "status": "success",
        "message": "Password berhasil direset"
    },
    "data": null
}
```

**Error Response (400 - OTP not verified):**

```json
{
    "meta": {
        "code": 400,
        "status": "error",
        "message": "OTP belum diverifikasi atau sudah kedaluwarsa"
    },
    "data": null
}
```

---

## Email Configuration

Pastikan email configuration sudah diset di file `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## OTP Details

- **Format**: 6 digit angka (contoh: 123456)
- **Expiration**: 10 menit setelah generate
- **Storage**: Disimpan di table `password_reset_otps`
- **Security**:
    - OTP akan dihapus setelah berhasil reset password
    - Setiap generate OTP baru akan menghapus OTP lama untuk email yang sama
    - OTP harus diverifikasi sebelum dapat reset password

## Database Migration

Jalankan migration untuk membuat table:

```bash
php artisan migrate
```

Table `password_reset_otps` berisi:

- `id`: Primary key
- `email`: Email user/owner
- `otp`: Kode OTP 6 digit
- `user_type`: 'user' atau 'owner'
- `is_verified`: Boolean status verifikasi
- `expires_at`: Waktu kadaluarsa OTP
- `created_at`: Timestamp pembuatan
- `updated_at`: Timestamp update

## Testing Flow

### User Flow:

1. **Send OTP**: POST `/api/user/forgot-password/send-otp` dengan email user
2. **Check Email**: Buka email dan dapatkan kode OTP 6 digit
3. **Verify OTP**: POST `/api/user/forgot-password/verify-otp` dengan email dan OTP
4. **Reset Password**: POST `/api/user/forgot-password/reset-password` dengan email, password baru dan konfirmasi

### Owner Flow:

1. **Send OTP**: POST `/api/owner/forgot-password/send-otp` dengan email owner
2. **Check Email**: Buka email dan dapatkan kode OTP 6 digit
3. **Verify OTP**: POST `/api/owner/forgot-password/verify-otp` dengan email dan OTP
4. **Reset Password**: POST `/api/owner/forgot-password/reset-password` dengan email, password baru dan konfirmasi

## Error Codes

- `200`: Success
- `400`: Bad Request (Invalid OTP, OTP not verified, etc)
- `404`: Not Found (Email not registered)
- `422`: Validation Error
- `500`: Server Error (Email sending failed, etc)
