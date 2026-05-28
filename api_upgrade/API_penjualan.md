# Dokumentasi API Sales

**Base URL:** `http://kasirbaru.local/api/v1`
**Auth:** JWT Bearer Token (didapat dari endpoint login)

---

## Daftar API

| No | API | Method | Endpoint | Keterangan |
|----|-----|--------|----------|------------|
| 1 | Login | POST | `/api/v1/login` | Mendapatkan JWT token |
| 2 | Cari Pelanggan | GET | `/api/v1/customers` | Cari pelanggan by nama/telepon |
| 3 | Tambah Pelanggan | POST | `/api/v1/customers` | Buat pelanggan baru |
| 4 | Cari Item | GET | `/api/v1/items` | Cari barang by nama/barcode |
| 5 | Payment Types | GET | `/api/v1/sales/payment-types` | Daftar jenis pembayaran |
| 6 | Simpan Penjualan | POST | `/api/v1/sales` | Checkout/simpan transaksi |
| 7 | Detail Penjualan | GET | `/api/v1/sales/{id}` | Ambil struk penjualan |
| 8 | User Info | GET | `/api/v1/me` | Informasi user yg login |
| 9 | Logout | POST | `/api/v1/logout` | Blacklist token |

---

## Urutan Pemanggilan API (Flow Penjualan)

```
  [HP Android]                         [Server kasirbaru]
       |                                      |
       |-- 1. POST /api/v1/login ----------->|
       |<---- { token: "eyJ..." } -----------|
       |                                      |
       |-- 2. GET /api/v1/customers?term= --->|  (opsional)
       |<---- { data: [...] } ---------------|
       |                                      |
       |-- 3. POST /api/v1/customers -------->|  (opsional)
       |<---- { data: {person_id} } ---------|
       |                                      |
       |-- 4. GET /api/v1/items?term=xxx ---->|
       |<---- { data: [...] } ---------------|
       |                                      |
       |-- 5. GET /api/v1/sales/payment-types>|
       |<---- { data: [...] } ---------------|
       |                                      |
       |-- 6. POST /api/v1/sales ------------>|
       |<---- { data: {sale_id, total} } ----|
       |                                      |
       |-- 7. GET /api/v1/sales/{id} -------->|
       |<---- { data: {items, payments} } ---|
       |                                      |
       |-- 8. GET /api/v1/me ---------------->|  (opsional)
       |<---- { data: {id, username} } ------|
       |                                      |
       |-- 9. POST /api/v1/logout ----------->|
       |<---- { message: "Logged out" } -----|
       |                                      |
```

---

## 1. LOGIN

Mendapatkan JWT token untuk akses API selanjutnya.

**Cara Pemanggilan:**
```bash
curl -X POST http://kasirbaru.local/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"pointofsale"}'
```

**Output Benar (200):**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJrYXNpcmJhcnUiLCJpYXQiOjE3NDgxOTUyMDAsImV4cCI6MTc0ODE5ODgwMCwic3ViIjoxLCJ1c2VybmFtZSI6ImFkbWluIn0.example",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "username": "admin"
    }
  }
}
```

**Error Validasi (400):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Username and password are required."
  }
}
```

**Error Kredensial (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_INVALID_CREDENTIALS",
    "message": "Invalid username or password."
  }
}
```

> **Simpan token** dari response ini. Setiap API berikutnya WAJIB menyertakan header:
> `Authorization: Bearer <token>`
>
> **Simpan `user.id`** untuk dikirim sebagai header `X-User-Id` pada saat checkout.

---

## 2. CARI PELANGGAN

Mencari pelanggan berdasarkan nama, telepon, atau email.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -H "Authorization: Bearer $TOKEN" \
  "http://kasirbaru.local/api/v1/customers?term=adnan"
```

**Parameter:**

| Param | Tipe | Required | Default | Keterangan |
|-------|------|----------|---------|------------|
| `term` | string | tidak | `""` | Kata kunci (nama/telepon/email) |
| `limit` | int | tidak | `25` | Maksimal jumlah hasil |

