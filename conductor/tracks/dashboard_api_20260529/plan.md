# Implementation Plan: Dashboard API for Mobile App

## Phase 1: Controller & Route Setup [checkpoint: 00515b7]

- [x] Task: Create Dashboard controller class (f3c9535)
    - [x] Write failing test for Dashboard controller class existence and method signature
    - [x] Implement `App\Controllers\api\v1\Dashboard` extending `ResourceController`
    - [x] Implement stub `index()` method returning empty success response
    - [x] Verify tests pass
- [x] Task: Register API route and JWT filter (f3c9535)
    - [x] Write failing test verifying route `GET /api/v1/dashboard` is registered
    - [x] Add route to `app/Config/Routes.php` under `api/v1` group
    - [x] Add route to `jwtauth` filter list in `app/Config/Filters.php`
    - [x] Verify tests pass
- [ ] Task: Conductor - User Manual Verification 'Controller & Route Setup' (Protocol in workflow.md)

## Phase 2: Dashboard Data Queries [checkpoint: 7b6fb1f]

- [x] Task: Implement total_transactions query (b8a04df)
    - [x] Write failing test for query returning count of completed sales in date range
    - [x] Implement query against `sales` table with `sale_status = 'complete'` and date filtering
    - [x] Verify tests pass
- [x] Task: Implement total_revenue query (b8a04df)
    - [x] Write failing test for query returning sum of revenue (accounting for discounts)
    - [x] Implement query summing payment_amount from sales_payments joined with completed sales
    - [x] Verify tests pass
- [x] Task: Implement hourly_revenue query (b8a04df)
    - [x] Write failing test for query returning hourly revenue breakdown
    - [x] Implement query grouping by `HOUR(sale_time)` with payment aggregation, filling zeros for hours 6-22
    - [x] Verify tests pass
- [ ] Task: Conductor - User Manual Verification 'Dashboard Data Queries' (Protocol in workflow.md)

## Phase 3: Dashboard API Endpoint Integration

- [ ] Task: Implement date parameter validation
    - [ ] Write failing test for invalid date format returning 400 error
    - [ ] Implement validation for `date_from` and `date_to` parameters (Y-m-d format)
    - [ ] Write failing test for default behavior (no params = today)
    - [ ] Implement default date logic (both omitted = today; only date_from = date_from)
    - [ ] Verify tests pass
- [ ] Task: Compose combined dashboard response
    - [ ] Write failing integration test for full dashboard response structure
    - [ ] Implement `index()` method composing `total_transactions`, `total_revenue`, and `hourly_revenue` into unified JSON response
    - [ ] Write failing test for JWT auth enforcement (returns 401 without token)
    - [ ] Implement/verify JWT filter is properly applied
    - [ ] Verify tests pass
- [ ] Task: Conductor - User Manual Verification 'Dashboard API Endpoint Integration' (Protocol in workflow.md)
