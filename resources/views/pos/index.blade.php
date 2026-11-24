@extends('layouts.app')

@section('title', 'POS Checkout — Campus Store')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left 2 Columns: Product Catalog -->
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <form method="GET" action="{{ route('pos.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search product name..."
                       class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">

                <select name="category_id"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'category_id']))
                    <a href="{{ route('pos.index') }}"
                       class="text-sm text-slate-500 hover:text-slate-800 px-3 py-2 text-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($products as $product)
                @php
                    $outOfStock = $product->stock_quantity <= 0;
                    $lowStock = $product->stock_quantity > 0 && $product->stock_quantity <= 5;
                @endphp
                <div class="bg-white rounded-xl border {{ $outOfStock ? 'border-slate-200 opacity-60' : ($lowStock ? 'border-amber-300' : 'border-slate-200') }} p-4 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-medium text-slate-400">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                            @if($outOfStock)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-rose-100 text-rose-700">Out of Stock</span>
                            @elseif($lowStock)
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Low: {{ $product->stock_quantity }}</span>
                            @else
                                <span class="text-xs font-medium text-emerald-700">Stock: {{ $product->stock_quantity }}</span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-slate-900 text-sm leading-snug">{{ $product->name }}</h3>
                        <p class="text-lg font-bold text-emerald-600 mt-2">฿{{ number_format($product->price, 2) }}</p>
                    </div>

                    <button type="button"
                            @disabled($outOfStock)
                            onclick="addToCart({{ $product->id }}, @js($product->name), {{ (float) $product->price }}, {{ $product->stock_quantity }})"
                            class="mt-4 w-full py-2 px-3 rounded-lg text-xs font-semibold transition {{ $outOfStock ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                        {{ $outOfStock ? 'Unavailable' : '+ Add to Cart' }}
                    </button>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-xl border border-slate-200 p-8 text-center text-sm text-slate-500">
                    No products matched your filter.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Column: Current Cart & Recent Orders -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                <h2 class="font-bold text-slate-900">Current Order Cart</h2>
                <button type="button" onclick="clearCart()" class="text-xs text-rose-600 hover:underline">
                    Clear
                </button>
            </div>

            <div id="cart-items" class="space-y-3 min-h-[120px]">
                <p class="text-sm text-slate-400 text-center py-8">Click "+ Add to Cart" on any item to begin.</p>
            </div>

            <div class="border-t border-slate-200 mt-4 pt-4 space-y-4">
                <div class="flex items-center justify-between text-base font-bold text-slate-900">
                    <span>Total</span>
                    <span id="cart-total">฿0.00</span>
                </div>

                <form method="POST" action="{{ route('pos.checkout') }}" id="checkout-form" onsubmit="return prepareCheckout()">
                    @csrf
                    <input type="hidden" name="cart_json" id="cart-json">
                    <button type="submit" id="checkout-btn" disabled
                            class="w-full py-2.5 px-4 rounded-lg text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white transition">
                        Complete Checkout
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Receipts -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h3 class="font-bold text-slate-900 text-sm mb-3">Recent Transactions</h3>
            <div class="space-y-2.5">
                @forelse($recentOrders as $order)
                    <div class="text-xs border border-slate-100 rounded-lg p-2.5 bg-slate-50">
                        <div class="flex justify-between font-semibold text-slate-800">
                            <span>Order #{{ $order->id }} ({{ $order->user->name ?? 'Staff' }})</span>
                            <span class="text-emerald-700">฿{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <p class="text-slate-500 mt-1">
                            {{ $order->items->map(fn($i) => ($i->product->name ?? 'Item') . ' x' . $i->quantity)->join(', ') }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-slate-400">No orders recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    let cart = [];

    function addToCart(productId, name, price, maxStock) {
        const existing = cart.find(item => item.product_id === productId);
        if (existing) {
            if (existing.quantity >= maxStock) {
                alert(`Cannot add more than available stock (${maxStock}) for ${name}.`);
                return;
            }
            existing.quantity += 1;
        } else {
            cart.push({ product_id: productId, name, price, quantity: 1, maxStock });
        }
        renderCart();
    }

    function updateQty(productId, delta) {
        const idx = cart.findIndex(item => item.product_id === productId);
        if (idx === -1) return;

        const nextQty = cart[idx].quantity + delta;
        if (nextQty <= 0) {
            cart.splice(idx, 1);
        } else if (nextQty > cart[idx].maxStock) {
            alert(`Only ${cart[idx].maxStock} units available in stock.`);
            return;
        } else {
            cart[idx].quantity = nextQty;
        }
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        const totalEl = document.getElementById('cart-total');
        const btn = document.getElementById('checkout-btn');

        if (cart.length === 0) {
            container.innerHTML = '<p class="text-sm text-slate-400 text-center py-8">Click "+ Add to Cart" on any item to begin.</p>';
            totalEl.textContent = '฿0.00';
            btn.disabled = true;
            return;
        }

        let total = 0;
        container.innerHTML = cart.map(item => {
            const subtotal = item.price * item.quantity;
            total += subtotal;
            return `
                <div class="flex items-center justify-between text-sm bg-slate-50 rounded-lg p-2.5 border border-slate-200">
                    <div class="pr-2">
                        <p class="font-medium text-slate-800">${item.name}</p>
                        <p class="text-xs text-slate-500">฿${item.price.toFixed(2)} each</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" onclick="updateQty(${item.product_id}, -1)" class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold text-xs">-</button>
                        <span class="font-semibold w-5 text-center">${item.quantity}</span>
                        <button type="button" onclick="updateQty(${item.product_id}, 1)" class="w-6 h-6 rounded bg-slate-200 hover:bg-slate-300 font-bold text-xs">+</button>
                        <span class="font-semibold text-slate-900 w-16 text-right">฿${subtotal.toFixed(2)}</span>
                    </div>
                </div>
            `;
        }).join('');

        totalEl.textContent = `฿${total.toFixed(2)}`;
        btn.disabled = false;
    }

    function prepareCheckout() {
        if (cart.length === 0) return false;
        document.getElementById('cart-json').value = JSON.stringify(
            cart.map(({ product_id, quantity }) => ({ product_id, quantity }))
        );
        return true;
    }
</script>
@endsection