**Output Benar (200):**
```json
{
  "success": true,
  "data": [
    {
      "person_id": 1,
      "first_name": "Adnan",
      "last_name": "Fauzi",
      "email": "adnan@example.com",
      "phone_number": "08123456789",
      "company_name": "PT Maju Jaya",
      "discount": 0.0,
      "discount_type": 0,
      "taxable": 1,
      "balance": 0.0
    }
  ]
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Invalid or expired token"
  }
}
```

---

## 3. TAMBAH PELANGGAN

Mendaftarkan pelanggan baru. Minimal `first_name` dan `last_name`.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -X POST http://kasirbaru.local/api/v1/customers \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "first_name": "Ahmad",
    "last_name": "Rizki",
    "phone_number": "08123456788",
    "email": "ahmad@email.com",
    "address_1": "Jl. Merdeka No. 10",
    "city": "Bandung"
  }'
```

**Body (JSON):**

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `first_name` | string | YA | Nama depan |
| `last_name` | string | YA | Nama belakang |
| `phone_number` | string | tidak | Nomor telepon |
| `email` | string | tidak | Alamat email |
| `gender` | int | tidak | 0=wanita, 1=pria |
| `address_1` | string | tidak | Alamat |
| `address_2` | string | tidak | Alamat lanjutan |
| `city` | string | tidak | Kota |
| `state` | string | tidak | Provinsi |
| `zip` | string | tidak | Kode pos |
| `country` | string | tidak | Negara |
| `company_name` | string | tidak | Nama perusahaan |
| `account_number` | string | tidak | Nomor akun |
| `tax_id` | string | tidak | NPWP |
| `taxable` | int | tidak | 1=kena pajak, 0=tidak (default 1) |
| `discount` | float | tidak | Diskon tetap (default 0) |
| `discount_type` | int | tidak | 0=persen, 1=nominal (default 0) |
| `comments` | string | tidak | Catatan |

**Output Benar (201):**
```json
{
  "success": true,
  "data": {
    "person_id": 5,
    "message": "Pelanggan berhasil ditambahkan."
  }
}
```

**Error (400 - validasi gagal):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Nama depan dan belakang wajib diisi."
  }
}
```

**Error (500 - gagal simpan):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_SAVE_FAILED",
    "message": "Gagal menyimpan data pelanggan."
  }
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Invalid or expired token"
  }
}
```

---

## 4. CARI ITEM

Mencari barang berdasarkan nama, barcode (`item_number`), atau kategori.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

# Cari barang
curl -H "Authorization: Bearer $TOKEN" \
  "http://kasirbaru.local/api/v1/items?term=baju&limit=20"
```

**Parameter:**

| Param | Tipe | Required | Default | Keterangan |
|-------|------|----------|---------|------------|
| `term` | string | tidak | `""` | Kata kunci pencarian (nama / barcode) |
| `limit` | int | tidak | `25` | Maksimal jumlah hasil |
| `category` | string | tidak | `""` | Filter berdasarkan kategori |
| `location_id` | int | tidak | `1` | ID lokasi stok |

**Output Benar (200):**
```json
{
  "success": true,
  "data": [
    {
      "item_id": 1,
      "item_number": "BRG001",
      "name": "Baju Batik Lengan Panjang",
      "category": "Pakaian",
      "cost_price": 50000.0,
      "unit_price": 75000.0,
      "quantity": 25.0,
      "supplier_id": 1,
      "supplier_name": "Bayu Group",
      "pic_filename": null
    },
    {
      "item_id": 2,
      "item_number": "BRG002",
      "name": "Baju Koko Putih",
      "category": "Pakaian",
      "cost_price": 35000.0,
      "unit_price": 55000.0,
      "quantity": 10.0,
      "supplier_id": 1,
      "supplier_name": "Bayu Group",
      "pic_filename": null
    }
  ]
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Invalid or expired token"
  }
}
```

