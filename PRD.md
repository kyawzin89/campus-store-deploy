# PRD: Campus Convenience Store Inventory & POS System

**Student:** Trust Wesley
**Version:** 1.1, 2026-11-22
**Due:** Day 3 of Week 1

---

## 1. Problem and users

The 2 cashiers and 1 store manager at the campus mini-mart currently track stock counts on paper and calculate checkout totals manually. This leads to slow lines between class breaks, untracked inventory discrepancies, and accidental sales of out-of-stock items. This application provides a headless Laravel REST API and React single-page frontend that automates checkout sales, enforces real-time stock checks, and lets the manager maintain the product catalog.

---

## 2. User roles

| Role | Who they are | What they can do | What they cannot do |
| --- | --- | --- | --- |
| `cashier` | Daily front-counter store staff | Log in, search/filter products, checkout orders, view past receipts | Cannot create, update, or delete products or categories |
| `admin` | Campus store manager | Full system access: add/edit/delete products, manage categories, audit sales | Cannot checkout with negative stock numbers |

---

## 3. User stories

| # | As a... | I want to... | So that... | Priority |
| --- | --- | --- | --- | --- |
| US1 | cashier / admin | Log in with email and password | I receive a secure Sanctum token to use the POS | Must |
| US2 | cashier / admin | Log out | My active session token is revoked | Must |
| US3 | cashier / admin | View all product categories | I can filter items during checkout | Must |
| US4 | cashier / admin | Search, filter, and paginate products | I can quickly find items during checkout | Must |
| US5 | admin | Create a new product with category, price, and stock | New inventory can be added to the store catalog | Must |
| US6 | admin | Update an existing product's price and stock | Inventory numbers and retail prices remain accurate | Must |
| US7 | admin | Soft-delete obsolete products | Retired items vanish from checkout without breaking past receipts | Must |
| US8 | cashier | Create an order with multiple products and quantities | A customer sale is completed and stock is deducted | Must |
| US9 | cashier / admin | View past orders and specific order line items | I can inspect transaction receipts | Must |
| US10 | cashier / admin | Have the server reject orders exceeding available stock | The store prevents negative inventory overselling | Must |

### Acceptance criteria

* **US1:**
  - Given valid credentials, when `POST /api/login` is called, then return 200 with a Sanctum Bearer token and user role.
  - Given invalid credentials, when `POST /api/login` is called, then return 422 with validation errors.
* **US2:**
  - Given an authenticated request, when `POST /api/logout` is called, then revoke the current token and return 200.
* **US3:**
  - Given an authenticated user, when `GET /api/categories` is called, then return 200 with an array of category records.
* **US4:**
  - Given query parameters `?search=milk&category_id=1&page=1`, when `GET /api/products` is called, then return 200 with paginated matching results.
* **US5:**
  - Given an admin token and valid body, when `POST /api/products` is called, then persist product and return 201.
  - Given a cashier token, when `POST /api/products` is called, then return 403 Forbidden.
* **US6:**
  - Given an admin token and valid updates, when `PUT /api/products/{id}` is called, then update record and return 200.
* **US7:**
  - Given an admin token, when `DELETE /api/products/{id}` is called, then set `deleted_at` and return 204 No Content.
* **US8:**
  - Given an array of valid product IDs and quantities with sufficient stock, when `POST /api/orders` is called, then create order, insert order items, deduct stock, and return 201.
* **US9:**
  - Given an authenticated user, when `GET /api/orders/{id}/items` is called, then return 200 with eager-loaded item details and unit prices.
* **US10:**
  - Given an order item where requested quantity exceeds current stock, when `POST /api/orders` is called, then abort transaction, leave stock untouched, and return 422 with a specific error message.

---

## 4. Business rules

1. **Rule 1: Atomic Stock Verification & Deduction**
   - *Logic:* When `POST /api/orders` runs, verify inside a database transaction (`DB::transaction`) that `products.stock_quantity >= requested_quantity` for every item.
   - *Failure:* If any product has insufficient stock, rollback the transaction and return HTTP 422: `{"message": "Insufficient stock for product: [Product Name]. Available: [X], Requested: [Y]"}`.
   - *Success:* If all items are valid, decrement `stock_quantity` on each product and create the order.

2. **Rule 2: Catalog Mutation Role Guard**
   - *Logic:* Only users with `role === 'admin'` are authorized via Laravel Policies to invoke `POST /api/products`, `PUT /api/products/{id}`, and `DELETE /api/products/{id}`.
   - *Failure:* If a cashier attempts any of these mutations, return HTTP 403: `{"message": "This action is unauthorized."}`.

---

## 5. Data model and architecture

### Entity Relationship Diagram (ERD)

```mermaid
erDiagram
    USERS ||--o{ ORDERS : "processes"
    CATEGORIES ||--o{ PRODUCTS : "classifies"
    ORDERS ||--|{ ORDER_ITEMS : "contains"
    PRODUCTS ||--o{ ORDER_ITEMS : "sold_in"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role "admin | cashier (default: cashier)"
        timestamp created_at
        timestamp updated_at
    }

    CATEGORIES {
        bigint id PK
        string name UK
        timestamp created_at
        timestamp updated_at
    }

    PRODUCTS {
        bigint id PK
        bigint category_id FK
        string name
        decimal price "10,2"
        unsignedInteger stock_quantity "default: 0"
        timestamp deleted_at "nullable (soft delete)"
        timestamp created_at
        timestamp updated_at
    }

    ORDERS {
        bigint id PK
        bigint user_id FK
        decimal total_amount "10,2"
        string status "default: completed"
        timestamp created_at
        timestamp updated_at
    }

    ORDER_ITEMS {
        bigint id PK
        bigint order_id FK
        bigint product_id FK
        unsignedInteger quantity
        decimal unit_price "10,2 (historical snapshot)"
        timestamp created_at
        timestamp updated_at
    }
