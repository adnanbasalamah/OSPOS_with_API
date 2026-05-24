# Initial Concept

Open Source Point of Sale (OSPOS) - Kasirbaru adalah sistem kasir (Point of Sale) berbasis web yang dibangun dengan PHP (CodeIgniter 4) dan MySQL.

# Product Guide

## Product Vision
Menyediakan sistem Point of Sale berbasis web yang handal, lengkap, dan mudah digunakan untuk mendukung operasional bisnis ritel, dengan tambahan kemampuan akses melalui REST API untuk integrasi dengan sistem lain.

## Target Users
- Kasir / Operator toko
- Manajer toko
- Admin sistem
- Developer pihak ketiga (via API)

## Core Features (Existing)
- Manajemen stok barang (item & item kits)
- Transaksi penjualan (sales register)
- Manajemen pelanggan dan pemasok
- Pembuatan faktur dan quotation
- Pencatatan pengeluaran (expenses)
- Manajemen kasir (cash up)
- Multi-user dengan kontrol izin
- Pelaporan (penjualan, stok, pengeluaran, dll)
- Barcode generation & printing
- Multi-bahasa
- Manajemen pajak (VAT, GST, multi-tier)
- REST API endpoint untuk autentikasi (login/logout via JWT)

## Planned Features
- REST API endpoint untuk akses data (items, sales, customers, dll)
- Integrasi dengan sistem eksternal

## Constraints
- Framework CodeIgniter 4
- Database MySQL/MariaDB
- Keamanan token-based authentication
