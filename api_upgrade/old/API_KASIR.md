# Dokumentasi API Kasir Android (OSPOS)

File ini berisi dokumentasi khusus untuk endpoint yang digunakan oleh aplikasi kasir Android, mencakup manajemen pelanggan dan proses transaksi penjualan.

## 1. API Penjualan (Sales)
Digunakan untuk proses transaksi kasir di Android.
**Controller:** `api_sales`

### A. Jenis Pembayaran
Mengambil daftar cara pembayaran yang tersedia (Tunai, Debit, dll).
- **URL:** `GET payment_types`
- **Curl:**
  ```bash
  curl -X GET "http://kasir.local/index.php/api_sales/payment_types" \
    -H "X-API-KEY: ospos_secret_key_123"
  ```

### B. Simpan Transaksi (Complete/Checkout)
Mengirimkan data keranjang belanja dan pembayaran untuk diselesaikan. API ini otomatis memotong stok dan menghitung pajak.
- **URL:** `POST complete`
- **Body JSON:**
  ```json
  {
    "customer_id": 1,
    "employee_id": 1,
    "comment": "Sales from Android",
    "sale_location": 1,
    "items": [
      {
        "item_id": 4,
        "quantity": 2,
        "price": 7500,
        "discount": 0,
        "discount_type": 0
      }
    ],
    "payments": [
      {
        "payment_type": "Tunai",
        "payment_amount": 15000
      }
    ]
  }
  ```
- **Response Sukses:**
  ```json
  {
    "success": true,
    "data": {
      "sale_id": "POS 101286",
      "total": "15000.00",
      "message": "Transaksi penjualan berhasil disimpan."
    }
  }
  ```

### C. Detail Struk (Receipt)
Mengambil detail transaksi untuk keperluan cetak struk via printer thermal.
- **URL:** `GET receipt/{sale_id}`
- **Contoh:** `GET /api_sales/receipt/101286`

---

## 2. API Pelanggan (Customers)
Digunakan untuk manajemen data pelanggan.
**Controller:** `api_customers`

### A. Pencarian Pelanggan
Mencari pelanggan berdasarkan nama, email, atau telepon.
- **URL:** `GET search`
- **Parameter:** `term` (Optional)
- **Contoh:** `GET /api_customers/search?term=Adnan`

### B. Detail Pelanggan
Mengambil data lengkap satu pelanggan.
- **URL:** `GET info/{customer_id}`

### C. Tambah Pelanggan
Mendaftarkan pelanggan baru langsung dari aplikasi.
- **URL:** `POST create`
- **Body JSON:**
  ```json
  {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone_number": "08123456789"
  }
  ```

---

## 3. Catatan Teknis
- **Otentikasi:** Wajib menyertakan header `X-API-KEY: ospos_secret_key_123`.
- **Base URL:** `http://kasir.local/index.php/`
- **Prefix ID:** 
  - Penjualan diawali dengan `POS ` (Contoh: `POS 123`).
  - Penerimaan diawali dengan `RECV ` (Contoh: `RECV 123`).
