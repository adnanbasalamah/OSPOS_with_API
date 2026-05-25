# Dokumentasi Terpadu OSPOS REST API (Upgrade v1.0)

Dokumen ini berisi daftar lengkap API yang tersedia di sistem OSPOS untuk integrasi eksternal, kegunaan masing-masing, serta detail teknis penggunaannya menggunakan `curl`.

## 1. Konfigurasi Global & Otentikasi

Semua permintaan API wajib menyertakan kunci keamanan pada header HTTP.

*   **Base URL:** `http://<domain-anda>/index.php/`
*   **Header Wajib:** `X-API-KEY: ospos_secret_key_123`
*   **Format Data:** JSON (Request & Response)

---

## 2. API Penjualan (`Api_sales`)
Digunakan untuk memproses transaksi kasir dan riwayat penjualan.

### 2.1. Daftar Jenis Pembayaran
Mengambil opsi pembayaran yang tersedia di sistem (Cash, Debit, dll).
*   **Method:** `GET`
*   **Endpoint:** `/api_sales/payment_types`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_sales/payment_types" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 2.2. Selesaikan Penjualan (Checkout)
Menyimpan transaksi penjualan baru ke sistem.
*   **Method:** `POST`
*   **Endpoint:** `/api_sales/complete`
*   **Payload JSON:**
    ```json
    {
      "customer_id": 1,
      "employee_id": 1,
      "sale_location": 1,
      "comment": "Transaksi via API",
      "items": [
        { "item_id": "1", "quantity": 2, "price": 10000, "discount": 0, "discount_type": 0 }
      ],
      "payments": [
        { "payment_type": "Cash", "payment_amount": 20000 }
      ]
    }
    ```
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api_sales/complete" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Content-Type: application/json" \
      -d '{"customer_id": 1, "employee_id": 1, "sale_location": 1, "items": [{"item_id": "1", "quantity": 2, "price": 10000}], "payments": [{"payment_type": "Cash", "payment_amount": 20000}]}'
    ```

### 2.3. Detail Struk (Receipt)
Mendapatkan informasi detail transaksi berdasarkan ID penjualan.
*   **Method:** `GET`
*   **Endpoint:** `/api_sales/receipt/{sale_id}`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_sales/receipt/1" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

---

## 3. API Penerimaan Barang (`Api_receivings`)
Digunakan untuk mencatat stok masuk dari supplier.

### 3.1. Cari Barang (Pencarian SKU/Nama)
Mencari barang untuk ditambahkan ke daftar penerimaan.
*   **Method:** `GET`
*   **Endpoint:** `/api_receivings/search_item`
*   **Parameter:** `term` (SKU/Nama), `location_id`.
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_receivings/search_item?term=8998685785980" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 3.2. Daftar Supplier
*   **Method:** `GET`
*   **Endpoint:** `/api_receivings/suppliers`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_receivings/suppliers" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 3.3. Daftar Lokasi Stok
*   **Method:** `GET`
*   **Endpoint:** `/api_receivings/stock_locations`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_receivings/stock_locations" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 3.4. Selesaikan Penerimaan (Complete)
Menyimpan daftar barang masuk dan memperbarui harga master secara otomatis.
*   **Method:** `POST`
*   **Endpoint:** `/api_receivings/complete`
*   **Payload JSON:**
    ```json
    {
      "supplier_id": 19,
      "employee_id": 1,
      "stock_location": 1,
      "items": [
        { "item_id": 4, "quantity": 10, "cost_price": 5000, "retail_price": 7000 }
      ]
    }
    ```
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api_receivings/complete" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Content-Type: application/json" \
      -d '{"supplier_id": 19, "employee_id": 1, "items": [{"item_id": 4, "quantity": 10}]}'
    ```

---

## 4. API Manajemen Stok (`Api_stock`)
Digunakan untuk memantau dan mengelola persediaan fisik.

