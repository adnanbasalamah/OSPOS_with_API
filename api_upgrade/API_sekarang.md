# Daftar API http://kasir.local

Base URL: `http://kasir.local`
Base URL (public): `http://kasir.local/public`

> Semua REST API (via CodeIgniter) membutuhkan header **`X-API-KEY`** dengan nilai default **`ospos_secret_key_123`**.

---

## A. REST API (CodeIgniter)

### 1. CREATE ITEM
**Endpoint:** `POST http://kasir.local/index.php/api/items/create`

**Body (JSON):**
```json
{
  "item_number": "BRG001",
  "name": "Baju Batik",
  "category": "Pakaian",
  "cost_price": 50000,
  "unit_price": 75000,
  "quantity": 10,
  "reorder_level": 5,
  "supplier_id": 1,
  "description": "Baju batik lengan panjang"
}
```

**cURL:**
```bash
curl -X POST http://kasir.local/index.php/api/items/create \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: ospos_secret_key_123" \
  -d '{"item_number":"BRG001","name":"Baju Batik","category":"Pakaian","cost_price":50000,"unit_price":75000,"quantity":10,"reorder_level":5,"supplier_id":1,"description":"Baju batik lengan panjang"}'
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "message": "Item created successfully",
    "item_id": 123,
    "item_number": "BRG001",
    "initial_quantity": 10
  }
}
```
**HTTP 201**

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Item number, name, and category are required."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_DUPLICATE_ITEM_NUMBER",
    "message": "Item number BRG001 already exists."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 2. GET SUPPLIERS
**Endpoint:** `GET http://kasir.local/index.php/api/items/suppliers?search=bayu`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api/items/suppliers?search=bayu"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Bayu Group"
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 3. GET CATEGORIES
**Endpoint:** `GET http://kasir.local/index.php/api/items/categories?search=Pak`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api/items/categories?search=Pak"
```

**Output Benar:**
```json
{
  "success": true,
  "data": ["Pakaian", "Paket Hemat"]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 4. RECEIVINGS INDEX (test auth)
**Endpoint:** `GET http://kasir.local/index.php/api_receivings/index`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_receivings/index"
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "message": "Receivings API is active"
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 5. SEARCH ITEM (Receivings)
**Endpoint:** `GET http://kasir.local/index.php/api_receivings/search_item?term=baju&location_id=1`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_receivings/search_item?term=baju&location_id=1"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "item_id": 1,
      "name": "Baju Batik",
      "item_number": "BRG001",
      "cost_price": 50000,
      "unit_price": 75000,
      "category": "Pakaian",
      "description": "Baju batik lengan panjang",
      "supplier_id": 1,
      "supplier_name": "Bayu Group",
      "quantity": 10
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_MISSING_TERM",
    "message": "Parameter term wajib diisi."
  }
}
```

---

### 6. GET SUPPLIERS (Receivings)
**Endpoint:** `GET http://kasir.local/index.php/api_receivings/suppliers`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_receivings/suppliers"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "person_id": 1,
      "company_name": "Bayu Group",
      "first_name": "Budi",
      "last_name": "Santoso"
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 7. GET STOCK LOCATIONS
**Endpoint:** `GET http://kasir.local/index.php/api_receivings/stock_locations`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_receivings/stock_locations"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "location_id": 1,
      "location_name": "Gudang Utama"
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 8. COMPLETE RECEIVING (Penerimaan Barang)
**Endpoint:** `POST http://kasir.local/index.php/api_receivings/complete`

**Body (JSON):**
```json
{
  "items": [
    {
      "item_id": 1,
      "quantity": 20,
      "cost_price": 45000,
      "retail_price": 75000,
      "discount": 0
    }
  ],
  "supplier_id": 1,
  "employee_id": 1,
  "comment": "Penerimaan via Android API",
  "reference": "PO-001",
  "payment_type": "Cash",
  "stock_location": "1"
}
```

