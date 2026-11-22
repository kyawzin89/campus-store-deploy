<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * List orders (Admins see all orders; Cashiers see their own processed orders).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user:id,name,role', 'items.product' => fn ($q) => $q->withTrashed()])
            ->latest();

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json([
            'orders' => $query->get(),
        ]);
    }

    /**
     * Process an atomic POS checkout transaction with strict stock locking.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Merge duplicate product_ids in the cart so stock checks stay exact
        $groupedItems = collect($validated['items'])
            ->groupBy('product_id')
            ->map(fn ($rows, $productId) => [
                'product_id' => (int) $productId,
                'quantity' => $rows->sum('quantity'),
            ])
            ->values();

        $order = DB::transaction(function () use ($request, $groupedItems) {
            $totalAmount = 0;
            $linePayloads = [];

            foreach ($groupedItems as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $product || $product->stock_quantity < $item['quantity']) {
                    $available = $product ? $product->stock_quantity : 0;
                    $name = $product ? $product->name : 'Selected product';

                    throw ValidationException::withMessages([
                        'items' => ["Insufficient stock for {$name}. Available: {$available}, requested: {$item['quantity']}."],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $subtotal = $unitPrice * $item['quantity'];
                $totalAmount += $subtotal;

                $product->decrement('stock_quantity', $item['quantity']);

                $linePayloads[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                ];
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => round($totalAmount, 2),
                'status' => 'completed',
            ]);

            foreach ($linePayloads as $payload) {
                $order->items()->create($payload);
            }

            return $order->load(['user:id,name,role', 'items.product']);
        });

        return response()->json([
            'message' => 'Order completed successfully.',
            'order' => $order,
        ], 201);
    }

    /**
     * Show a single order receipt with line items.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if (! $request->user()->isAdmin() && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'order' => $order->load(['user:id,name,role', 'items.product' => fn ($q) => $q->withTrashed()]),
        ]);
    }

    /**
     * Daily sales analytics summary for Admin dashboard.
     */
    public function summary(): JsonResponse
    {
        $todayOrders = Order::whereDate('created_at', today())
            ->where('status', 'completed');

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(quantity * unit_price) as revenue'))
            ->whereHas('order', fn ($q) => $q->whereDate('created_at', today())->where('status', 'completed'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with(['product' => fn ($q) => $q->withTrashed()])
            ->limit(5)
            ->get();

        return response()->json([
            'date' => today()->toDateString(),
            'total_revenue' => round((float) $todayOrders->sum('total_amount'), 2),
            'total_orders' => $todayOrders->count(),
            'low_stock_count' => Product::where('stock_quantity', '<=', 5)->count(),
            'top_products' => $topProducts,
        ]);
    }
}
