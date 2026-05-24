# Dokumentasi API OSPOS dengan Login & Bearer Token (Versi Lengkap & Output)

Dokumen ini adalah referensi teknis final untuk integrasi aplikasi Android dengan sistem OSPOS. Semua request wajib menyertakan **X-API-KEY** dan **Bearer Token** (setelah login).

## 1. Konfigurasi Global
*   **Base URL:** `http://localhost/index.php/`
*   **Header Keamanan Statis:** `X-API-KEY: ospos_secret_key_123`
*   **Header Keamanan Dinamis:** `Authorization: Bearer <token_hasil_login>`

---

## 2. API Autentikasi (Login & Logout)

### 2.1. Login (Mendapatkan Token)
**Endpoint:** `POST /api_login/login`
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api_login/login" \
      -H "Content-Type: application/json" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -d '{"username": "admin", "password": "password_anda"}'
    ```
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
        "employee_id": 1,
        "username": "admin",
        "first_name": "Administrator",
        "last_name": "Sistem",
        "permissions": ["items", "sales", "receivings", "customers"]
      }
    }
    ```

### 2.2. Logout (Menghapus Sesi)
**Endpoint:** `POST /api_login/logout`
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api_login/logout" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Authorization: Bearer MASUKKAN_TOKEN_DISINI"
    ```
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "message": "Logout berhasil, token telah dinonaktifkan."
    }
    ```

---

## 3. API Penjualan (`Api_sales`)

### 3.1. Daftar Jenis Pembayaran
**Endpoint:** `GET /api_sales/payment_types`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_sales/payment_types" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Authorization: Bearer MASUKKAN_TOKEN_DISINI"
    ```
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": ["Cash", "Check", "Debit Card", "Credit Card", "Gift Card"]
    }
    ```

### 3.2. Selesaikan Penjualan (Checkout)
**Endpoint:** `POST /api_sales/complete`
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api_sales/complete" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Authorization: Bearer MASUKKAN_TOKEN_DISINI" \
      -H "Content-Type: application/json" \
      -d '{
        "customer_id": 1,
        "employee_id": 1,
        "sale_location": 1,
        "comment": "Input via Android",
        "items": [{"item_id": "1", "quantity": 2, "price": 10000, "discount": 0}],
        "payments": [{"payment_type": "Cash", "payment_amount": 20000}]
      }'
    ```
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "sale_id": 450,
        "receipt_url": "http://localhost/index.php/sales/receipt/450"
      }
    }
    ```

### 3.3. Detail Struk (Receipt)
**Endpoint:** `GET /api_sales/receipt/{sale_id}`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "sale_id": 450,
        "sale_time": "2023-10-27 10:30:00",
        "customer": "John Doe",
        "employee": "Admin",
        "items": [
          { "name": "Beras 5kg", "qty": 2, "price": 50000, "total": 100000 }
        ],
        "total": 100000,
        "payment": "Cash: 100000"
      }
    }
    ```

---

## 4. API Penerimaan Barang (`Api_receivings`)

### 4.1. Cari Barang (Search)
**Endpoint:** `GET /api_receivings/search_item?term=SKU001`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": [
        {
          "item_id": 4,
          "name": "Minyak Goreng 1L",
          "item_number": "8998685785980",
          "cost_price": 14000,
          "unit_price": 16000,
          "quantity": 50
        }
      ]
    }
    ```

### 4.2. Selesaikan Penerimaan (Complete)
**Endpoint:** `POST /api_receivings/complete`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "receiving_id": 12,
        "message": "Stok berhasil diperbarui dan harga master diupdate."
      }
    }
    ```

---

## 5. API Manajemen Stok (`Api_stock`)

### 5.1. Cek Stok via SKU
**Endpoint:** `GET /api_stock/get_by_sku/8998685785980`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "item_id": 4,
        "name": "Minyak Goreng 1L",
        "stock_details": [
          { "location_name": "Toko Utama", "quantity": 45 },
          { "location_name": "Gudang", "quantity": 100 }
        ]
      }
    }
    ```

### 5.2. Update Stok Fisik (Manual)
**Endpoint:** `PATCH /api_stock/update_stock/8998685785980`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "sku": "8998685785980",
        "new_quantity": 150,
        "location_id": 1
      }
    }
    ```

---

## 6. API Manajemen Barang (`Api_items`)

### 6.1. Buat Barang Baru
**Endpoint:** `POST /api/items/create`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": {
        "item_id": 101,
        "item_number": "SKU-999",
        "name": "Produk Baru"
      }
    }
    ```

---

## 7. API Manajemen Pelanggan (`Api_customers`)

### 7.1. Cari Pelanggan
**Endpoint:** `GET /api_customers/search?term=Adnan`
*   **Output Sukses:**
    ```json
    {
      "success": true,
      "data": [
        { "id": 123, "name": "Adnan Doe", "phone": "08123456789", "email": "adnan@test.com" }
      ]
    }
    ```

---

## 8. Penanganan Error (Format JSON)

Jika terjadi kesalahan, server akan merespons dengan status code yang sesuai (400, 401, 403, 404, 500) dan body berikut:

**Contoh Error Token Kadaluarsa:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "Sesi telah berakhir. Silakan login kembali."
  }
}
```

**Contoh Error Validasi Harga:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_INVALID_PRICE",
    "message": "Harga jual (6000) tidak boleh lebih rendah dari harga modal (7000)."
  }
}
```

---

## Tabel Referensi Kode Error Lengkap

| Kode Error | Penjelasan untuk Android |
| :--- | :--- |
| `ERR_AUTH_FAILED` | Arahkan user ke layar Login. |
| `ERR_TOKEN_MISSING` | Header Authorization tidak disertakan. |
| `ERR_LOGIN_FAILED` | Tampilkan "Username atau Password salah". |
| `ERR_ITEM_NOT_FOUND` | Barang tidak ada (mungkin SKU salah ketik). |
| `ERR_VALIDATION_FAILED` | Ada field wajib yang kosong dalam JSON. |
| `ERR_NO_ACCESS` | User login tidak punya hak akses modul ini. |
| `ERR_DUPLICATE_SKU` | Barcode/SKU sudah dipakai barang lain. |
