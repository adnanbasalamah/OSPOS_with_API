# Panduan API Penerimaan Barang (Receivings)

Dokumen ini menjelaskan cara menggunakan API untuk fitur penerimaan barang (Receivings) yang dapat digunakan oleh aplikasi Android atau klien eksternal lainnya.

## 1. Otentikasi
Semua request wajib menyertakan API Key pada header HTTP.
- **Header:** `X-API-KEY`
- **Default Value:** `ospos_secret_key_123` (Dapat diubah di `application/config/ospos_api.php`)

## 2. Base URL
Diasumsikan aplikasi berjalan di `http://localhost/`. Endpoint API adalah:
`http://localhost/index.php/api_receivings/`

---

## 3. Daftar Endpoint

### A. Pencarian Barang (Search Item)
Digunakan untuk mencari detail barang berdasarkan SKU/Barcode/Nama sebelum ditambahkan ke daftar penerimaan.
- **URL:** `GET search_item`
- **Parameter:** 
  - `term` (String, Required): Barcode, SKU, atau Nama Barang.
  - `location_id` (Int, Optional): ID lokasi stok (Default: 1).
- **Contoh Curl:**
  ```bash
  curl -X GET -H "X-API-KEY: ospos_secret_key_123" \
  "http://localhost/index.php/api_receivings/search_item?term=8998685785980"
  ```
- **Struktur Respons (Data):**
  ```json
  {
    "success": true,
    "data": [
      {
        "item_id": "4",
        "name": "AIR IKHWAN 1LITER",
        "item_number": "8998685785980",
        "cost_price": 5500.0,
        "unit_price": 6500.0,
        "category": "MINUMAN",
        "description": "",
        "supplier_id": 4,
        "supplier_name": "KILANG AIR SUBANG",
        "quantity": 61.0
      }
    ]
  }
  ```
  *Note: `cost_price`, `unit_price`, dan `quantity` dikembalikan sebagai tipe numeric (float) untuk mempermudah kalkulasi.*

### B. Daftar Supplier
Mengambil daftar supplier yang terdaftar di sistem.
- **URL:** `GET suppliers`
- **Contoh Curl:**
  ```bash
  curl -X GET -H "X-API-KEY: ospos_secret_key_123" \
  "http://localhost/index.php/api_receivings/suppliers"
  ```

### C. Lokasi Stok
Mengambil daftar lokasi penyimpanan barang.
- **URL:** `GET stock_locations`
- **Contoh Curl:**
  ```bash
  curl -X GET -H "X-API-KEY: ospos_secret_key_123" \
  "http://localhost/index.php/api_receivings/stock_locations"
  ```

### D. Simpan Transaksi (Complete)
Menyimpan seluruh daftar barang yang diterima ke database dalam satu transaksi. Endpoint ini juga memperbarui harga master barang secara otomatis.
- **URL:** `POST complete`
- **Content-Type:** `application/json`
- **Body JSON:**
  ```json
  {
    "supplier_id": 19,
    "employee_id": 1,
    "comment": "Input via Android",
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
- **Contoh Curl:**
  ```bash
  curl -X POST -H "X-API-KEY: ospos_secret_key_123" \
  -H "Content-Type: application/json" \
  -d '{"items": [{"item_id": 4, "quantity": 10, "cost_price": 5500, "retail_price": 6500}]}' \
  "http://localhost/index.php/api_receivings/complete"
  ```

---

## 4. Format Respons

### Berhasil (Success)
```json
{
  "success": true,
  "data": {
    "receiving_id": "RECV 98",
    "message": "Penerimaan barang berhasil disimpan."
  }
}
```

### Gagal (Error)
```json
{
  "success": false,
  "error": {
    "code": "ERR_EMPTY_CART",
    "message": "Daftar barang tidak boleh kosong."
  }
}
```

## 5. Tips Pengujian Manual
1. Pastikan Anda menggunakan `index.php` di dalam URL jika `mod_rewrite` tidak aktif.
2. Gunakan flag `-s` pada curl untuk menyembunyikan progress bar jika diperlukan.
3. Gunakan tool seperti `jq` untuk merapikan output JSON:
   `curl ... | jq`
