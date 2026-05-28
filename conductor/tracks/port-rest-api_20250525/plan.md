# Plan: Port REST API from Old System

## Phase 1: Items & Suppliers API

- [x] Task: Register routes for items-create, suppliers, categories
    - [ ] Add `$routes->post('items', 'Items::create')` in Routes.php
    - [ ] Add `$routes->get('suppliers', 'Suppliers::index')` in Routes.php
    - [ ] Add `$routes->get('categories', 'Categories::index')` in Routes.php
    - [ ] Apply `jwtauth` filter for all new routes
- [x] Task: Write unit tests for create item, list suppliers, list categories
    - [ ] Create `tests/Controllers/api/v1/ItemsCreateTest.php`
    - [ ] Create `tests/Controllers/api/v1/SuppliersTest.php`
    - [ ] Create `tests/Controllers/api/v1/CategoriesTest.php`
    - [ ] Test create item with valid data (expect 201)
    - [ ] Test create item missing required fields (expect 400)
    - [ ] Test create item duplicate item_number (expect 409)
    - [ ] Test create item without auth (expect 401)
    - [ ] Test list suppliers with/without search
    - [ ] Test list categories with/without search
    - [ ] Run tests and confirm they fail (Red phase)
- [x] Task: Implement create item endpoint
    - [ ] Create `Items::create()` method or new controller
    - [ ] Validate required fields (item_number, name, category)
    - [ ] Check unique item_number
    - [ ] Insert item + set initial quantity in item_quantities
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [x] Task: Implement suppliers list endpoint
    - [ ] Create `Suppliers::index()` controller
    - [ ] Query suppliers with optional search filter
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [x] Task: Implement categories list endpoint
    - [ ] Create `Categories::index()` controller
    - [ ] Query distinct categories with optional search filter
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Items & Suppliers API' (Protocol in workflow.md)

## Phase 2: Customer Info & Stock by SKU

- [x] Task: Register routes for customer-info and stock-by-sku
    - [ ] Add `$routes->get('customers/(:num)', 'Customers::show/$1')` in Routes.php
    - [ ] Add `$routes->get('stock/by-sku/(:any)', 'Stock::bySku/$1')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [x] Task: Write unit tests
    - [x] Test get customer info existing (expect 200)
    - [x] Test get customer info not found (expect 404)
    - [x] Test get stock by sku valid (expect 200)
    - [x] Test get stock by sku not found (expect 404)
    - [x] Test without auth (expect 401)
- [x] Task: Implement customer info endpoint
    - [x] Add `show()` method to Customers controller
    - [x] Validate customer exists via Customer model
    - [x] Return old-format response
- [x] Task: Implement stock by SKU endpoint
    - [x] Create `Stock::bySku()` controller
    - [x] Query item by item_number, join with stock locations
    - [x] Compute total_stock across locations
    - [x] Return old-format response
- [ ] Task: Conductor - User Manual Verification 'Customer Info & Stock by SKU' (Protocol in workflow.md)

## Phase 3: Stock Management API

- [x] Task: Register routes for stock management
    - [ ] Add `$routes->get('stock/out-of-stock', 'Stock::outOfStock')` in Routes.php
    - [ ] Add `$routes->get('stock/below-minimum', 'Stock::belowMinimum')` in Routes.php
    - [ ] Add `$routes->patch('stock/update/(:any)', 'Stock::update/$1')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [x] Task: Write unit tests
    - [x] Test out of stock list
    - [x] Test below minimum with filters
    - [x] Test update stock valid (expect 200)
    - [x] Test update stock missing quantity (expect 400)
    - [x] Test update stock item not found (expect 404)
    - [x] Test without auth (expect 401)
- [x] Task: Implement out-of-stock endpoint
    - [x] Query items with quantity = 0 across locations
    - [x] Return old-format response
- [x] Task: Implement below-minimum endpoint
    - [x] Query items where quantity <= reorder_level
    - [x] Support supplier_id, category filters
    - [x] Return old-format response
- [x] Task: Implement update stock endpoint
    - [x] Validate SKU and quantity
    - [x] Update item_quantities for given location
    - [x] Return old-format response with old/new quantity
- [ ] Task: Conductor - User Manual Verification 'Stock Management API' (Protocol in workflow.md)

## Phase 4: Receiving API

- [x] Task: Register routes for receiving
    - [ ] Add `$routes->get('receivings', 'Receivings::index')` in Routes.php
    - [ ] Add `$routes->get('receivings/items', 'Receivings::items')` in Routes.php
    - [ ] Add `$routes->get('receivings/stock-locations', 'Receivings::stockLocations')` in Routes.php
    - [ ] Add `$routes->post('receivings', 'Receivings::complete')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [x] Task: Write unit tests
    - [x] Test receiving health check
    - [x] Test search items with term (expect 200)
    - [x] Test search items missing term (expect 400)
    - [x] Test stock locations list
    - [x] Test complete receiving valid (expect 201)
    - [x] Test complete receiving empty cart (expect 400)
    - [x] Test complete receiving invalid price (expect 400)
    - [x] Test without auth (expect 401)
- [x] Task: Implement receiving index (health check)
    - [x] Return success message
- [x] Task: Implement search items for receiving
    - [x] Require `term` parameter (error if missing)
    - [x] Query items LIKE name, join suppliers + quantities
    - [x] Return old-format response
- [x] Task: Implement stock locations endpoint
    - [x] Query stock_locations table
    - [x] Return old-format response
- [x] Task: Implement complete receiving endpoint
    - [x] Validate items not empty
    - [x] Validate retail_price > cost_price for each item
    - [x] Manual insert into receivings + receivings_items
    - [x] Update item_quantities
    - [x] Return old-format response with receiving_id
- [ ] Task: Conductor - User Manual Verification 'Receiving API' (Protocol in workflow.md)

## Phase 5: Integration & Response Format Consistency

- [x] Task: Run full test suite and fix any issues
- [x] Task: Verify all 12 endpoints with automated tests (69 API tests passing)
- [x] Task: Ensure all responses use consistent old-system format
- [x] Task: Conductor - User Manual Verification 'Integration & Final Testing' (Protocol in workflow.md)

## Phase 6: Review Fixes

- [x] Task: Apply review suggestions fed5725
