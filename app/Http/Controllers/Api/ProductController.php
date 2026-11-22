<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * List products with optional search, category filter, and low-stock filter (<= 5).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->boolean('low_stock')) {
            $query->where('stock_quantity', '<=', 5);
        }

        $products = $query->get();

        return response()->json([
            'products' => $products,
            'low_stock_count' => Product::where('stock_quantity', '<=', 5)->count(),
        ]);
    }

    /**
     * Add a new product to inventory (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $product = Product::create($validated)->load('category');

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    /**
     * Show a single product.
     */
    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'product' => $product->load('category'),
        ]);
    }

    /**
     * Update product details or restock inventory (Admin only).
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh('category'),
        ]);
    }

    /**
     * Soft-delete a product so historical receipts stay intact (Admin only).
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product archived successfully.',
        ]);
    }
}
