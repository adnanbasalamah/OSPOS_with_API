# Spec: Implement Sales API for Android

## Overview
Implement 6 REST API endpoints untuk proses penjualan dari HP Android, mencakup pencarian barang, manajemen pelanggan, jenis pembayaran, simpan transaksi, dan detail transaksi. Semua endpoint menggunakan JWT Bearer token dari api/v1/login.

## Functional Requirements

### 1. GET /api/v1/items — Cari Barang
- Menerima parameter `term` (string) untuk pencarian
- Mencocokkan `term` dengan `item_number` (barcode) secara **exact match** ATAU dengan `name` (LIKE search)
- Filter tambahan: `category`, `location_id`, `limit`
- Return fields: item_id, item_number, name, category, cost_price, unit_price, quantity, supplier_id, supplier_name, pic_filename
- Hanya menampilkan item dengan `deleted = 0` dan `stock_type = 0` (fisik)

### 2. GET /api/v1/customers — Cari Pelanggan
- Menerima parameter `term` (string) opsional
- Mencocokkan dengan `first_name`, `last_name`, `phone_number`, `email` (LIKE)
- Jika `term` kosong, return semua pelanggan (dengan limit)
- Return fields: person_id, first_name, last_name, email, phone_number, company_name, discount, discount_type, taxable, balance

### 3. POST /api/v1/customers — Tambah Pelanggan
- Field wajib: `first_name`, `last_name`
- Field opsional: phone_number, email, gender, address_1, address_2, city, state, zip, country, company_name, account_number, tax_id, taxable, discount, discount_type, comments
- Validasi input sebelum menyimpan
- Gunakan model Customer::save_customer() existing

### 4. GET /api/v1/sales/payment-types — Daftar Pembayaran
- Return daftar payment type yang terdaftar di konfigurasi OSPOS
- Menggunakan helper `get_payment_options()`

### 5. POST /api/v1/sales — Simpan Penjualan
- Menerima JSON body: customer_id, employee_id (default dari token), comment, sale_location, items[], payments[]
- **Validasi stok**: sebelum menyimpan, cek apakah quantity setiap item tersedia di location ybs. Jika stok tidak cukup, return error 400
- Items: item_id, quantity, price (optional), discount (optional), discount_type (optional)
- Payments: payment_type, payment_amount, cash_refund (optional), cash_adjustment (optional)
- Gunakan Sale_lib dan Sale::save_value() existing
- Return: sale_id, sale_id_display ("POS " + id), sale_time, total, amount_due, item_count

### 6. GET /api/v1/sales/{id} — Detail Penjualan
- Menerima sale_id dari URL segment
- Validasi keberadaan sale
- Return: sale_id, sale_id_display, sale_time, customer info, employee info, items[], payments[], total, comment

## Non-Functional Requirements
- Semua endpoint (kecuali login) membutuhkan `Authorization: Bearer <token>`
- Format response mengikuti pola existing Auth API: `{"status":"success"|"error", ...}`
- Status code: 200 (GET success), 201 (POST success), 400 (validation error), 401 (auth error), 404 (not found)

## Acceptance Criteria
- [ ] Semua 6 endpoint dapat dipanggil dengan token valid
- [ ] Pencarian item bekerja untuk barcode (exact) dan nama (LIKE)
- [ ] Validasi stok menolak transaksi jika stok tidak cukup
- [ ] Tambah pelanggan dengan data lengkap berhasil disimpan
- [ ] Simpan penjualan berhasil mengurangi stok dan mencatat di sales + inventory
- [ ] Detail penjualan menampilkan data lengkap
- [ ] Error response konsisten dengan format existing (Auth API)

## Out of Scope
- Edit/hapus pelanggan via API (hanya create & search)
- Edit/hapus penjualan via API (hanya create & detail)
- Report / analytics endpoints
- Upload gambar item