### 4.1. Cek Stok via SKU
*   **Method:** `GET`
*   **Endpoint:** `/api_stock/get_by_sku/{sku}`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_stock/get_by_sku/8998685785980" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 4.2. Update Stok (Adjustment Manual)
Memperbarui jumlah stok fisik secara langsung di lokasi tertentu.
*   **Method:** `PATCH`
*   **Endpoint:** `/api_stock/update_stock/{sku}`
*   **Payload:** `{"quantity": 150, "location_id": 1}`
*   **Curl:**
    ```bash
    curl -X PATCH "http://localhost/index.php/api_stock/update_stock/8998685785980" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Content-Type: application/json" \
      -d '{"quantity": 150, "location_id": 1}'
    ```

### 4.3. Laporan Stok Habis
Menampilkan barang dengan stok <= 0.
*   **Method:** `GET`
*   **Endpoint:** `/api_stock/out_of_stock`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_stock/out_of_stock" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 4.4. Stok di Bawah Minimum (Reorder List)
*   **Method:** `GET`
*   **Endpoint:** `/api_stock/below_minimum`
*   **Parameter:** `supplier_id` (opsional).
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_stock/below_minimum?supplier_id=5" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

---

## 5. API Manajemen Barang (`Api_items`)
Digunakan untuk mengelola Data Master produk.

### 5.1. Tambah Barang Baru
*   **Method:** `POST`
*   **Endpoint:** `/api/items/create`
*   **Payload:** `item_number`, `name`, `category`, `cost_price`, `unit_price`, `quantity`.
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api/items/create" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Content-Type: application/json" \
      -d '{"item_number": "SKU-999", "name": "Produk Baru", "category": "General", "cost_price": 1000, "unit_price": 2000}'
    ```

### 5.2. Daftar Supplier & Kategori
*   **GET `/api/items/suppliers`**: `curl -X GET "http://localhost/index.php/api/items/suppliers" -H "X-API-KEY: ospos_secret_key_123"`
*   **GET `/api/items/categories`**: `curl -X GET "http://localhost/index.php/api/items/categories" -H "X-API-KEY: ospos_secret_key_123"`

---

## 6. API Manajemen Pelanggan (`Api_customers`)
Digunakan untuk database customer.

### 6.1. Cari Pelanggan
*   **Method:** `GET`
*   **Endpoint:** `/api_customers/search`
*   **Parameter:** `term` (Nama/Telp).
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_customers/search?term=Adnan" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 6.2. Detail Pelanggan
*   **Method:** `GET`
*   **Endpoint:** `/api_customers/info/{id}`
*   **Curl:**
    ```bash
    curl -X GET "http://localhost/index.php/api_customers/info/1" \
      -H "X-API-KEY: ospos_secret_key_123"
    ```

### 6.3. Tambah Pelanggan Baru
*   **Method:** `POST`
*   **Endpoint:** `/api_customers/create`
*   **Curl:**
    ```bash
    curl -X POST "http://localhost/index.php/api_customers/create" \
      -H "X-API-KEY: ospos_secret_key_123" \
      -H "Content-Type: application/json" \
      -d '{"first_name": "John", "last_name": "Doe", "phone_number": "08123456789"}'
    ```

---

## 7. Penanganan Error & Kode Respons

Sistem mengembalikan format JSON standar untuk setiap kesalahan:

```json
{
  "success": false,
  "error": {
    "code": "KODE_ERROR",
    "message": "Pesan kesalahan dalam Bahasa Indonesia"
  }
}
```

**Kode Error Umum:**
*   `ERR_AUTH_FAILED`: API Key salah.
*   `ERR_ITEM_NOT_FOUND`: Barang tidak ditemukan.
*   `ERR_VALIDATION_FAILED`: Input tidak valid atau tidak lengkap.
*   `ERR_INVALID_PRICE_COMPARISON`: Harga jual <= Harga modal.
*   `ERR_DUPLICATE_ITEM_NUMBER`: SKU sudah digunakan.
