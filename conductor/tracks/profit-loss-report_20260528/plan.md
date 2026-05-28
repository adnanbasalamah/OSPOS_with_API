# Plan: Laporan Rugi Laba (Profit & Loss Report)

## Phase 1: Database & Configuration

- [x] Task: Create migration to add permission and config defaults [14afde4]
    - [x] INSERT permission `reports_profit_loss` (module: reports)
    - [x] INSERT grant for admin (person_id=1)
    - [x] INSERT default config `balance_cash_initial = 0`, `balance_bank_initial = 0`
- [ ] Task: Write tests for config save/retrieve of saldo awal
    - [ ] Test config values can be saved and retrieved
    - [ ] Test default values are 0
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Add config UI fields for Saldo Awal Kas & Bank
    - [ ] Add input fields in app/Views/configs/general_config.php
    - [ ] Add save handler in app/Controllers/Config.php::postSaveGeneral()
    - [ ] Add language strings in app/Language/{en,id}/Config.php
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Database & Configuration' (Protocol in workflow.md)

## Phase 2: Report Model

- [ ] Task: Write unit tests for Profit_loss model (Red phase)
    - [ ] Test getDataColumns() returns expected array
    - [ ] Test getData() returns expected keys (saldo barang, kas, bank, revenue, cogs, biaya, rugilaba)
    - [ ] Test getData() with empty period returns 0 values
    - [ ] Test saldo barang calculation (mock items + quantities)
    - [ ] Run tests and confirm they fail
- [ ] Task: Implement Profit_loss model
    - [ ] Create app/Models/Reports/Profit_loss.php extending Report
    - [ ] Implement getDataColumns()
    - [ ] Implement getData() with all calculations:
        - saldo_barang: SUM(cost_price x quantity)
        - revenue: SUM(sales_payments.payment_amount) for completed sales in period
        - cogs: SUM(sales_items.item_cost_price x quantity_purchased)
        - biaya_operasional: SUM(expenses.amount) not deleted
        - saldo_kas: config balance_cash_initial + cash_inflows - cash_outflows
        - saldo_bank: config balance_bank_initial + bank_inflows - bank_outflows
        - rugi_laba: revenue - cogs - biaya_operasional
    - [ ] Implement getSummaryData()
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Report Model' (Protocol in workflow.md)

## Phase 3: Controller, Route & View

- [ ] Task: Write tests for controller and route (Red phase)
    - [ ] Test Reports::profit_loss() returns 200 response
    - [ ] Test route /public/reports/profit_loss exists
    - [ ] Test access without permission returns redirect
    - [ ] Run tests and confirm they fail
- [ ] Task: Implement controller method and route
    - [ ] Add profit_loss() method to app/Controllers/Reports.php
    - [ ] Add route in app/Config/Routes.php
    - [ ] Add route for date_input (matching existing pattern)
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement view and menu
    - [ ] Create custom view app/Views/reports/profit_loss.php
    - [ ] Add menu link in app/Views/reports/listing.php (Financial Reports panel)
    - [ ] Add language strings in app/Language/{en,id}/Reports.php
    - [ ] Ensure date_input form works for this report
- [ ] Task: Conductor - User Manual Verification 'Controller, Route & View' (Protocol in workflow.md)

## Phase 4: Integration & Final Testing

- [ ] Task: Run full test suite and fix any issues
- [ ] Task: End-to-end verification of complete flow (date filter -> calculations -> display)
- [ ] Task: Update documentation if needed
- [ ] Task: Conductor - User Manual Verification 'Integration & Final Testing' (Protocol in workflow.md)
