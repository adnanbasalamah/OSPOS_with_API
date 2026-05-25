# Dokumentasi API Penjualan Android

**Base URL:** `http://kasirbaru.local/api/v1`
**Auth:** JWT Bearer Token (didapat dari endpoint login)

---

## Daftar API

| No | API | Method | Endpoint | Keterangan |
|----|-----|--------|----------|------------|
| 1 | Login | POST | `/api/v1/login` | Mendapatkan JWT token |
| 2 | Cari Item | GET | `/api/v1/items` | Cari barang by barcode/nama |
| 3 | Cari Pelanggan | GET | `/api/v1/customers` | Cari pelanggan by nama/telepon |
| 4 | Tambah Pelanggan | POST | `/api/v1/customers` | Buat pelanggan baru |
| 5 | Payment Types | GET | `/api/v1/sales/payment-types` | Daftar jenis pembayaran |
| 6 | Simpan Penjualan | POST | `/api/v1/sales` | Checkout/simpan transaksi |
| 7 | Detail Penjualan | GET | `/api/v1/sales/{id}` | Ambil struk penjualan |
| 8 | Logout | POST | `/api/v1/logout` | Blacklist token |

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
       |<---- { data: {id} } ----------------|
       |                                      |
       |-- 4. GET /api/v1/items?term=xxx ---->|
       |<---- { data: [...] } ---------------|
       |                                      |
       |-- 5. GET /api/v1/sales/payment-types>|
       |<---- { data: {...} } ---------------|
       |                                      |
       |-- 6. POST /api/v1/sales ------------>|
       |<---- { data: {sale_id, total} } ----|
       |                                      |
       |-- 7. GET /api/v1/sales/{id} -------->|
       |<---- { data: {items, payments} } ---|
       |                                      |
       |-- 8. POST /api/v1/logout ----------->|
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
  "status": "success",
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

**Error (401):**
```json
{
  "status": "error",
  "message": "Invalid username or password"
}
```

**Error Validasi (400):**
```json
{
  "status": "error",
  "messages": {
    "username": "The username field is required."
  }
}
```

> **Simpan token** dari response ini. Setiap API berikutnya WAJIB menyertakan header:
> `Authorization: Bearer <token>`

---

## 2. CARI ITEM

Mencari barang berdasarkan nama, barcode (item_number), atau kategori.

**Cara Pemanggilan:**
```bash
# Simpan token dari login
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
  "status": "success",
  "data": [
    {
      "item_id": 1,
      "item_number": "BRG001",
      "name": "Baju Batik Lengan Panjang",
      "category": "Pakaian",
      "cost_price": 50000,
      "unit_price": 75000,
      "quantity": 25,
      "supplier_id": 1,
      "supplier_name": "Bayu Group",
      "pic_filename": null
    },
    {
      "item_id": 2,
      "item_number": "BRG002",
      "name": "Baju Koko Putih",
      "category": "Pakaian",
      "cost_price": 35000,
      "unit_price": 55000,
      "quantity": 10,
      "supplier_id": 1,
      "supplier_name": "Bayu Group",
      "pic_filename": null
    }
  ]
}
```

**Error (401 - token tidak valid):**
```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

**Error (401 - token tidak dikirim):**
```json
{
  "status": "error",
  "message": "Missing or invalid authorization header"
}
```

---

## 3. CARI PELANGGAN

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
  "status": "success",
  "data": [
    {
      "person_id": 1,
      "first_name": "Adnan",
      "last_name": "Fauzi",
      "email": "adnan@example.com",
      "phone_number": "08123456789",
      "company_name": "PT Maju Jaya",
      "discount": 0,
      "discount_type": 0,
      "taxable": 1,
      "balance": 0
    }
  ]
}
```

**Error (401):**
```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

---

## 4. TAMBAH PELANGGAN

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
  "status": "success",
  "data": {
    "person_id": 5,
    "message": "Pelanggan berhasil ditambahkan."
  }
}
```

**Error (400 - validasi gagal):**
```json
{
  "status": "error",
  "messages": {
    "first_name": "The first_name field is required.",
    "last_name": "The last_name field is required."
  }
}
```

**Error (401):**
```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

---

## 5. PAYMENT TYPES

Mendapatkan daftar jenis pembayaran yang tersedia.

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -H "Authorization: Bearer $TOKEN" \
  "http://kasirbaru.local/api/v1/sales/payment-types"
```

**Output Benar (200):**
```json
{
  "status": "success",
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
  "status": "error",
  "message": "Invalid or expired token"
}
```

---

## 6. SIMPAN PENJUALAN (CHECKOUT)

Menyimpan transaksi penjualan. API ini akan:
1. Membuat record penjualan di tabel `sales`
2. Mencatat setiap item di `sales_items`
3. Mencatat pembayaran di `sales_payments`
4. Mengurangi stok barang
5. Mencatat inventory log

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -X POST http://kasirbaru.local/api/v1/sales \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
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

