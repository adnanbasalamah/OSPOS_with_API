# Dokumentasi Lengkap REST API OSPOS

Dokumen ini berisi panduan penggunaan seluruh API yang tersedia di sistem OSPOS untuk integrasi dengan aplikasi eksternal (seperti Android).

## 1. Otentikasi
Semua request wajib menyertakan API Key pada header HTTP.
- **Header:** `X-API-KEY`
- **Default Value:** `ospos_secret_key_123` (Dapat diubah di `application/config/ospos_api.php`)

## 2. Base URL
Diasumsikan aplikasi berjalan di `http://localhost/`. Endpoint API menggunakan format:
`http://localhost/index.php/[controller]/[method]`

---

## 3. API Penerimaan Barang (Receivings)
Digunakan untuk proses input barang masuk ke sistem.
**Controller:** `api_receivings`

### A. Pencarian Barang
Mencari barang berdasarkan Barcode, SKU, atau Nama.
- **URL:** `GET search_item`
- **Parameter:** 
  - `term` (String, Required): Barcode, SKU, atau Nama Barang.
  - `location_id` (Int, Optional): ID lokasi stok (Default: 1).
- **Contoh:** `GET /api_receivings/search_item?term=8998685785980`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api_receivings/search_item?term=8998685785980" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### B. Daftar Supplier
Mengambil daftar supplier yang terdaftar.
- **URL:** `GET suppliers`
- **Contoh:** `GET /api_receivings/suppliers`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api_receivings/suppliers" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### C. Lokasi Stok
Mengambil daftar lokasi penyimpanan barang.
- **URL:** `GET stock_locations`
- **Contoh:** `GET /api_receivings/stock_locations`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api_receivings/stock_locations" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### D. Simpan Transaksi (Complete)
Menyimpan seluruh daftar barang yang diterima ke database dalam satu transaksi. Endpoint ini juga memperbarui harga master barang secara otomatis.
- **URL:** `POST complete`
- **Body JSON:**
  ```json
  {
    "supplier_id": 19,
    "employee_id": 1,
    "comment": "Input via API",
    "reference": "FAKTUR-001",
    "payment_type": "Cash",
    "stock_location": 1,
    "items": [
      {
        "item_id": 4,
        "quantity": 10,
        "cost_price": 5500,
        "retail_price": 6500,
        "discount": 0,
        "discount_type": 0
      }
    ]
  }
  ```
- **Catatan Parameter Items:**
  - `cost_price` (Float, Optional): Harga modal baru. Jika tidak dikirim, menggunakan harga lama dari database.
  - `retail_price` (Float, Optional): Harga jual baru. Jika tidak dikirim, menggunakan harga lama dari database.
  - *Penting:* `retail_price` harus selalu lebih besar dari `cost_price`. Jika tidak, API akan mengembalikan error `ERR_INVALID_PRICE_COMPARISON`.
- **Curl:**
  ```bash
  curl -X POST "http://localhost/index.php/api_receivings/complete" \
    -H "X-API-KEY: ospos_secret_key_123" \
    -H "Content-Type: application/json" \
    -d '{
      "supplier_id": 19,
      "employee_id": 1,
      "comment": "Input via API",
      "reference": "FAKTUR-001",
      "payment_type": "Cash",
      "stock_location": 1,
      "items": [
        {
          "item_id": 4,
          "quantity": 10,
          "cost_price": 5500,
          "retail_price": 6500,
          "discount": 0,
          "discount_type": 0
        }
      ]
    }'
  ```

---

## 4. API Manajemen Stok (Stock)
Digunakan untuk memantau dan memperbarui jumlah stok.
**Controller:** `api_stock`

