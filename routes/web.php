<?php

use App\Http\Controllers\WebStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [WebStoreController::class, 'showLogin'])->name('login');
Route::post('/login', [WebStoreController::class, 'login'])->name('login.post');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebStoreController::class, 'logout'])->name('logout');

    // POS Terminal (Cashier & Admin)
    Route::get('/pos', [WebStoreController::class, 'pos'])->name('pos.index');
    Route::post('/pos/checkout', [WebStoreController::class, 'checkout'])->name('pos.checkout');

    // Inventory & Sales Dashboard (Admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/inventory', [WebStoreController::class, 'inventory'])->name('admin.inventory');
        Route::post('/inventory/products', [WebStoreController::class, 'storeProduct'])->name('admin.products.store');
        Route::put('/inventory/products/{product}', [WebStoreController::class, 'updateProduct'])->name('admin.products.update');
        Route::delete('/inventory/products/{product}', [WebStoreController::class, 'destroyProduct'])->name('admin.products.destroy');
    });
});
