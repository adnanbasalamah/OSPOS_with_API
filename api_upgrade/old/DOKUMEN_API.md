# Dokumentasi API OSPOS (Open Source Point of Sale)

Dokumentasi ini berisi daftar API yang tersedia untuk integrasi eksternal (seperti aplikasi Android Kasir atau sistem inventaris).

## Informasi Umum

*   **Base URL:** `http://<domain-anda>/index.php/`
*   **Format Data:** JSON (Request & Response)
*   **Autentikasi:** Header `X-API-KEY` wajib disertakan di setiap permintaan.
    *   *Default API Key:* `ospos_secret_key_123` (Bisa diubah di `application/config/ospos_api.php`)

---

## 1. API Penjualan (`Api_sales`)

### 1.1. Daftar Jenis Pembayaran
Mendapatkan opsi pembayaran yang tersedia di sistem.
*   **Method:** `GET`
*   **Endpoint:** `/api_sales/payment_types`
*   **Response Sukses:**
    ```json
    {
      "success": true,
      "data": ["Cash", "Check", "Debit Card", "Credit Card", ...]
    }
    ```

### 1.2. Detail Struk (Receipt)
Mendapatkan detail transaksi penjualan berdasarkan ID.
*   **Method:** `GET`
*   **Endpoint:** `/api_sales/receipt/{sale_id}`
*   **Response Sukses:** Mengembalikan detail barang, pembayaran, dan pajak.

### 1.3. Checkout (Selesaikan Penjualan)
Menyimpan transaksi penjualan baru ke sistem.
*   **Method:** `POST`
*   **Endpoint:** `/api_sales/complete`
*   **Payload (JSON):**
    ```json
    {
      "customer_id": 123,
      "employee_id": 1,
      "sale_location": 1,
      "comment": "Catatan transaksi",
      "items": [
        {
          "item_id": "1",
          "quantity": 2,
          "price": 10000,
          "discount": 0,
          "discount_type": 0
        }
      ],
      "payments": [
        {
          "payment_type": "Cash",
          "payment_amount": 20000
        }
      ]
    }
    ```

---

## 2. API Barang (`Api_items`)

### 2.1. Tambah Barang Baru
*   **Method:** `POST`
*   **Endpoint:** `/api/items/create`
*   **Payload (JSON):** `item_number`, `name`, `category` (Wajib). `cost_price`, `unit_price`, `quantity` (Opsional).

### 2.2. Daftar Supplier
*   **Method:** `GET`
*   **Endpoint:** `/api/items/suppliers`

### 2.3. Daftar Kategori
*   **Method:** `GET`
*   **Endpoint:** `/api/items/categories`

---

## 3. API Stok (`Api_stock`)

### 3.1. Cek Stok via SKU
*   **Method:** `GET`
*   **Endpoint:** `/api_stock/get_by_sku/{sku}`

### 3.2. Update Stok (Manual)
*   **Method:** `PATCH` (atau `POST` tergantung routing)
*   **Endpoint:** `/api_stock/update_stock/{sku}`
*   **Payload:** `{"quantity": 50, "location_id": 1}`

### 3.3. Stok di Bawah Minimum
Mendapatkan daftar barang yang perlu dipesan ulang.
*   **Method:** `GET`
*   **Endpoint:** `/api_stock/below_minimum`
*   **Parameter:** `supplier_id`, `category`, `group_by` (supplier/category).

---

## 4. API Penerimaan Barang (`Api_receivings`)

### 4.1. Cari Barang (untuk Receiving)
Mencari barang berdasarkan barcode/nama khusus untuk proses input stok masuk.
*   **Method:** `GET`
*   **Endpoint:** `/api_receivings/search_item`
*   **Parameter:** `term` (nama/barcode), `location_id`.

### 4.2. Selesaikan Penerimaan (RECV)
Mencatat stok masuk dari supplier.
*   **Method:** `POST`
*   **Endpoint:** `/api_receivings/complete`
*   **Payload:** Mengirimkan daftar `items`, `supplier_id`, dan `stock_location`.

---

## 5. API Pelanggan (`Api_customers`)

### 5.1. Cari Pelanggan
*   **Method:** `GET`
*   **Endpoint:** `/api_customers/search?term=Adnan`

### 5.2. Detail Pelanggan
*   **Method:** `GET`
*   **Endpoint:** `/api_customers/info/{id}`

### 5.3. Tambah Pelanggan
*   **Method:** `POST`
*   **Endpoint:** `/api_customers/create`
*   **Payload:** `first_name`, `last_name`, `email`, `phone_number`, dll.

---

## Penanganan Error

Setiap error akan mengembalikan status code HTTP yang sesuai (400, 401, 404, 500) dengan format body:
```json
{
  "success": false,
  "error": {
    "code": "NAMA_KODE_ERROR",
    "message": "Pesan penjelasan error dalam Bahasa Indonesia/Inggris."
  }
}
```
