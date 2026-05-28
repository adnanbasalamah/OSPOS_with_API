# Rencana Implementasi Laporan Rugi Laba

## Overview
Menambahkan laporan **Rugi Laba** di halaman Reports (`/public/reports`) yang menampilkan:
- Saldo Barang (Nilai Stok)
- Saldo Kas
- Saldo Bank
- Rugi Laba (dengan breakdown Revenue, COGS, Biaya Operasional)

## File Baru

### 1. `app/Models/Reports/Profit_loss.php`
Model report yang menghitung 4 baris laporan.
- **Saldo Barang**: `SUM(items.cost_price * item_quantities.quantity)` untuk stok aktif (`stock_type=0`, `deleted=0`)
- **Saldo Kas**: `saldo_awal_kas + CashInSales - CashOutPurchases - CashOutExpenses`
- **Saldo Bank**: `saldo_awal_bank + BankInSales - BankOutPurchases - BankOutExpenses`
- **Rugi Laba**: `Revenue - COGS - BiayaOperasional`

**Mapping Payment Type:**
| Tipe | Kas | Bank |
|------|:---:|:----:|
| Cash | ✓ | |
| Debit | | ✓ |
| Transfer | | ✓ |
| QRIS | | ✓ |
| Credit | | ✓ |

### 2. `app/Views/reports/profit_loss.php`
View kustom (bukan tabular) yang menampilkan laporan dalam format ringkas.

## File Diubah

### 3. `app/Controllers/Reports.php`
- Tambah import & inisialisasi `Profit_loss` model
- Tambah method `profit_loss()` — auto periode bulan berjalan

### 4. `app/Views/reports/listing.php`
Tambah panel "Laporan Keuangan" dengan link ke Profit & Loss Report.

### 5. `app/Config/Routes.php`
Tambah route: `reports/profit_loss` → `Reports::profit_loss`

### 6. Language files
- `app/Language/en/Reports.php` — tambah labels EN
- `app/Language/id/Reports.php` — tambah labels ID

### 7. Config (Saldo Awal)
- `app/Views/configs/general_config.php` — tambah 2 field input (Saldo Awal Kas, Saldo Awal Bank)
- `app/Controllers/Config.php::postSaveGeneral()` — simpan nilai baru
- `app/Language/en/Config.php` — label EN
- `app/Language/id/Config.php` — label ID

### 8. Database Migration
- `app/Database/Migrations/20260528000000_add_profit_loss_report.php`
  - INSERT permission `reports_profit_loss`
  - INSERT grant untuk admin (person_id=1)
  - INSERT default config `balance_cash_initial` = 0, `balance_bank_initial` = 0

## Perhitungan

### Revenue (Pendapatan)
```sql
SELECT COALESCE(SUM(payment_amount), 0)
FROM ospos_sales_payments
WHERE sale_id IN (
  SELECT sale_id FROM ospos_sales
  WHERE sale_status = 0 AND DATE(sale_time) BETWEEN :start AND :end
)
```

### COGS / HPP
```sql
SELECT COALESCE(SUM(item_cost_price * quantity_purchased), 0)
FROM ospos_sales_items
WHERE sale_id IN (
  SELECT sale_id FROM ospos_sales
  WHERE sale_status = 0 AND DATE(sale_time) BETWEEN :start AND :end
)
```

### Biaya Operasional
```sql
SELECT COALESCE(SUM(amount), 0)
FROM ospos_expenses
WHERE deleted = 0 AND DATE(date) BETWEEN :start AND :end
```

### Saldo Barang
```sql
SELECT COALESCE(SUM(items.cost_price * item_quantities.quantity), 0)
FROM ospos_items
JOIN ospos_item_quantities ON items.item_id = item_quantities.item_id
WHERE items.deleted = 0 AND items.stock_type = 0
```

### Saldo Kas & Bank
```
Kas  = saldo_awal_kas  + CashSales  - CashPurchases  - CashExpenses
Bank = saldo_awal_bank + BankSales  - BankPurchases  - BankExpenses
```
