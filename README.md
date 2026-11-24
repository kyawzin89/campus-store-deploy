# Campus Store Management System

A Point-of-Sale (POS) and Inventory Management web app built with **Laravel**, **Sanctum**, **Blade + Tailwind CSS**, and **SQLite**.

## Features
* **Admin (`admin`):** Full POS checkout, product CRUD, restocking, soft-delete archiving, low-stock alerts (`<= 5`), and daily sales analytics.
* **Cashier (`cashier`):** POS checkout and order history (blocked from inventory edits).
* **Overselling Protection:** Uses `DB::transaction` and `lockForUpdate` to prevent negative stock and save historical `unit_price` snapshots.

## Quick Setup
1. `composer install`
2. `cp .env.example .env && php artisan key:generate`
3. `touch database/database.sqlite && php artisan migrate --seed`
4. `php artisan serve` -> Open `http://127.0.0.1:8000`

## Demo Accounts
* **Admin:** `admin@campusstore.test` / `password`
* **Cashier:** `cashier@campusstore.test` / `password`

## Run Tests
`php artisan test`