**cURL:**
```bash
curl -X POST http://kasir.local/index.php/api_receivings/complete \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: ospos_secret_key_123" \
  -d '{"items":[{"item_id":1,"quantity":20,"cost_price":45000,"retail_price":75000,"discount":0}],"supplier_id":1,"employee_id":1,"comment":"Penerimaan via Android API","reference":"PO-001","payment_type":"Cash","stock_location":"1"}'
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "receiving_id": "RECV 1",
    "message": "Penerimaan barang berhasil disimpan."
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_EMPTY_CART",
    "message": "Daftar barang tidak boleh kosong."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_INVALID_JSON",
    "message": "Format JSON tidak valid atau body request kosong."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_INVALID_PRICE_COMPARISON",
    "message": "Harga jual untuk 'Baju Batik' (75000) harus lebih besar dari harga modal (45000)."
  }
}
```

---

### 9. GET PAYMENT TYPES (Sales)
**Endpoint:** `GET http://kasir.local/index.php/api_sales/payment_types`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_sales/payment_types"
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "Cash": "Cash",
    "Check": "Check",
    "Debit Card": "Debit Card",
    "Credit Card": "Credit Card"
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 10. GET RECEIPT (Detail Penjualan)
**Endpoint:** `GET http://kasir.local/index.php/api_sales/receipt/1`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_sales/receipt/1"
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "sale_info": {
      "sale_id": "POS 1",
      "sale_id_num": 1,
      "customer_id": 1,
      "employee_id": 1,
      "comment": "",
      "sale_time": "2025-05-25 10:00:00",
      "payment_type": "Cash"
    },
    "items": [
      {
        "item_id": 1,
        "name": "Baju Batik",
        "quantity_purchased": 2,
        "unit_price": 75000
      }
    ],
    "payments": [
      {
        "payment_type": "Cash",
        "payment_amount": 150000
      }
    ],
    "taxes": []
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_SALE_NOT_FOUND",
    "message": "Transaksi tidak ditemukan."
  }
}
```

---

### 11. COMPLETE SALE (Checkout Penjualan)
**Endpoint:** `POST http://kasir.local/index.php/api_sales/complete`

**Body (JSON):**
```json
{
  "items": [
    {
      "item_id": 1,
      "quantity": 2,
      "price": 75000,
      "discount": 0,
      "discount_type": 0,
      "description": "",
      "serialnumber": ""
    }
  ],
  "payments": [
    {
      "payment_type": "Cash",
      "payment_amount": 150000,
      "cash_refund": 0,
      "cash_adjustment": 0
    }
  ],
  "customer_id": 1,
  "employee_id": 1,
  "comment": "Penjualan via Android API",
  "sale_location": 1
}
```

**cURL:**
```bash
curl -X POST http://kasir.local/index.php/api_sales/complete \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: ospos_secret_key_123" \
  -d '{"items":[{"item_id":1,"quantity":2,"price":75000,"discount":0,"discount_type":0,"description":"","serialnumber":""}],"payments":[{"payment_type":"Cash","payment_amount":150000,"cash_refund":0,"cash_adjustment":0}],"customer_id":1,"employee_id":1,"comment":"Penjualan via Android API","sale_location":1}'
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "sale_id": "POS 1",
    "sale_id_num": 1,
    "total": 150000,
    "amount_due": 0,
    "message": "Transaksi penjualan berhasil disimpan."
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_EMPTY_CART",
    "message": "Keranjang belanja tidak boleh kosong."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_MISSING_PAYMENTS",
    "message": "Informasi pembayaran wajib diisi."
  }
}
```

---

### 12. SEARCH CUSTOMERS
**Endpoint:** `GET http://kasir.local/index.php/api_customers/search?term=adnan`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_customers/search?term=adnan"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "person_id": 1,
      "first_name": "Adnan",
      "last_name": "Fauzi",
      "email": "adnan@example.com",
      "phone_number": "08123456789"
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 13. GET CUSTOMER INFO
**Endpoint:** `GET http://kasir.local/index.php/api_customers/info/1`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_customers/info/1"
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "person_id": 1,
    "first_name": "Adnan",
    "last_name": "Fauzi",
    "email": "adnan@example.com",
    "phone_number": "08123456789"
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_CUSTOMER_NOT_FOUND",
    "message": "Pelanggan tidak ditemukan."
  }
}
```

---

### 14. CREATE CUSTOMER
**Endpoint:** `POST http://kasir.local/index.php/api_customers/create`

