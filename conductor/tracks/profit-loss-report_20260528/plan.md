# Plan: Laporan Rugi Laba (Profit & Loss Report)

## Phase 1: Database & Configuration

- [x] Task: Create migration to add permission and config defaults [14afde4]
    - [x] INSERT permission `reports_profit_loss` (module: reports)
    - [x] INSERT grant for admin (person_id=1)
    - [x] INSERT default config `balance_cash_initial = 0`, `balance_bank_initial = 0`
- [x] Task: Write tests for config save/retrieve of saldo awal
    - [x] Test config values can be saved and retrieved
    - [x] Test default values are 0
- [x] Task: Add config UI fields for Saldo Awal Kas & Bank [307d61a]
    - [x] Add input fields in app/Views/configs/general_config.php
    - [x] Add save handler in app/Controllers/Config.php::postSaveGeneral()
    - [x] Add language strings in app/Language/{en,id}/Config.php
- [x] Task: Conductor - User Manual Verification 'Database & Configuration' (Protocol in workflow.md)

## Phase 2: Report Model

- [x] Task: Write unit tests for Profit_loss model (Red phase)
    - [x] Test getDataColumns() returns expected array
    - [x] Test getData() returns expected keys (saldo barang, kas, bank, revenue, cogs, biaya, rugilaba)
    - [x] Test getData() with empty period returns 0 values
    - [x] Test saldo barang calculation (mock items + quantities)
    - [x] Run tests and confirm they fail
- [x] Task: Implement Profit_loss model
    - [x] Create app/Models/Reports/Profit_loss.php extending Report
    - [x] Implement getDataColumns()
    - [x] Implement getData() with all calculations:
        - saldo_barang: SUM(cost_price x quantity)
        - revenue: SUM(sales_payments.payment_amount) for completed sales in period
        - cogs: SUM(sales_items.item_cost_price x quantity_purchased)
        - biaya_operasional: SUM(expenses.amount) not deleted
        - saldo_kas: config balance_cash_initial + cash_inflows - cash_outflows
        - saldo_bank: config balance_bank_initial + bank_inflows - bank_outflows
        - rugi_laba: revenue - cogs - biaya_operasional
    - [x] Implement getSummaryData()
    - [x] Run tests and confirm they pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Report Model' (Protocol in workflow.md)

## Phase 3: Controller, Route & View

- [x] Task: Write tests for controller and route (Red phase)
    - [x] Test Reports::profit_loss() returns 200 response
    - [x] Test route /public/reports/profit_loss exists
    - [x] Test access without permission returns redirect
    - [x] Run tests and confirm they pass (16 tests, 111 assertions)
- [x] Task: Implement controller method and route
    - [x] Add profit_loss() method to app/Controllers/Reports.php
    - [x] Add route in app/Config/Routes.php
    - [x] Add route for date_input (matching existing pattern)
    - [x] Permission check for reports_profit_loss in constructor
- [x] Task: Implement view and menu
    - [x] Create custom view app/Views/reports/profit_loss.php
    - [x] Create custom input view app/Views/reports/profit_loss_input.php
    - [x] Add menu link in app/Views/reports/listing.php (Financial Reports panel)
    - [x] Add language strings in app/Language/{en,id}/Reports.php
    - [x] Exclude profit_loss from summary/graphical report loops
- [ ] Task: Conductor - User Manual Verification 'Controller, Route & View' (Protocol in workflow.md)

## Phase 4: Integration & Final Testing

- [x] Task: Run full test suite and fix any issues
- [x] Task: End-to-end verification of complete flow (date filter -> calculations -> display)
- [x] Task: Update documentation if needed
- [ ] Task: Conductor - User Manual Verification 'Integration & Final Testing' (Protocol in workflow.md)
