# API Login/Authentication (JWT-based)

Dokumen ini menjelaskan API autentikasi menggunakan JWT (*JSON Web Token*) untuk mengakses REST API di OSPOS.

## Base URL

Diasumsikan aplikasi berjalan di `http://localhost/`. Endpoint API menggunakan format:

`http://localhost/api/v1/[endpoint]`

## Alur Autentikasi

1. **Login** — Kirim `username` dan `password` ke `/api/v1/login`.
2. **Dapatkan Token** — Server mengembalikan JWT token (berlaku 1 jam).
3. **Gunakan Token** — Setiap request ke endpoint yang dilindungi (e.g., `/api/v1/me`, `/api/v1/logout`) wajib menyertakan header `Authorization: Bearer <token>`.
4. **Logout** — Kirim token ke `/api/v1/logout` untuk memasukkan token ke daftar hitam (*blacklist*).

---

## Endpoint: Login

Mengautentikasi user dan mengembalikan JWT token.

- **URL:** `POST /api/v1/login`
- **Body:** `application/x-www-form-urlencoded` atau `application/json`
- **Parameter:**

| Parameter  | Tipe    | Required | Deskripsi                      |
|------------|---------|----------|--------------------------------|
| `username` | String  | Ya       | Username employee              |
| `password` | String  | Ya       | Password employee              |

### Contoh Curl

```bash
curl -X POST "http://localhost/api/v1/login" \
  -H "Content-Type: application/json" \
  -d '{"username": "admin", "password": "password123"}'
```

### Respons Sukses (200)

```json
{
  "status": "success",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "username": "admin"
    }
  }
}
```

### Respons Error (401)

```json
{
  "status": "error",
  "message": "Invalid username or password"
}
```

### Respons Error — Validasi Gagal (400)

```json
{
  "status": "error",
  "messages": {
    "username": "The username field is required.",
    "password": "The password field is required."
  }
}
```

---

## Endpoint: Logout

Memasukkan token ke daftar hitam sehingga tidak bisa digunakan lagi.

- **URL:** `POST /api/v1/logout`
- **Header:** `Authorization: Bearer <token>`

### Contoh Curl

```bash
curl -X POST "http://localhost/api/v1/logout" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Respons Sukses (200)

```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

### Respons Error — Token Tidak Valid (401)

```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

---

## Endpoint: Get Current User

Mengembalikan informasi user yang sedang terautentikasi.

- **URL:** `GET /api/v1/me`
- **Header:** `Authorization: Bearer <token>`

### Contoh Curl

```bash
curl -X GET "http://localhost/api/v1/me" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Respons Sukses (200)

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "person_id": 1
  }
}
```

### Respons Error (401)

```json
{
  "status": "error",
  "message": "Missing or invalid authorization header"
}
```

Atau jika token kedaluwarsa:

```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

---

## Catatan

- Token JWT berlaku selama 1 jam (3600 detik).
- Token yang sudah di-*logout* dimasukkan ke daftar hitam selama sisa masa berlaku token.
- Password di-hash menggunakan bcrypt; legacy MD5 akan di-upgrade ke bcrypt saat login pertama kali.
- Format respons JSON konsisten: `{"status": "success"|"error", ...}`.
