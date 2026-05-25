# Plan: Implement Sales API for Android

## Phase 1: Items Search API [checkpoint: 616a443]

- [x] Task: Create Items API controller file and register route (GET /api/v1/items)
    - [x] Create `app/Controllers/api/v1/Items.php` extending ResourceController
    - [x] Add `$routes->get('items', 'Items::index')` inside api/v1 group
    - [x] Apply `jwtauth` filter to items route in Filters.php
- [x] Task: Write unit tests for items search
    - [x] Create `tests/Controllers/api/v1/ItemsTest.php`
    - [x] Test search by barcode (exact match)
    - [x] Test search by name (LIKE)
    - [x] Test search with category filter
    - [x] Test search without auth (expect 401)
    - [x] Run tests and confirm they fail (Red phase)
- [x] Task: Implement Items::index() endpoint [1a04c82]
    - [x] Read `term`, `category`, `location_id`, `limit` from query params
    - [x] Query items with LIKE on `name` OR exact on `item_number`
    - [x] Filter by `deleted = 0`, `stock_type = 0`
    - [x] Join item_quantities for stock, suppliers for supplier_name
    - [x] Return JSON response with items data
    - [x] Run tests and confirm they pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Items Search API' (Protocol in workflow.md)

## Phase 2: Customers API

- [ ] Task: Create Customers API controller file and register routes
    - [ ] Create `app/Controllers/api/v1/Customers.php` extending ResourceController
    - [ ] Add routes: `$routes->get('customers', 'Customers::index')` and `$routes->post('customers', 'Customers::create')`
    - [ ] Apply `jwtauth` filter to customers routes
- [ ] Task: Write unit tests for customers search and create
    - [ ] Create `tests/Controllers/api/v1/CustomersTest.php`
    - [ ] Test search by name
    - [ ] Test search by phone
    - [ ] Test create customer with valid data
    - [ ] Test create customer with missing required fields (expect 400)
    - [ ] Test tanpa auth (expect 401)
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement Customers::index() — search endpoint
    - [ ] Read `term` and `limit` from query params
    - [ ] Query people + customers join, LIKE on first_name, last_name, phone_number, email
    - [ ] If term kosong, return all customers (paginated)
    - [ ] Return JSON response
- [ ] Task: Implement Customers::create() — create endpoint
    - [ ] Read JSON body input
    - [ ] Validate required fields (first_name, last_name)
    - [ ] Call Customer::save_customer()
    - [ ] Return 201 with person_id
- [ ] Task: Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Customers API' (Protocol in workflow.md)

## Phase 3: Payment Types API

- [ ] Task: Register route for payment types (GET /api/v1/sales/payment-types)
    - [ ] Add route in Routes.php inside api/v1 group
    - [ ] Apply `jwtauth` filter
- [ ] Task: Write unit tests for payment types
    - [ ] Create `tests/Controllers/api/v1/PaymentTypesTest.php`
    - [ ] Test returns array of payment types
    - [ ] Test returns 401 without auth
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement payment-types endpoint
    - [ ] Create method in Sales controller or standalone controller
    - [ ] Use helper `get_payment_options()`
    - [ ] Return formatted JSON
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Payment Types API' (Protocol in workflow.md)

## Phase 4: Sales Create API

- [ ] Task: Register route for sales create (POST /api/v1/sales)
    - [ ] Add `$routes->post('sales', 'Sales::create')` in Routes.php
    - [ ] Apply `jwtauth` filter
- [ ] Task: Write unit tests for sales create
    - [ ] Create `tests/Controllers/api/v1/SalesTest.php`
    - [ ] Test create sale with valid items and payments
    - [ ] Test create sale with insufficient stock (expect 400)
    - [ ] Test create sale with empty cart (expect 400)
    - [ ] Test create sale without auth (expect 401)
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement Sales::create() endpoint
    - [ ] Read JSON body: customer_id, employee_id, comment, sale_location, items[], payments[]
    - [ ] Validate: items tidak kosong, payments tidak kosong
    - [ ] **Stock validation**: loop items, cek quantity tersedia di location, jika kurang return error
    - [ ] Load Sale_lib, set customer, mode, location
    - [ ] Add items to cart via Sale_lib::add_item()
    - [ ] Calculate taxes via Tax_lib
    - [ ] Save via Sale::save_value()
    - [ ] Clear cart, return sale_id + total
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Sales Create API' (Protocol in workflow.md)

## Phase 5: Sales Detail API

- [ ] Task: Register route for sales detail (GET /api/v1/sales/{id})
    - [ ] Add `$routes->get('sales/(:num)', 'Sales::detail/$1')` in Routes.php
- [ ] Task: Write unit tests for sales detail
    - [ ] Test get existing sale detail
    - [ ] Test get non-existent sale (expect 404)
    - [ ] Test without auth (expect 401)
    - [ ] Run tests and confirm they fail (Red phase)
- [ ] Task: Implement sales detail endpoint
    - [ ] Read sale_id from URL segment
    - [ ] Validasi sale exists via Sale::exists()
    - [ ] Query sale info, items, payments via Sale model
    - [ ] Return formatted JSON
    - [ ] Run tests and confirm they pass (Green phase)
- [ ] Task: Conductor - User Manual Verification 'Sales Detail API' (Protocol in workflow.md)

## Phase 6: Integration & Final Testing

- [ ] Task: Run full test suite and fix any issues
- [ ] Task: Verify all API endpoints with curl end-to-end
- [ ] Task: Update documentation if needed
- [ ] Task: Conductor - User Manual Verification 'Integration & Final Testing' (Protocol in workflow.md)