---

## 5. PAYMENT TYPES

Mendapatkan daftar jenis pembayaran yang tersedia. Jika mode toko adalah `sale_work_order`, akan ditambahkan opsi `cash_deposit` dan `credit_deposit`.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -H "Authorization: Bearer $TOKEN" \
  "http://kasirbaru.local/api/v1/sales/payment-types"
```

**Output Benar (200):**
```json
{
  "success": true,
  "data": [
    {"id": "Cash", "name": "Tunai"},
    {"id": "Debit Card", "name": "Kartu Debit"},
    {"id": "Credit Card", "name": "Kartu Kredit"},
    {"id": "QRIS", "name": "QRIS"},
    {"id": "Transfer", "name": "Transfer Bank"}
  ]
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Invalid or expired token"
  }
}
```

---

## 6. SIMPAN PENJUALAN (CHECKOUT)

Menyimpan transaksi penjualan. API ini akan:
1. Memvalidasi items, payments, dan ketersediaan stok
2. Membuat record penjualan di tabel `sales`
3. Mencatat setiap item di `sales_items`
4. Mencatat pembayaran di `sales_payments`
5. Mengurangi stok barang

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -X POST http://kasirbaru.local/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -H "X-User-Id: 1" \
  -d '{
    "customer_id": 1,
    "comment": "Penjualan dari HP Android",
    "sale_location": 1,
    "items": [
      {
        "item_id": 1,
        "quantity": 2,
        "price": 75000,
        "discount": 0,
        "discount_type": 0
      },
      {
        "item_id": 3,
        "quantity": 1,
        "price": 15000,
        "discount": 0,
        "discount_type": 0
      }
    ],
    "payments": [
      {
        "payment_type": "Cash",
        "payment_amount": 165000,
        "cash_refund": 0,
        "cash_adjustment": 0
      }
    ]
  }'
```

**Header khusus:**

| Header | Tipe | Required | Keterangan |
|--------|------|----------|------------|
| `X-User-Id` | int | YA | ID karyawan/kasir (dari `user.id` hasil login) |

**Body (JSON):**

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `customer_id` | int | tidak | ID pelanggan (-1 atau tidak dikirim = pelanggan umum) |
| `comment` | string | tidak | Catatan transaksi |
| `sale_location` | int | tidak | ID lokasi stok (default 1) |
| `items` | array | YA | Daftar barang (min 1) |
| `payments` | array | YA | Daftar pembayaran (min 1) |

**Item object:**

| Field | Tipe | Required | Default | Keterangan |
|-------|------|----------|---------|------------|
| `item_id` | int | YA | - | ID barang |
| `quantity` | float | YA | - | Jumlah beli |
| `price` | float | tidak | `unit_price` dari database | Harga jual |
| `discount` | float | tidak | 0 | Nilai diskon |
| `discount_type` | int | tidak | 0 | 0=persen, 1=nominal |
| `description` | string | tidak | deskripsi dari database | Deskripsi khusus item ini |
| `serialnumber` | string | tidak | `""` | Nomor seri |
| `print_option` | int | tidak | 0 | 0=cetak di struk, 1=jangan cetak |

**Payment object:**

| Field | Tipe | Required | Default | Keterangan |
|-------|------|----------|---------|------------|
| `payment_type` | string | YA | - | Jenis pembayaran (Cash, QRIS, Debit Card, dll) |
| `payment_amount` | float | YA | - | Jumlah bayar |
| `cash_refund` | float | tidak | 0 | Kembalian tunai |
| `cash_adjustment` | int | tidak | 0 | 1=jika ada penyesuaian kas, 0=tidak |

**Output Benar (201):**
```json
{
  "success": true,
  "data": {
    "sale_id": 101286,
    "sale_id_display": "POS 101286",
    "sale_time": "2025-05-28 10:30:00",
    "total": 165000.00,
    "amount_due": 0.00,
    "item_count": 2
  }
}
```