**Body (JSON):**
```json
{
  "first_name": "Ahmad",
  "last_name": "Rizki",
  "gender": 1,
  "email": "ahmad@example.com",
  "phone_number": "08123456788",
  "address_1": "Jl. Merdeka No.1",
  "city": "Bandung",
  "company_name": "PT ABC",
  "account_number": "CUST001",
  "tax_id": "1234567890",
  "taxable": 1,
  "discount": 0,
  "discount_type": 0
}
```

**cURL:**
```bash
curl -X POST http://kasir.local/index.php/api_customers/create \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: ospos_secret_key_123" \
  -d '{"first_name":"Ahmad","last_name":"Rizki","gender":1,"email":"ahmad@example.com","phone_number":"08123456788","address_1":"Jl. Merdeka No.1","city":"Bandung","company_name":"PT ABC","account_number":"CUST001","tax_id":"1234567890","taxable":1,"discount":0,"discount_type":0}'
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "customer_id": 2,
    "message": "Pelanggan berhasil ditambahkan."
  }
}
```
**HTTP 201**

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_VALIDATION_FAILED",
    "message": "Nama depan dan belakang wajib diisi."
  }
}
```

---

### 15. GET STOCK BY SKU
**Endpoint:** `GET http://kasir.local/index.php/api_stock/get_by_sku/BRG001`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_stock/get_by_sku/BRG001"
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "item_id": 1,
    "name": "Baju Batik",
    "item_number": "BRG001",
    "reorder_level": 5,
    "total_stock": 30,
    "stock_locations": [
      {
        "location_id": 1,
        "location_name": "Gudang Utama",
        "quantity": 30
      }
    ]
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_MISSING_SKU",
    "message": "SKU tidak boleh kosong."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_ITEM_NOT_FOUND",
    "message": "Barang dengan SKU BRG001 tidak ditemukan."
  }
}
```

---

### 16. GET OUT OF STOCK ITEMS
**Endpoint:** `GET http://kasir.local/index.php/api_stock/out_of_stock`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_stock/out_of_stock"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "item_id": 5,
      "name": "Sabun Mandi",
      "item_number": "SBN001",
      "quantity": 0,
      "location_name": "Gudang Utama"
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 17. GET BELOW MINIMUM STOCK
**Endpoint:** `GET http://kasir.local/index.php/api_stock/below_minimum?supplier_id=1&category=Pakaian&group_by=supplier`

**cURL:**
```bash
curl -H "X-API-KEY: ospos_secret_key_123" "http://kasir.local/index.php/api_stock/below_minimum?supplier_id=1&category=Pakaian&group_by=supplier"
```

**Output Benar:**
```json
{
  "success": true,
  "data": [
    {
      "item_id": 1,
      "name": "Baju Batik",
      "item_number": "BRG001",
      "category": "Pakaian",
      "supplier_name": "Bayu Group",
      "reorder_level": 5,
      "quantity": 3
    }
  ]
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_AUTH_FAILED",
    "message": "API key tidak valid atau hilang."
  }
}
```

---

### 18. UPDATE STOCK BY SKU
**Endpoint:** `PATCH http://kasir.local/index.php/api_stock/update_stock/BRG001`

**Body (JSON):**
```json
{
  "quantity": 50,
  "location_id": 1
}
```

**cURL:**
```bash
curl -X PATCH http://kasir.local/index.php/api_stock/update_stock/BRG001 \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: ospos_secret_key_123" \
  -d '{"quantity":50,"location_id":1}'
```

**Output Benar:**
```json
{
  "success": true,
  "data": {
    "item_id": 1,
    "sku": "BRG001",
    "location_id": 1,
    "old_quantity": 30,
    "new_quantity": 50
  }
}
```

**Error:**
```json
{
  "success": false,
  "error": {
    "code": "ERR_MISSING_SKU",
    "message": "SKU tidak boleh kosong."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_MISSING_QUANTITY",
    "message": "Quantity harus disertakan."
  }
}
```
```json
{
  "success": false,
  "error": {
    "code": "ERR_ITEM_NOT_FOUND",
    "message": "Barang dengan SKU BRG001 tidak ditemukan."
  }
}
```
