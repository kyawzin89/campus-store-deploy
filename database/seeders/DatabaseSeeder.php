<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with staff accounts, categories, and campus store items.
     */
    public function run(): void
    {
        // 1. Create Store Staff Accounts (1 Admin Manager + 1 Cashier)
        User::updateOrCreate(
            ['email' => 'admin@campusstore.test'],
            [
                'name' => 'Store Manager (Admin)',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'cashier@campusstore.test'],
            [
                'name' => 'Front Counter Cashier',
                'password' => Hash::make('password'),
                'role' => 'cashier',
            ]
        );

        // 2. Create Product Categories
        $beverages = Category::firstOrCreate(['name' => 'Beverages']);
        $snacks = Category::firstOrCreate(['name' => 'Snacks']);
        $instantFood = Category::firstOrCreate(['name' => 'Instant Food']);
        $stationery = Category::firstOrCreate(['name' => 'Stationery']);

        // 3. Create Campus Store Products (includes low-stock items <= 5 for testing)
        $products = [
            // Beverages
            ['category_id' => $beverages->id, 'name' => 'Fresh Milk 250ml', 'price' => 18.00, 'stock_quantity' => 30],
            ['category_id' => $beverages->id, 'name' => 'Iced Matcha Latte', 'price' => 35.00, 'stock_quantity' => 15],
            ['category_id' => $beverages->id, 'name' => 'Mineral Water 600ml', 'price' => 10.00, 'stock_quantity' => 50],
            ['category_id' => $beverages->id, 'name' => 'Cold Brew Coffee', 'price' => 45.00, 'stock_quantity' => 4],

            // Snacks
            ['category_id' => $snacks->id, 'name' => 'Seaweed Crispy Rolls', 'price' => 20.00, 'stock_quantity' => 25],
            ['category_id' => $snacks->id, 'name' => 'Salted Potato Chips', 'price' => 25.00, 'stock_quantity' => 18],
            ['category_id' => $snacks->id, 'name' => 'Chocolate Wafer Bar', 'price' => 15.00, 'stock_quantity' => 3],

            // Instant Food
            ['category_id' => $instantFood->id, 'name' => 'Spicy Cup Noodles', 'price' => 16.00, 'stock_quantity' => 40],
            ['category_id' => $instantFood->id, 'name' => 'Tuna Mayo Onigiri', 'price' => 29.00, 'stock_quantity' => 12],
            ['category_id' => $instantFood->id, 'name' => 'Chicken Ham Sandwich', 'price' => 32.00, 'stock_quantity' => 5],

            // Stationery
            ['category_id' => $stationery->id, 'name' => 'Blue Gel Pen 0.5mm', 'price' => 12.00, 'stock_quantity' => 35],
            ['category_id' => $stationery->id, 'name' => 'A5 Spiral Notebook', 'price' => 28.00, 'stock_quantity' => 20],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
