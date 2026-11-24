<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WebStoreController extends Controller
{
    /**
     * Show the staff login page (or redirect if already signed in).
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('pos.index');
        }

        return view('auth.login');
    }

    /**
     * Handle web session login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('pos.index'));
        }

        return back()
            ->withErrors(['email' => 'Invalid staff email or password.'])
            ->onlyInput('email');
    }

    /**
     * Log out the current staff member.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Render the Cashier/Admin POS Checkout Terminal.
     */
    public function pos(Request $request): View
    {
        $query = Product::with('category')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $products = $query->get();
        $categories = Category::orderBy('name')->get();
        $recentOrders = Order::with(['user', 'items.product' => fn ($q) => $q->withTrashed()])
            ->when(! $request->user()->isAdmin(), fn ($q) => $q->where('user_id', $request->user()->id))
            ->latest()
            ->limit(5)
            ->get();

        return view('pos.index', compact('products', 'categories', 'recentOrders'));
    }

    /**
     * Process a web POS checkout using the exact same atomic transaction + stock lock.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cart_json' => ['required', 'string'],
        ]);

        $rawItems = json_decode($validated['cart_json'], true);

        if (! is_array($rawItems) || count($rawItems) === 0) {
            return back()->with('error', 'Your cart is empty. Add at least one item before checking out.');
        }

        try {
            $order = DB::transaction(function () use ($request, $rawItems) {
                $totalAmount = 0;
                $linePayloads = [];

                foreach ($rawItems as $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 0);

                    if ($qty < 1) {
                        continue;
                    }

                    $product = Product::where('id', $productId)->lockForUpdate()->first();

                    if (! $product || $product->stock_quantity < $qty) {
                        $available = $product ? $product->stock_quantity : 0;
                        $name = $product ? $product->name : 'Unknown item';
                        throw new \RuntimeException("Insufficient stock for {$name} (Available: {$available}, Requested: {$qty}).");
                    }

                    $unitPrice = (float) $product->price;
                    $totalAmount += $unitPrice * $qty;

                    $product->decrement('stock_quantity', $qty);

                    $linePayloads[] = [
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                    ];
                }

                if (empty($linePayloads)) {
                    throw new \RuntimeException('No valid items in cart.');
                }

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total_amount' => round($totalAmount, 2),
                    'status' => 'completed',
                ]);

                foreach ($linePayloads as $payload) {
                    $order->items()->create($payload);
                }

                return $order;
            });

            return redirect()
                ->route('pos.index')
                ->with('success', "Order #{$order->id} completed! Total: ฿" . number_format($order->total_amount, 2));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Render the Admin Inventory & Daily Sales Dashboard.
     */
    public function inventory(Request $request): View
    {
        $query = Product::with('category')->orderBy('stock_quantity');

        if ($request->boolean('low_stock')) {
            $query->where('stock_quantity', '<=', 5);
        }

        $products = $query->get();
        $categories = Category::orderBy('name')->get();
        $lowStockCount = Product::where('stock_quantity', '<=', 5)->count();

        $todayRevenue = (float) Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->sum('total_amount');

        $todayOrdersCount = Order::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(quantity * unit_price) as revenue'))
            ->whereHas('order', fn ($q) => $q->whereDate('created_at', today())->where('status', 'completed'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with(['product' => fn ($q) => $q->withTrashed()])
            ->limit(5)
            ->get();

        return view('admin.inventory', compact(
            'products',
            'categories',
            'lowStockCount',
            'todayRevenue',
            'todayOrdersCount',
            'topProducts'
        ));
    }

    /**
     * Admin: Add a new product.
     */
    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        Product::create($validated);

        return back()->with('success', 'Product added to inventory.');
    }

    /**
     * Admin: Restock or update product price.
     */
    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return back()->with('success', "Updated {$product->name}.");
    }

    /**
     * Admin: Soft-delete (archive) a product.
     */
    public function destroyProduct(Product $product): RedirectResponse
    {
        $product->delete();

        return back()->with('success', "Archived {$product->name} (past receipts preserved).");
    }
}
