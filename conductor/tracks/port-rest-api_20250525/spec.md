# Spec: Port REST API from Old System

## Overview
Implement remaining REST API endpoints from the old system (`http://kasir.local`) into the new CodeIgniter 4 codebase (`kasirbaru`). These cover item management, suppliers, categories, customer detail, stock management, and the receiving flow. All endpoints use JWT Bearer token from `http://kasirbaru.local/api/v1/login`.

## Response Format
All endpoints use the **old response format** (not the existing kasirbaru API format):

**Success:**
```json
{"success": true, "data": {...}}
```

**Error:**
```json
{"success": false, "error": {"code": "ERR_CODE", "message": "..."}}
```

## Functional Requirements

### 1. POST /api/v1/items — Create Item
- Request fields: `item_number`, `name`, `category`, `cost_price`, `unit_price`, `quantity`, `reorder_level`, `supplier_id`, `description`
- Validation: `item_number`, `name`, `category` required
- Duplicate check: `item_number` must be unique (return `ERR_DUPLICATE_ITEM_NUMBER`)
- On success: create item record + set initial quantity in `item_quantities` (return `ERR_ITEM_CREATED`)
- HTTP 201 on success, 400/409 on validation/duplicate error

### 2. GET /api/v1/suppliers — List/Search Suppliers
- Accepts optional `search` query param to filter by company name (LIKE)
- Returns array of `{id, name, first_name, last_name}`
- HTTP 200

### 3. GET /api/v1/categories — List/Search Categories
- Accepts optional `search` query param to filter category names (LIKE)
- Returns flat array of category name strings
- HTTP 200

### 4. GET /api/v1/customers/{id} — Get Customer Info
- Validates customer exists (return `ERR_CUSTOMER_NOT_FOUND`)
- Returns `{person_id, first_name, last_name, email, phone_number}`
- HTTP 200 / 404

### 5. GET /api/v1/stock/by-sku/{sku} — Get Stock by SKU
- Validates SKU not empty (return `ERR_MISSING_SKU`)
- Validates item exists (return `ERR_ITEM_NOT_FOUND`)
- Returns `{item_id, name, item_number, reorder_level, total_stock, stock_locations: [{location_id, location_name, quantity}]}`
- HTTP 200 / 400 / 404

### 6. GET /api/v1/stock/out-of-stock — List Out of Stock Items
- Returns items with `quantity = 0` across all locations
- Returns array of `{item_id, name, item_number, quantity, location_name}`
- HTTP 200

### 7. GET /api/v1/stock/below-minimum — List Below Minimum Stock
- Accepts optional filters: `supplier_id`, `category`, `group_by`
- Returns items where `quantity <= reorder_level`
- Returns array of `{item_id, name, item_number, category, supplier_name, reorder_level, quantity}`
- HTTP 200

### 8. PATCH /api/v1/stock/update/{sku} — Update Stock Quantity
- Validates SKU not empty (return `ERR_MISSING_SKU`)
- Validates quantity provided (return `ERR_MISSING_QUANTITY`)
- Validates item exists (return `ERR_ITEM_NOT_FOUND`)
- Updates `item_quantities` for given SKU + location
- Returns `{item_id, sku, location_id, old_quantity, new_quantity}`
- HTTP 200 / 400 / 404

### 9. GET /api/v1/receivings — Receivings Health Check
- Returns `{"message": "Receivings API is active"}`
- HTTP 200

### 10. GET /api/v1/receivings/items — Search Items for Receiving
- Accepts `term` (required, return `ERR_MISSING_TERM`) and `location_id`
- Returns array of `{item_id, name, item_number, cost_price, unit_price, category, description, supplier_id, supplier_name, quantity}`
- HTTP 200 / 400

### 11. GET /api/v1/receivings/stock-locations — List Stock Locations
- Returns array of `{location_id, location_name}`
- HTTP 200

### 12. POST /api/v1/receivings — Complete Receiving
- Request body: `items[]`, `supplier_id`, `employee_id`, `comment`, `reference`, `payment_type`, `stock_location`
- Validation: items not empty (return `ERR_EMPTY_CART`)
- Validation: cost_price < retail_price for each item (return `ERR_INVALID_PRICE_COMPARISON`)
- On success: save receiving + update stock
- Returns `{receiving_id: "RECV " + id, message: "..."}`
- HTTP 201 / 400

## Non-Functional Requirements
- All endpoints require `Authorization: Bearer <token>` header (JWT from kasirbaru.local)
- Response format follows old system pattern: `{"success": true/false, "data": {...}, "error": {"code": "...", "message": "..."}}`
- Status codes: 200 (GET success), 201 (POST success), 400 (validation error), 401 (auth error), 404 (not found), 409 (conflict/duplicate)

## Acceptance Criteria
- [ ] All 12 endpoints return correct responses with valid token
- [ ] All endpoints return 401 without token
- [ ] Create item validates required fields and duplicate item_number
- [ ] Complete receiving validates price comparison
- [ ] Stock management endpoints produce correct data
- [ ] Error response format matches old system pattern

## Out of Scope
- Edit/delete items via API (create only)
- Edit/delete suppliers, categories (read-only)
- Standalone PHP scripts (get-product.php, your_api_file.php, cekjualan.php, etc.)
- Telegram integration (updateomset.php)
- Already-implemented endpoints (items search, customers CRUD, sales create/detail, payment types)