**Body (JSON):**

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `customer_id` | int | tidak | ID pelanggan (-1 = umum) |
| `employee_id` | int | tidak | ID kasir (default dari token) |
| `comment` | string | tidak | Catatan transaksi |
| `sale_location` | int | tidak | ID lokasi stok (default 1) |
| `items` | array | YA | Daftar barang (min 1) |
| `payments` | array | YA | Daftar pembayaran (min 1) |

**Item object:**

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `item_id` | int | YA | ID barang |
| `quantity` | float | YA | Jumlah beli |
| `price` | float | tidak | Harga jual (default dari database) |
| `discount` | float | tidak | Diskon (default 0) |
| `discount_type` | int | tidak | 0=persen, 1=nominal (default 0) |
| `description` | string | tidak | Deskripsi khusus |
| `serialnumber` | string | tidak | Nomor seri |

**Payment object:**

| Field | Tipe | Required | Keterangan |
|-------|------|----------|------------|
| `payment_type` | string | YA | Jenis pembayaran (Cash, QRIS, dll) |
| `payment_amount` | float | YA | Jumlah bayar |
| `cash_refund` | float | tidak | Kembalian tunai (default 0) |
| `cash_adjustment` | float | tidak | Penyesuaian (default 0) |

**Output Benar (201):**
```json
{
  "status": "success",
  "data": {
    "sale_id": 101286,
    "sale_id_display": "POS 101286",
    "sale_time": "2025-05-25 10:30:00",
    "total": 165000,
    "amount_due": 0,
    "item_count": 2,
    "message": "Transaksi penjualan berhasil disimpan."
  }
}
```

**Error (400 - item kosong):**
```json
{
  "status": "error",
  "message": "Keranjang belanja tidak boleh kosong."
}
```

**Error (400 - pembayaran kosong):**
```json
{
  "status": "error",
  "message": "Informasi pembayaran wajib diisi."
}
```

**Error (400 - item tidak ditemukan):**
```json
{
  "status": "error",
  "message": "Barang dengan ID 99 tidak ditemukan."
}
```

**Error (400 - stok tidak mencukupi):**
```json
{
  "status": "error",
  "message": "Stok 'Baju Batik' tidak mencukupi. Sisa: 5, diminta: 10."
}
```

**Error (401):**
```json
{
  "status": "error",
  "message": "Invalid or expired token"
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
  "status": "success",
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
        "quantity": 2,
        "price": 75000,
        "discount": 0,
        "discount_type": 0,
        "subtotal": 150000
      },
      {
        "item_id": 3,
        "name": "Kaos Polos Hitam",
        "item_number": "KPS001",
        "quantity": 1,
        "price": 15000,
        "discount": 0,
        "discount_type": 0,
        "subtotal": 15000
      }
    ],
    "payments": [
      {
        "payment_type": "Cash",
        "payment_amount": 165000,
        "cash_refund": 0
      }
    ],
    "total": 165000,
    "comment": "Penjualan dari HP Android"
  }
}
```

**Error (404 - tidak ditemukan):**
```json
{
  "status": "error",
  "message": "Transaksi penjualan tidak ditemukan."
}
```

**Error (401):**
```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

---

## 8. LOGOUT

Menonaktifkan token JWT (dimasukkan ke blacklist).

**Cara Pemanggilan:**
```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."

curl -X POST http://kasirbaru.local/api/v1/logout \
  -H "Authorization: Bearer $TOKEN"
```

**Output Benar (200):**
```json
{
  "status": "success",
  "message": "Logged out successfully"
}
```

**Error (401 - token tidak valid):**
```json
{
  "status": "error",
  "message": "Invalid or expired token"
}
```

**Error (401 - tanpa header):**
```json
{
  "status": "error",
  "message": "Missing or invalid authorization header"
}
```

---

## Ringkasan Flow Aplikasi Android

```
1. Login Screen
   → POST /api/v1/login
   → Simpan token di memori/shared preferences

2. Dashboard / Cari Pelanggan (opsional)
   → GET /api/v1/customers?term=xxx
   → Tampilkan hasil, user pilih pelanggan
   → Bisa juga "Lanjut tanpa pelanggan"

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
   → Kirim items[] + payments[]
   → Dapat sale_id

7. Tampilkan Struk
   → GET /api/v1/sales/{sale_id}
   → Tampilkan receipt / struk digital

8. Logout (opsional)
   → POST /api/v1/logout
   → Hapus token dari memori
```

## Catatan Penting

| Item | Keterangan |
|------|------------|
| Auth header | `Authorization: Bearer <token>` |
| Expiry token | 3600 detik (1 jam) |
| Format response sukses | `{"status":"success","data":{...}}` |
| Format response error | `{"status":"error","message":"..."}` |
| Sale ID display | `"POS " + sale_id` (contoh: "POS 101286") |
| Status code sukses | 200 (GET), 201 (POST) |
| Status code error | 400 (validasi), 401 (auth), 404 (not found) |
