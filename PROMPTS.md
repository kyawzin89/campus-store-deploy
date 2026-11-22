### Entry 1
* **Date:** 2026-11-11
* **Tool:** Gemini
* **Prompt:** "Analyze the IT341 final project specifications and help me design a streamlined Product Requirements Document (PRD) for a Campus Convenience Store Inventory and Point-of-Sale (POS) application. Ensure the architecture strictly satisfies baseline requirements: 5 relational entities, one 1:N relationship, one M:N relationship with extra pivot attributes, two server-enforced transactional business rules, Sanctum token authentication, and a three-screen React frontend."
* **AI Output:** Structured the PRD across 10 sections adhering to the course starter template: defined user personas (Cashier and Admin), detailed 10 user stories with Gherkin-style acceptance criteria, specified 2 atomic business rules (stock deduction guard and policy-based catalog protection), mapped out an 11-endpoint REST API schema, and scoped a minimal React interface.
* **Hand Changes:** Tailored the problem statement to our campus kiosk workflow, reduced the endpoint count to eliminate out-of-scope complexity, verified that the `order_items` pivot carries necessary snapshot pricing (`unit_price`, `subtotal`), and confirmed that all level-ups remain deferred to prioritize core baseline stability.
* **Commit:** 0e61cd6

### Entry 2
* **Date:** 2026-11-11
* **Tool:** GitHub Copilot (Inline)
* **Prompt:** "Add a string column named 'role' with a default value of 'cashier'"
* **AI Output:** `$table->string('role')->default('cashier');` added to users migration table schema.
* **Hand Changes:** Accepted inline proposal and verified default value enforces the principle of least privilege for new accounts.
* **Commit:** 3be8c74

### Entry 3
* **Date:** 2026-11-11
* **Tool:** GitHub Copilot (Inline) & Gemini
* **Prompt:** "Design the minimal database migration schema for the Categories table to support product classification and 1:N relational integrity, removing leftover template artifacts."
* **AI Output:** Clean schema definition using `Blueprint` with unique constraints on category names and soft rollback handling.
* **Hand Changes:** Verified field types, ensured single source of truth for POS catalog filtering, and purged unnecessary template boilerplate.
* **Commit:** 0ab7142

### Entry 4
* **Date:** 2026-11-11
* **Tool:** Gemini
* **Prompt:** "Generate the database migration schema for the Products table supporting 1:N category relationship with restrictOnDelete constraint, decimal price precision, non-negative stock counts, and soft delete capabilities."
* **AI Output:** Migration blueprint containing `foreignId('category_id')`, `decimal('price', 10, 2)`, `unsignedInteger('stock_quantity')`, and `softDeletes()` with descriptive inline comments.
* **Hand Changes:** Verified foreign key references match categories table naming, confirmed decimal precision avoids floating-point issues, and enabled soft deletes to preserve sales receipt integrity.
* **Commit:** b3d2ee4

### Entry 5
* **Date:** 2026-11-19
* **Tool:** Gemini
* **Prompt:** "Design the database migration schema for the Orders table linking each sale to the responsible cashier (`user_id`), storing exact `total_amount` in decimal format, and tracking order status."
* **AI Output:** Laravel migration blueprint defining `foreignId('user_id')` with `restrictOnDelete()`, `decimal('total_amount', 10, 2)`, and `string('status')->default('completed')`.
* **Hand Changes:** Verified foreign key references `users` table, added descriptive Burmese inline comments, and confirmed status defaults align with instant POS checkout workflow.
* **Commit:** c3238dd


### Entry 6
* **Date:** 2026-11-22
* **Tool:** Gemini
* **Prompt:** "Expand PRD.md Section 5 with Mermaid ERD and System Architecture diagrams, complete column/constraint specifications for all 5 entities, and concrete Level-Up selections in Section 8."
* **AI Output:** Mermaid `erDiagram` and `flowchart LR` blocks, constraint summaries (`restrictOnDelete`, `cascadeOnDelete`, `softDeletes`, `decimal(10,2)`), and 3 scoped level-up features.
* **Hand Changes:** Verified every diagram column matches existing Laravel migrations and aligned level-up targets with Week 2 API endpoints.
* **Commit:** 850ed01

### Entry 7
* **Date:** 2026-11-22
* **Tool:** Gemini
* **Prompt:** "Create the database migration schema for the `order_items` pivot table linking `orders` (with cascade delete) and `products` (with restrict delete), capturing `quantity` and historical `unit_price` snapshot."
* **AI Output:** Migration schema defining `order_id` and `product_id` foreign keys, `unsignedInteger('quantity')`, and `decimal('unit_price', 10, 2)` with inline comments.
* **Hand Changes:** Added closing class bracket, added Burmese inline comments, and verified full schema build via `php artisan migrate:fresh`.
* **Commit:** 6e4c9e5

### Entry 8
* **Date:** 2026-11-22
* **Tool:** Gemini
* **Prompt:** "Configure all 5 Eloquent models (User, Category, Product, Order, OrderItem) with fillable attributes, decimal/integer casts, SoftDeletes on Product, Sanctum HasApiTokens on User, and full 1:N and M:N relationships."
* **AI Output:** Complete Eloquent model definitions implementing `hasMany`, `belongsTo`, and `belongsToMany` with pivot attributes (`quantity`, `unit_price`).
* **Hand Changes:** Reviewed model relationships and casts against database migrations.
* **Commit:** 625d13f

### Entry 9
* **Date:** 2026-11-22
* **Tool:** Gemini
* **Prompt:** "Create an idempotent DatabaseSeeder with Admin and Cashier user accounts, 4 campus store categories, and 12 products including low-stock items (<= 5 units) for threshold and overselling testing."
* **AI Output:** `DatabaseSeeder` using `updateOrCreate` and `firstOrCreate` to populate staff logins, categories, and 12 realistic campus store products.
* **Hand Changes:** Verified fresh database build and seeding via `php artisan migrate:fresh --seed`.
* **Commit:** 3c1c5ad


### Entry 10
* **Date:** 2026-11-22
* **Tool:** Gemini
* **Prompt:** "Implement Sanctum AuthController (login, me, logout) and EnsureUserIsAdmin RBAC middleware, and register the 'admin' middleware alias in bootstrap/app.php."
* **AI Output:** Token-based authentication controller and role-checking middleware returning 403 Forbidden for non-admin users.
* **Hand Changes:** Registered `admin` middleware alias in `bootstrap/app.php`.
* **Commit:** cf20cf7


### Entry 11
* **Date:** 2026-11-22
* **Tool:** Gemini
* **Prompt:** "Implement CategoryController, ProductController (search, category filter, low-stock <= 5 alert, soft deletes), and OrderController (atomic DB::transaction checkout with lockForUpdate overselling protection, unit_price snapshots, and daily sales summary) and register role-protected endpoints in routes/api.php."
* **AI Output:** Complete REST API controllers and Sanctum + Admin-protected routes enforcing stock integrity and RBAC.
* **Hand Changes:** Verified API route registration via `php artisan route:list --path=api`.
* **Commit:** * **Commit:** d61d825




