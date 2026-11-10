# PROMPTS.md: AI usage log

**Student:** _your name_
**Project:** _your app's name and one-line description_
**AI tools used:** _e.g. ChatGPT, Claude, Copilot, Cursor_

## Rules (from the assignment)

- One step = one small, working change. Commit after every step.
- At least **40 commits on at least 12 different days** (most projects land between 60 and 100). Tiny commits piled up on the last night do not count.
- Add one entry below for **every** commit, newest at the bottom.
- Copy your prompt **exactly** as you typed it. Do not tidy it up afterwards.
- Steps you wrote yourself are logged too: write `no AI` as the tool.
- "What I changed by hand" is the most important field. If you changed nothing, say so and explain how you checked the code was right.
- Fill in the commit hash after you commit (`git log --oneline -1`).

---

## Entry template (copy this block for each step)

### Step N: _short title_

- **Date:**
- **Tool:**
- **Prompt:**
  ```text
  paste your exact prompt here
  ```
- **What the AI produced:** _one line, e.g. "migration, model and factory for Product"_
- **What I changed by hand and why:**
- **How I checked it works:** _e.g. ran `php artisan test`, tried it in Postman_
- **Commit:** `abc1234`

---

## Example entry

### Step 3: Product model and migration

- **Date:** 2026-11-12
- **Tool:** ChatGPT
- **Prompt:**
  ```text
  In my Laravel 12 API, create a migration, model and factory for Product with
  name (string), sku (unique string), price (decimal 8,2), stock (integer, default 0)
  and a category_id foreign key to categories. A Product belongs to a Category.
  ```
- **What the AI produced:** migration, `Product` model with `belongsTo(Category)`, factory.
- **What I changed by hand and why:** the AI used `float` for price; I changed it to `decimal(8, 2)` because floats round money badly. Added `cascadeOnDelete()` on `category_id`. Added `price` to `$casts` as `decimal:2`.
- **How I checked it works:** `php artisan migrate:fresh --seed`, then checked the table in TablePlus.
- **Commit:** `4f9c2d1`

---

## My log

<!-- Start your entries here. -->