**Error (400 - item kosong):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Item penjualan wajib diisi."
  }
}
```

**Error (400 - pembayaran kosong):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Pembayaran wajib diisi."
  }
}
```

**Error (400 - item tidak valid):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Item #2: item_id dan quantity wajib valid."
  }
}
```

**Error (400 - item tidak ditemukan):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_ITEM_NOT_FOUND",
    "message": "Item #1 (ID: 99) tidak ditemukan."
  }
}
```

**Error (400 - stok tidak mencukupi):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_OUT_OF_STOCK",
    "message": "Stok tidak mencukupi untuk beberapa item."
  },
  "data": {
    "out_of_stock": [
      {
        "item_id": 1,
        "name": "Baju Batik Lengan Panjang",
        "requested": 10,
        "available": 5
      }
    ]
  }
}
```

**Error (400 - pembayaran tidak valid):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Setiap pembayaran harus memiliki payment_type dan payment_amount."
  }
}
```

**Error (500 - gagal simpan):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_SAVE_FAILED",
    "message": "Gagal menyimpan transaksi penjualan."
  }
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Invalid or expired token"
  }
}
```

---

## 7. DETAIL PENJUALAN (RECEIPT)

Mengambil detail transaksi penjualan untuk ditampilkan di struk.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -H "Authorization: Bearer $TOKEN" \
  "http://kasirbaru.local/api/v1/sales/101286"
```

**Output Benar (200):**
```json
{
  "success": true,
  "data": {
    "sale_id": 101286,
    "sale_id_display": "POS 101286",
    "sale_time": "2025-05-25 10:30:00",
    "customer": {
      "person_id": 1,
      "name": "Adnan Fauzi",
      "phone_number": "08123456789"
    },
    "employee": {
      "person_id": 1,
      "name": "Admin"
    },
    "items": [
      {
        "item_id": 1,
        "name": "Baju Batik Lengan Panjang",
        "item_number": "BRG001",
        "quantity": 2.0,
        "price": 75000.0,
        "discount": 0.0,
        "discount_type": 0,
        "subtotal": 150000.0
      },
      {
        "item_id": 3,
        "name": "Kaos Polos Hitam",
        "item_number": "KPS001",
        "quantity": 1.0,
        "price": 15000.0,
        "discount": 0.0,
        "discount_type": 0,
        "subtotal": 15000.0
      }
    ],
    "payments": [
      {
        "payment_type": "Cash",
        "payment_amount": 165000.0,
        "cash_refund": 0.0
      }
    ],
    "total": 165000.0,
    "comment": "Penjualan dari HP Android"
  }
}
```

**Error (404 - tidak ditemukan):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_SALE_NOT_FOUND",
    "message": "Transaksi penjualan tidak ditemukan."
  }
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Invalid or expired token"
  }
}
```

---

## 8. USER INFO

Mendapatkan informasi user yang sedang login. Berguna untuk verifikasi token dan mendapatkan data user.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -H "Authorization: Bearer $TOKEN" \
  "http://kasirbaru.local/api/v1/me"
```

**Output Benar (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "person_id": 1
  }
}
```

**Error (401):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_REQUIRED",
    "message": "Authentication required."
  }
}
```

---

## 9. LOGOUT

Menonaktifkan token JWT (dimasukkan ke blacklist) sehingga tidak bisa dipakai lagi.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -X POST http://kasirbaru.local/api/v1/logout \
  -H "Authorization: Bearer $TOKEN"
```

**Output Benar (200):**
```json
{
  "success": true,
  "data": {
    "message": "Logged out successfully."
  }
}
```

**Error (401 - tanpa header):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_MISSING_TOKEN",
    "message": "Missing or invalid authorization header."
  }
}
```

**Error (401 - token tidak valid):**
```json
{
  "success": false,
  "error": {
    "code": "ERR_INVALID_TOKEN",
    "message": "Invalid or expired token."
  }
}
```

