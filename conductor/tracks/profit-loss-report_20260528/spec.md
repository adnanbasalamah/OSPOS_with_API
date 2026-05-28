# Laporan Rugi Laba (Profit & Loss Report)

## Overview

Menambahkan laporan Rugi Laba (Profit & Loss) baru di halaman Reports yang menampilkan posisi keuangan dan kinerja laba/rugi dalam suatu periode. Laporan ini mengintegrasikan data penjualan, pembelian, stok, dan biaya operasional yang sudah ada di sistem.

## Functional Requirements

### 1. Akses Laporan
- Laporan dapat diakses dari menu Reports → Financial Reports → Laporan Rugi Laba
- Route: `/public/reports/profit_loss`
- Memerlukan permission `reports_profit_loss`

### 2. Input / Filter
- Menggunakan filter rentang tanggal (start_date, end_date) seperti laporan summary lainnya di sistem
- Format input menggunakan datepicker yang sudah ada

### 3. Baris Laporan yang Ditampilkan
| Baris | Sumber Data |
|---|---|
| **Saldo Barang** | `SUM(items.cost_price × item_quantities.quantity)` untuk stok aktif (`stock_type=0, deleted=0`) |
| **Saldo Kas** | Saldo Awal Kas + Penerimaan Cash (sales) - Pengeluaran Cash (pembelian + biaya) |
| **Saldo Bank** | Saldo Awal Bank + Penerimaan non-Cash (sales Transfer/QRIS/Debit/Credit) - Pengeluaran non-Cash (pembelian + biaya) |
| **Pendapatan (Revenue)** | `SUM(sales_payments.payment_amount)` untuk completed sales dalam periode |
| **HPP / COGS** | `SUM(sales_items.item_cost_price × quantity_purchased)` dalam periode |
| **Biaya Operasional** | `SUM(expenses.amount)` untuk expenses `deleted=0` dalam periode |
| **Rugi Laba** | `Revenue - COGS - Biaya Operasional` |

### 4. Saldo Awal Kas & Bank
- Ditambahkan sebagai field konfigurasi di halaman Config → General
- Key: `balance_cash_initial` dan `balance_bank_initial`
- Default value: 0

### 5. Mapping Payment Type
| Payment Type | Kategori |
|---|---|
| Cash | Kas |
| Debit | Bank |
| Transfer | Bank |
| QRIS | Bank |
| Credit | Bank |

### 6. Output / View
- View kustom (bukan tabular standard) yang menampilkan laporan dalam format ringkas
- Menampilkan periode laporan (rentang tanggal)
- Nilai ditampilkan dalam format currency (`to_currency()`)

### 7. Permission
- New permission: `reports_profit_loss` (module: `reports`)
- Granted to admin by default

## Non-Functional Requirements

- Laporan hanya menghitung transaksi dengan status completed (`sale_status = 0`)
- Hanya items dengan `stock_type = 0` (HAS_STOCK) dan `deleted = 0` yang dihitung dalam saldo barang
- Expenses dengan `deleted = 0`
- Stok barang dihitung point-in-time (current state), bukan berdasarkan periode

## Acceptance Criteria

1. Report muncul di halaman `/public/reports` setelah user memiliki permission
2. User dapat memilih rentang tanggal dan melihat hasil laporan
3. Saldo Barang menampilkan nilai stok terkini (cost_price × quantity)
4. Saldo Kas menampilkan: saldo awal + cash inflow - cash outflow dalam periode
5. Saldo Bank menampilkan: saldo awal + non-cash inflow - non-cash outflow dalam periode
6. Pendapatan, HPP, dan Biaya Operasional sesuai periode yang dipilih
7. Rugi Laba = Revenue - COGS - Biaya Operasional
8. Admin dapat mengatur Saldo Awal Kas & Bank di halaman Config

## Out of Scope

- Laporan grafik (graphical) untuk Rugi Laba — cukup format summary
- Export ke PDF/Excel — menggunakan export bawaan browser
