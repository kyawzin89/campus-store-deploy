<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CampusStoreFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_and_receive_sanctum_token(): void
    {
        $cashier = User::factory()->create([
            'email' => 'cashier@campusstore.test',
            'password' => 'password',
            'role' => 'cashier',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $cashier->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_cashier_is_forbidden_from_admin_inventory_endpoints(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Stationery']);

        Sanctum::actingAs($cashier);

        $this->postJson('/api/products', [
            'category_id' => $category->id,
            'name' => 'Blue Gel Pen',
            'price' => 15.00,
            'stock_quantity' => 50,
        ])->assertForbidden();
    }

    public function test_pos_checkout_deducts_stock_and_snapshots_unit_price(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Beverages']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Iced Thai Tea',
            'price' => 35.00,
            'stock_quantity' => 10,
        ]);

        Sanctum::actingAs($cashier);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('order.total_amount', '105.00');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 7,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 35.00,
        ]);

        // Updating product price later must not alter historical order_items unit_price
        $product->update(['price' => 45.00]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'unit_price' => 35.00,
        ]);
    }

    public function test_pos_checkout_blocks_overselling_and_preserves_stock(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Snacks']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Seaweed Crisp',
            'price' => 20.00,
            'stock_quantity' => 2,
        ]);

        Sanctum::actingAs($cashier);

        $response = $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => 2,
        ]);
    }

    public function test_admin_can_filter_low_stock_and_view_daily_sales_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'Supplies']);

        $lowItem = Product::create([
            'category_id' => $category->id,
            'name' => 'A4 Notebook',
            'price' => 40.00,
            'stock_quantity' => 4,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Ruler 30cm',
            'price' => 12.00,
            'stock_quantity' => 25,
        ]);

        Sanctum::actingAs($admin);

        // Ring up 2 notebooks so stock drops to 2 and revenue is 80.00
        $this->postJson('/api/orders', [
            'items' => [
                ['product_id' => $lowItem->id, 'quantity' => 2],
            ],
        ])->assertCreated();

        $this->getJson('/api/products?low_stock=1')
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.name', 'A4 Notebook');

        $this->getJson('/api/sales/summary')
            ->assertOk()
            ->assertJsonPath('total_revenue', 80)
            ->assertJsonPath('total_orders', 1)
            ->assertJsonPath('low_stock_count', 1);
    }
}
