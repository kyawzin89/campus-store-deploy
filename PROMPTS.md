### Entry 1
* **Date:** 2026-11-11
* **Tool:** Gemini
* **Prompt:** "Analyze the IT341 final project specifications and help me design a streamlined Product Requirements Document (PRD) for a Campus Convenience Store Inventory and Point-of-Sale (POS) application. Ensure the architecture strictly satisfies baseline requirements: 5 relational entities, one 1:N relationship, one M:N relationship with extra pivot attributes, two server-enforced transactional business rules, Sanctum token authentication, and a three-screen React frontend."
* **AI Output:** Structured the PRD across 10 sections adhering to the course starter template: defined user personas (Cashier and Admin), detailed 10 user stories with Gherkin-style acceptance criteria, specified 2 atomic business rules (stock deduction guard and policy-based catalog protection), mapped out an 11-endpoint REST API schema, and scoped a minimal React interface.
* **Hand Changes:** Tailored the problem statement to our campus kiosk workflow, reduced the endpoint count to eliminate out-of-scope complexity, verified that the `order_items` pivot carries necessary snapshot pricing (`unit_price`, `subtotal`), and confirmed that all level-ups remain deferred to prioritize core baseline stability.
* **Commit:** 

### Entry 2
* **Date:** 2026-11-11
* **Tool:** GitHub Copilot (Inline)
* **Prompt:** "Add a string column named 'role' with a default value of 'cashier'"
* **AI Output:** `$table->string('role')->default('cashier');` added to users migration table schema.
* **Hand Changes:** Accepted inline proposal and verified default value enforces the principle of least privilege for new accounts.
* **Commit:** (pending)
### Entry 3
* **Date:** 2026-11-11
* **Tool:** GitHub Copilot (Inline) & Gemini
* **Prompt:** "Design the minimal database migration schema for the Categories table to support product classification and 1:N relational integrity, removing leftover template artifacts."
* **AI Output:** Clean schema definition using `Blueprint` with unique constraints on category names and soft rollback handling.
* **Hand Changes:** Verified field types, ensured single source of truth for POS catalog filtering, and purged unnecessary template boilerplate.
* **Commit:** (pending)
