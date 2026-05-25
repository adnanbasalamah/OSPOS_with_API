# Plan: Port REST API from Old System

## Phase 1: Items & Suppliers API

- [ ] Task: Register routes for items-create, suppliers, categories
    - [ ] Add `$routes->post('items', 'Items::create')` in Routes.php
    - [ ] Add `$routes->get('suppliers', 'Suppliers::index')` in Routes.php
    - [ ] Add `$routes->get('categories', 'Categories::index')` in Routes.php
    - [ ] Apply `jwtauth` filter for all new routes
- [ ] Task: Write unit tests for create item, list suppliers, list categories
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
- [ ] Task: Implement create item endpoint
    - [ ] Create `Items::create()` method or new controller
    - [ ] Validate required fields (item_number, name, category)
    - [ ] Check unique item_number
    - [ ] Insert item + set initial quantity in item_quantities
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement suppliers list endpoint
    - [ ] Create `Suppliers::index()` controller
    - [ ] Query suppliers with optional search filter
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement categories list endpoint
    - [ ] Create `Categories::index()` controller
    - [ ] Query distinct categories with optional search filter
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Items & Suppliers API' (Protocol in workflow.md)

## Phase 2: Customer Info & Stock by SKU

- [ ] Task: Register routes for customer-info and stock-by-sku
    - [ ] Add `$routes->get('customers/(:num)', 'Customers::show/$1')` in Routes.php
    - [ ] Add `$routes->get('stock/by-sku/(:any)', 'Stock::bySku/$1')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [ ] Task: Write unit tests
    - [ ] Test get customer info existing (expect 200)
    - [ ] Test get customer info not found (expect 404)
    - [ ] Test get stock by sku valid (expect 200)
    - [ ] Test get stock by sku empty (expect 400)
    - [ ] Test get stock by sku not found (expect 404)
    - [ ] Test without auth (expect 401)
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement customer info endpoint
    - [ ] Create `Customers::show()` method or new controller
    - [ ] Validate customer exists via Customer model
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement stock by SKU endpoint
    - [ ] Create `Stock::bySku()` controller
    - [ ] Query item by item_number, join with stock locations
    - [ ] Compute total_stock across locations
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Customer Info & Stock by SKU' (Protocol in workflow.md)

## Phase 3: Stock Management API

- [ ] Task: Register routes for stock management
    - [ ] Add `$routes->get('stock/out-of-stock', 'Stock::outOfStock')` in Routes.php
    - [ ] Add `$routes->get('stock/below-minimum', 'Stock::belowMinimum')` in Routes.php
    - [ ] Add `$routes->patch('stock/update/(:any)', 'Stock::update/$1')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [ ] Task: Write unit tests
    - [ ] Test out of stock list
    - [ ] Test below minimum with filters
    - [ ] Test update stock valid (expect 200)
    - [ ] Test update stock missing quantity (expect 400)
    - [ ] Test update stock item not found (expect 404)
    - [ ] Test without auth (expect 401)
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement out-of-stock endpoint
    - [ ] Query items with quantity = 0 across locations
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement below-minimum endpoint
    - [ ] Query items where quantity <= reorder_level
    - [ ] Support supplier_id, category, group_by filters
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement update stock endpoint
    - [ ] Validate SKU and quantity
    - [ ] Update item_quantities for given location
    - [ ] Return old-format response with old/new quantity
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Stock Management API' (Protocol in workflow.md)

## Phase 4: Receiving API

- [ ] Task: Register routes for receiving
    - [ ] Add `$routes->get('receivings', 'Receivings::index')` in Routes.php
    - [ ] Add `$routes->get('receivings/items', 'Receivings::items')` in Routes.php
    - [ ] Add `$routes->get('receivings/stock-locations', 'Receivings::stockLocations')` in Routes.php
    - [ ] Add `$routes->post('receivings', 'Receivings::complete')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [ ] Task: Write unit tests
    - [ ] Test receiving health check
    - [ ] Test search items with term (expect 200)
    - [ ] Test search items missing term (expect 400)
    - [ ] Test stock locations list
    - [ ] Test complete receiving valid (expect 201)
    - [ ] Test complete receiving empty cart (expect 400)
    - [ ] Test complete receiving invalid price (expect 400)
    - [ ] Test without auth (expect 401)
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement receiving index (health check)
    - [ ] Return success message
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement search items for receiving
    - [ ] Require `term` parameter (error if missing)
    - [ ] Query items LIKE name, join suppliers + quantities
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement stock locations endpoint
    - [ ] Query stock_locations table
    - [ ] Return old-format response
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Implement complete receiving endpoint
    - [ ] Validate items not empty
    - [ ] Validate retail_price > cost_price for each item
    - [ ] Call Receiving model save_value or manual insert
    - [ ] Update item_quantities
    - [ ] Return old-format response with receiving_id
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Receiving API' (Protocol in workflow.md)

## Phase 5: Integration & Response Format Consistency

- [ ] Task: Run full test suite and fix any issues
- [ ] Task: Verify all 12 endpoints with curl end-to-end
- [ ] Task: Ensure all responses use consistent old-system format
- [ ] Task: Conductor - User Manual Verification 'Integration & Final Testing' (Protocol in workflow.md)
