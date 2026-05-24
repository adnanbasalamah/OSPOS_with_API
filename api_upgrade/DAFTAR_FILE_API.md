# Daftar File Pengembangan API OSPOS

Berikut adalah daftar file yang baru dikembangkan untuk menambahkan fungsi API pada sistem OSPOS:

## 1. Logic & Controllers
- `application/controllers/Api_Controller.php`: Menangani pembuatan item (`api/items/create`).
- `application/controllers/Api_receivings.php`: Menangani transaksi penerimaan barang via API.
- `application/controllers/Api_stock.php`: Menangani manajemen stok (cek stok, update stok, stok rendah).

## 2. Configuration
- `application/config/ospos_api.php`: Pengaturan otentikasi API Key (`X-API-KEY`) dan akses API.

## 3. Documentation Files
- `API_DOCUMENTATION.md`: Dokumentasi utama REST API OSPOS.
- `API_RECEIVINGS_GUIDE.md`: Panduan teknis untuk integrasi API Receiving.
- `RECEIVING_API_FILES.md`: Log file yang dikembangkan untuk fitur API Receiving.
- `receiving.MD`: Penjelasan teknis alur data receiving.

## 4. Public API Scripts (Integrasi Langsung)
- `public/your_api_file.php`
- `public/get-product.php`
- `public/updateomset.php`
- `public/cekjualan.php`
- `public/cekjualanv2.php`
- `public/cekjualanv3.php`
- `public/cekbelumada.php`

## 5. Development Plans (Conductor)
- `conductor/tracks/receiving_api_20260324/`: Rencana dan spesifikasi API Receiving.
- `conductor/tracks/item_creation_api_20260403/`: Rencana dan spesifikasi API Item Creation.