### A. Cek Stok per SKU
Mendapatkan detail stok barang berdasarkan SKU/Barcode.
- **URL:** `GET get_by_sku/{sku}`
- **Contoh:** `GET /api_stock/get_by_sku/8998685785980`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api_stock/get_by_sku/8998685785980" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### B. Daftar Stok Habis
Menampilkan barang dengan stok 0 atau negatif.
- **URL:** `GET out_of_stock`
- **Contoh:** `GET /api_stock/out_of_stock`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api_stock/out_of_stock" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### C. Daftar Stok di Bawah Minimum
Menampilkan barang yang perlu diorder ulang (di bawah reorder level).
- **URL:** `GET below_minimum`
- **Parameter (Optional):** `supplier_id`
- **Contoh:** `GET /api_stock/below_minimum?supplier_id=5`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api_stock/below_minimum?supplier_id=5" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### D. Update Stok Fisik
Memperbarui jumlah stok secara langsung di lokasi tertentu.
- **URL:** `PATCH update_stock/{sku}`
- **Body JSON:**
  ```json
  {
    "location_id": 1,
    "quantity": 150
  }
  ```
- **Curl:**
  ```bash
  curl -X PATCH "http://localhost/index.php/api_stock/update_stock/8998685785980" \
    -H "X-API-KEY: ospos_secret_key_123" \
    -H "Content-Type: application/json" \
    -d '{
      "location_id": 1,
      "quantity": 150
    }'
  ```

---

## 5. API Manajemen Barang (Items)
Digunakan untuk mengelola data master barang.
**Base URL:** `api/items/`

### A. Buat Item Baru
Menambahkan barang baru ke dalam sistem.
- **URL:** `POST create`
- **Body JSON:**
  ```json
  {
    "item_number": "SKU-999",
    "name": "Barang Baru",
    "category": "Elektronik",
    "cost_price": 5000,
    "unit_price": 7500,
    "quantity": 20,
    "reorder_level": 5,
    "supplier_id": 19
  }
  ```
- **Curl:**
  ```bash
  curl -X POST "http://localhost/index.php/api/items/create" \
    -H "X-API-KEY: ospos_secret_key_123" \
    -H "Content-Type: application/json" \
    -d '{
      "item_number": "SKU-999",
      "name": "Barang Baru",
      "category": "Elektronik",
      "cost_price": 5000,
      "unit_price": 7500,
      "quantity": 20,
      "reorder_level": 5,
      "supplier_id": 19
    }'
  ```

### B. Daftar Supplier
Mengambil daftar supplier untuk keperluan pembuatan item baru.
- **URL:** `GET suppliers`
- **Parameter (Optional):**
  - `search` (String): Filter berdasarkan nama perusahaan supplier.
- **Contoh:** `GET /api/items/suppliers?search=Test`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api/items/suppliers?search=Test" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### C. Daftar Kategori
Mengambil daftar kategori produk yang sudah ada di database.
- **URL:** `GET categories`
- **Parameter (Optional):**
  - `search` (String): Filter berdasarkan nama kategori.
- **Contoh:** `GET /api/items/categories?search=Elek`
- **Curl:**
  ```bash
  curl -X GET "http://localhost/index.php/api/items/categories?search=Elek" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

---

## 6. Format Respons & Penanganan Error

### Berhasil (Success)
Status Code: `200 OK` atau `201 Created`.
```json
{
  "success": true,
  "data": { ... }
}
```

### Gagal (Error)
Status Code: `400`, `401`, `404`, atau `500`.
```json
{
  "success": false,
  "error": {
    "code": "ERR_CODE",
    "message": "Pesan kesalahan dalam Bahasa Indonesia"
  }
}
```

**Daftar Kode Error Umum:**
- `ERR_AUTH_FAILED`: API Key salah atau tidak ada.
- `ERR_ITEM_NOT_FOUND`: Barang tidak ditemukan.
- `ERR_VALIDATION_FAILED`: Data input tidak lengkap atau format salah.
- `ERR_INVALID_PRICE_COMPARISON`: Harga jual lebih kecil atau sama dengan harga modal.
- `ERR_DUPLICATE_ITEM_NUMBER`: SKU sudah digunakan oleh barang lain.