---

## Tabel Error Code

| Error Code | HTTP Status | Penyebab |
|------------|-------------|----------|
| `ERR_VALIDATION_FAILED` | 400 | Input tidak valid (field required, format salah) |
| `ERR_INVALID_CREDENTIALS` | 401 | Username/password salah |
| `ERR_MISSING_TOKEN` | 401 | Header Authorization tidak dikirim |
| `ERR_INVALID_TOKEN` | 401 | Token tidak valid / kedaluwarsa |
| `ERR_AUTH_FAILED` | 401 | Autentikasi gagal (umum) |
| `ERR_AUTH_REQUIRED` | 401 | Token tidak ada / user tidak terautentikasi |
| `ERR_ITEM_NOT_FOUND` | 400 | ID barang tidak ditemukan di database |
| `ERR_OUT_OF_STOCK` | 400 | Stok barang tidak mencukupi |
| `ERR_SAVE_FAILED` | 500 | Gagal menyimpan transaksi (database error) |
| `ERR_SALE_NOT_FOUND` | 404 | ID penjualan tidak ditemukan |
| `ERR_CUSTOMER_NOT_FOUND` | 404 | ID pelanggan tidak ditemukan |
| `ERR_USER_NOT_FOUND` | 404 | User tidak ditemukan |
| `ERR_DUPLICATE_ITEM_NUMBER` | 409 | Nomor barang sudah terdaftar |

---

## Ringkasan Flow Aplikasi Android

```
1. Login Screen
   → POST /api/v1/login
   → Simpan token + user.id di memori/shared preferences

2. Dashboard / Cari Pelanggan (opsional)
   → GET /api/v1/customers?term=xxx
   → Tampilkan hasil, user pilih pelanggan
   → Bisa juga "Lanjut tanpa pelanggan" (kirim customer_id = -1)

3. Tambah Pelanggan Baru (opsional)
   → POST /api/v1/customers
   → Dapat person_id untuk digunakan di transaksi

4. Scan / Cari Barang
   → GET /api/v1/items?term=barcode|nama
   → Tampilkan hasil, user pilih & input quantity
   → Ulangi sampai semua barang masuk keranjang

5. Pilih Pembayaran
   → GET /api/v1/sales/payment-types
   → User pilih jenis pembayaran, input jumlah bayar

6. Checkout / Simpan
   → POST /api/v1/sales
   → Headers: Authorization + X-User-Id
   → Body: items[] + payments[]
   → Dapat sale_id

7. Tampilkan Struk
   → GET /api/v1/sales/{sale_id}
   → Tampilkan receipt / struk digital

8. Logout (opsional)
   → POST /api/v1/logout
   → Hapus token dari memori
```

---

## Catatan Penting

| Item | Keterangan |
|------|------------|
| Auth header | `Authorization: Bearer <token>` |
| Kasir header | `X-User-Id: <employee_id>` (wajib untuk `POST /api/v1/sales`) |
| Expiry token | 3600 detik (1 jam) — diatur di `app/Config/API.php` |
| Format response sukses | `{"success": true, "data": {...}}` |
| Format response error | `{"success": false, "error": {"code": "ERR_...", "message": "..."}}` |
| Sale ID display | `"POS " + sale_id` (contoh: `"POS 101286"`) |
| Status code sukses | 200 (GET), 201 (POST) |
| Status code error | 400 (validasi), 401 (auth), 404 (not found), 409 (conflict), 500 (server) |
| `customer_id` = -1 | Pelanggan umum (walk-in, tidak perlu dicatat) |
| `discount_type` = 0 | Diskon dalam persen |
| `discount_type` = 1 | Diskon dalam nominal rupiah |
| `cash_adjustment` = 1 | Penyesuaian kas aktif (untuk laporan kas) |
| Cari tanpa `term` | Akan mengembalikan semua data (dibatasi `limit`) |
