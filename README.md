<div align="center">
  <img src="https://raw.githubusercontent.com/opensourcepos/opensourcepos/master/branding/emblem.svg" alt="OSPOS Logo" height="120">
  <h1>OSPOS with API</h1>
  <p><strong>Open Source Point of Sale — Dilengkapi REST API Modern</strong></p>
  <p>
    <a href="#fitur">Fitur</a> ·
    <a href="#api-endpoints">API</a> ·
    <a href="#tech-stack">Tech Stack</a> ·
    <a href="#instalasi">Instalasi</a> ·
    <a href="#dokumentasi">Dokumentasi</a>
  </p>
  <p>
    <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/CodeIgniter-4.6-EF4223?logo=codeigniter&logoColor=white" alt="CodeIgniter">
    <img src="https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white" alt="MySQL">
    <img src="https://img.shields.io/badge/JWT-Auth-000000?logo=jsonwebtokens&logoColor=white" alt="JWT">
    <img src="https://img.shields.io/badge/License-MIT-yellow" alt="License">
  </p>
</div>

---

## Tentang

**OSPOS with API** adalah platform Point of Sale berbasis web yang dikembangkan dari [Open Source Point of Sale](https://github.com/opensourcepos/opensourcepos) dengan tambahan **REST API modern** menggunakan autentikasi **JWT (JSON Web Token)**. Cocok untuk retailer, restoran, dan bisnis kecil-menengah yang membutuhkan sistem POS terintegrasi dengan aplikasi pihak ketiga.

Versi: **3.4.1** · Framework: **CodeIgniter 4.6** · Database: **MySQL/MariaDB**

---

## Fitur

### POS Inti
- Manajemen stok barang & item kit dengan atribut ekstensibel
- Pencatatan transaksi penjualan lengkap dengan diskon dan pajak
- Quotation & invoicing dengan cetak PDF (Dompdf)
- Penerimaan barang (receivings / GRN)
- Manajemen pelanggan, pemasok, dan karyawan
- Kontrol akses berbasis peran (RBAC) dengan sistem permission
- Barcode generation & printing (Code39, EAN, dll)
- Pajak multi-tier (VAT, GST, pajak pelanggan)
- Gift cards & program rewards
- Manajemen meja restoran
- Laporan penjualan, stok, pajak, diskon, pembayaran
- Cash up function (rekap kasir harian)
- Pencatatan pengeluaran (expenses)
- Multi-bahasa (45+ bahasa termasuk Indonesia)
- SMS messaging & MailChimp integration
- Google reCAPTCHA pada halaman login

### REST API (`/api/v1`)
- Autentikasi JWT (HS256) dengan token blacklist
- **Items** — CRUD, cari berdasarkan SKU/nama/kategori/barcode
- **Customers** — CRUD, cari, detail per ID
- **Suppliers** — daftar pemasok
- **Categories** — daftar kategori barang
- **Stock** — cek stok by SKU, barang habis, barang minimal, update stok
- **Receivings** — transaksi penerimaan barang
- **Sales** — buat transaksi, detail penjualan, daftar tipe pembayaran
- CORS support untuk integrasi dengan aplikasi frontend/ mobile

---

## API Endpoints

| Method | Endpoint | Auth | Deskripsi |
|--------|----------|:----:|-----------|
| POST | `/api/v1/login` | ✗ | Mendapatkan JWT token |
| POST | `/api/v1/logout` | ✓ | Revoke token |
| GET | `/api/v1/me` | ✓ | Info user saat ini |
| GET | `/api/v1/items` | ✓ | Daftar / cari barang |
| POST | `/api/v1/items` | ✓ | Tambah barang baru |
| GET | `/api/v1/customers` | ✓ | Daftar / cari pelanggan |
| POST | `/api/v1/customers` | ✓ | Tambah pelanggan baru |
| GET | `/api/v1/customers/{id}` | ✓ | Detail pelanggan |
| GET | `/api/v1/suppliers` | ✓ | Daftar pemasok |
| GET | `/api/v1/categories` | ✓ | Daftar kategori |
| GET | `/api/v1/stock/by-sku/{sku}` | ✓ | Cek stok by SKU |
| GET | `/api/v1/stock/out-of-stock` | ✓ | Barang habis |
| GET | `/api/v1/stock/below-minimum` | ✓ | Barang di bawah minimum |
| PATCH | `/api/v1/stock/update/{sku}` | ✓ | Update stok |
| GET | `/api/v1/sales/payment-types` | ✓ | Tipe pembayaran |
| POST | `/api/v1/sales` | ✓ | Buat transaksi penjualan |
| GET | `/api/v1/sales/{id}` | ✓ | Detail penjualan |
| GET | `/api/v1/receivings/items` | ✓ | Cari barang untuk receiving |
| GET | `/api/v1/receivings/stock-locations` | ✓ | Daftar lokasi stok |
| POST | `/api/v1/receivings` | ✓ | Complete receiving |

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Language** | PHP 8.1+ |
| **Framework** | CodeIgniter 4.6 |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **API Auth** | JWT (firebase/php-jwt) HS256 |
| **Frontend** | Bootstrap 3 + Bootswatch themes |
| **Barcode** | picqer/php-barcode-generator |
| **PDF** | Dompdf 2.0 |
| **Cache** | Redis (predis) |
| **Container** | Docker & Docker Compose |
| **Testing** | PHPUnit 11 |

---

## Instalasi

### Persiapan
- PHP ≥ 8.1
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite / Nginx
- Composer

### Langkah Cepat

```bash
# Clone repositori
git clone https://github.com/adnanbasalamah/OSPOS_with_API.git
cd OSPOS_with_API

# Install dependencies
composer install

# Salin file environment
cp .env.example .env

# Edit .env — sesuaikan konfigurasi database
#   database.default.hostname = localhost
#   database.default.database = kasirbaru
#   database.default.username = root
#   database.default.password =

# Import database
mysql -u root -p kasirbaru < app/Database/database.sql

# Jalankan
php spark serve
```

Atau dengan Docker:

```bash
docker compose up -d
```

---

## Struktur Direktori

```
├── app/
│   ├── Config/          # Konfigurasi aplikasi
│   ├── Controllers/
│   │   ├── api/v1/      # REST API controllers
│   │   ├── Sales.php    # POS sales register
│   │   ├── Items.php    # Manajemen barang
│   │   └── ...
│   ├── Database/        # Migrations & SQL scripts
│   ├── Filters/         # JWTAuth, CORS filters
│   ├── Helpers/         # Helper functions
│   ├── Libraries/       # Barcode, Sale, Tax, dll
│   ├── Models/          # Data models
│   ├── Language/        # 45+ bahasa
│   └── Views/           # Template views
├── api_upgrade/         # Dokumentasi API
├── conductor/           # Manajemen proyek
├── docker-compose.yml   # Docker orchestration
├── Dockerfile           # Multi-stage build
└── public/              # Web root
```

---

## Dokumentasi

Dokumentasi API lengkap tersedia di direktori [`api_upgrade/`](api_upgrade/):
- [`API_sekarang.md`](api_upgrade/API_sekarang.md) — Dokumentasi lengkap endpoint (Bahasa Indonesia)
- [`API_login.md`](api_upgrade/API_login.md) — Alur autentikasi
- [`API_penjualan.md`](api_upgrade/API_penjualan.md) — Endpoint penjualan

Dokumen proyek dan panduan kontribusi di [`conductor/`](conductor/).

---

## Pengembangan

```bash
# Jalankan test
php vendor/bin/phpunit

# Development dengan Docker
docker compose -f docker-compose.dev.yml up -d
```

---

## Lisensi

MIT License dengan tambahan atribusi footer. Detail lengkap di [LICENSE](LICENSE).

---

<div align="center">
  <sub>Built on top of <a href="https://github.com/opensourcepos/opensourcepos">opensourcepos/opensourcepos</a> · © 2010-2025</sub>
</div>
