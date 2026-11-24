@extends('layouts.app')

@section('title', 'Admin Inventory & Sales — Campus Store')

@section('content')
<div class="space-y-6">

    <!-- Top KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Today's Revenue</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">฿{{ number_format($todayRevenue, 2) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Completed Orders Today</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $todayOrdersCount }}</p>
        </div>

        <div class="bg-white rounded-xl border {{ $lowStockCount > 0 ? 'border-amber-300 bg-amber-50/40' : 'border-slate-200' }} p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Low Stock Alerts (&le; 5)</p>
                <p class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">{{ $lowStockCount }}</p>
            </div>
            @if(request()->boolean('low_stock'))
                <a href="{{ route('admin.inventory') }}" class="text-xs font-semibold text-slate-700 underline">Show All</a>
            @else
                <a href="{{ route('admin.inventory', ['low_stock' => 1]) }}" class="text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg">Filter Low Stock</a>
            @endif
        </div>
    </div>

    <!-- Add New Product & Top Sellers -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h2 class="font-bold text-slate-900 mb-4">Add New Store Product</h2>
            <form method="POST" action="{{ route('admin.products.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                @csrf
                <input type="text" name="name" placeholder="Product Name" required
                       class="sm:col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">

                <select name="category_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Category...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <input type="number" step="0.01" min="0" name="price" placeholder="Price (฿)" required
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm">

                <input type="number" min="0" name="stock_quantity" placeholder="Initial Stock" required
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm">

                <button type="submit"
                        class="sm:col-span-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition">
                    + Add Product to Catalog
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <h2 class="font-bold text-slate-900 mb-3">Top Sellers Today</h2>
            <div class="space-y-2">
                @forelse($topProducts as $item)
                    <div class="flex items-center justify-between text-sm border-b border-slate-100 pb-2">
                        <div>
                            <p class="font-medium text-slate-800">{{ $item->product->name ?? 'Archived Item' }}</p>
                            <p class="text-xs text-slate-400">{{ $item->total_sold }} units sold</p>
                        </div>
                        <span class="font-semibold text-emerald-700">฿{{ number_format($item->revenue, 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4">No sales completed today yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Inventory Management Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <h2 class="font-bold text-slate-900">
                {{ request()->boolean('low_stock') ? 'Low-Stock Products (5 or fewer)' : 'Full Product Inventory' }}
            </h2>
            <span class="text-xs text-slate-500">{{ $products->count() }} items</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Price (฿) &amp; Stock Update</th>
                        <th class="px-5 py-3 text-right">Archive</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($products as $product)
                        <tr class="{{ $product->stock_quantity <= 5 ? 'bg-amber-50/50' : '' }}">
                            <td class="px-5 py-3 font-medium text-slate-900">
                                {{ $product->name }}
                                @if($product->stock_quantity <= 5)
                                    <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Low Stock</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $product->category->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('admin.products.update', $product) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="number" step="0.01" min="0" name="price" value="{{ $product->price }}"
                                           class="w-24 rounded border border-slate-300 px-2 py-1 text-xs">
                                    <input type="number" min="0" name="stock_quantity" value="{{ $product->stock_quantity }}"
                                           class="w-20 rounded border border-slate-300 px-2 py-1 text-xs">
                                    <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-medium px-3 py-1 rounded">
                                        Save
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                      onsubmit="return confirm('Archive {{ $product->name }}? Past order receipts will stay intact.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 hover:underline font-medium">
                                        Archive
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
