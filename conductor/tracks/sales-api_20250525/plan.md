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

- [x] Task: Create Customers API controller file and register routes
    - [x] Create `app/Controllers/api/v1/Customers.php` extending ResourceController
    - [x] Add routes: `$routes->get('customers', 'Customers::index')` and `$routes->post('customers', 'Customers::create')`
    - [x] Apply `jwtauth` filter to customers routes
- [x] Task: Write unit tests for customers search and create
    - [x] Create `tests/Controllers/api/v1/CustomersTest.php`
    - [x] Test search by name
    - [x] Test search by phone
    - [x] Test create customer with valid data
    - [x] Test create customer with missing required fields (expect 400)
    - [x] Test tanpa auth (expect 401)
    - [x] Run tests and confirm they fail (Red phase)
- [x] Task: Implement Customers::index() — search endpoint
    - [x] Read `term` and `limit` from query params
    - [x] Query people + customers join, LIKE on first_name, last_name, phone_number, email
    - [x] If term kosong, return all customers (paginated)
    - [x] Return JSON response
- [x] Task: Implement Customers::create() — create endpoint [9943cc5]
    - [x] Read JSON body input
    - [x] Validate required fields (first_name, last_name)
    - [x] Call Customer::save_customer()
    - [x] Return 201 with person_id
- [x] Task: Run tests and confirm they pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Customers API' (Protocol in workflow.md)

## Phase 3: Payment Types API

- [x] Task: Register route for payment types (GET /api/v1/sales/payment-types)
    - [x] Add route in Routes.php inside api/v1 group
    - [x] Apply `jwtauth` filter
- [x] Task: Write unit tests for payment types
    - [x] Create `tests/Controllers/api/v1/SalesTest.php`
    - [x] Test returns array of payment types
    - [x] Test returns 401 without auth
    - [x] Run tests and confirm they fail (Red phase)
- [x] Task: Implement payment-types endpoint [4adb752]
    - [x] Create Sales controller with paymentTypes() method
    - [x] Use helper `get_payment_options()`
    - [x] Return formatted JSON [{id, name}, ...]
    - [x] Run tests and confirm they pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Payment Types API' (Protocol in workflow.md)

## Phase 4: Sales Create API

- [x] Task: Register route for sales create (POST /api/v1/sales)
    - [x] Add `$routes->post('sales', 'Sales::create')` in Routes.php
    - [x] Apply `jwtauth` filter
- [x] Task: Write unit tests for sales create
    - [x] Create tests in `SalesTest.php`
    - [x] Test create sale with valid items and payments
    - [x] Test create sale with insufficient stock (expect 400)
    - [x] Test create sale with empty cart (expect 400)
    - [x] Test create sale without auth (expect 401)
    - [x] Run tests and confirm they fail (Red phase)
- [x] Task: Implement Sales::create() endpoint [36e9c9e]
    - [x] Read JSON body: customer_id, employee_id, comment, sale_location, items[], payments[]
    - [x] Validate: items tidak kosong, payments tidak kosong
    - [x] **Stock validation**: loop items, cek quantity tersedia di location, jika kurang return error
    - [x] Build items array for save_value (line, description, serialnumber, cost_price, price, discount)
    - [x] Build payments array for save_value
    - [x] Save via Sale::save_value()
    - [x] Return sale_id, sale_id_display, sale_time, total, amount_due, item_count
    - [x] Run tests and confirm they pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Sales Create API' (Protocol in workflow.md)

## Phase 5: Sales Detail API

- [x] Task: Register route for sales detail (GET /api/v1/sales/{id})
    - [x] Add `$routes->get('sales/(:num)', 'Sales::show/$1')` in Routes.php
    - [x] Apply `jwtauth` filter (api/v1/sales/*)
- [x] Task: Write unit tests for sales detail
    - [x] Test get existing sale detail
    - [x] Test get non-existent sale (expect 404)
    - [x] Test without auth (expect 401)
    - [x] Run tests and confirm they fail (Red phase)
- [x] Task: Implement sales detail endpoint [98c7de6]
    - [x] Read sale_id from URL segment
    - [x] Validasi sale exists via Sale::get_info()
    - [x] Query sale info, items, payments via Sale model
    - [x] Return formatted JSON with customer, employee, items (subtotal), payments
    - [x] Run tests and confirm they pass (Green phase)
- [x] Task: Conductor - User Manual Verification 'Sales Detail API' (Protocol in workflow.md)

## Phase 6: Integration & Final Testing

- [ ] Task: Run full test suite and fix any issues
- [ ] Task: Verify all API endpoints with curl end-to-end
- [ ] Task: Update documentation if needed
- [ ] Task: Conductor - User Manual Verification 'Integration & Final Testing' (Protocol in workflow.md)
