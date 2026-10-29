<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\Admin\AdminWebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Vendor\VendorStoreController;
use Illuminate\Support\Facades\Route;

// Public Storefront
Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{slug}', [CatalogController::class, 'show'])->name('catalog.show');

// Shopping Cart (Web & AJAX fetch)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/update/{productId}', [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/summary', [CartController::class, 'summary'])->name('summary');
});

// Checkout
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
    Route::get('/confirmation/{orderNumber}', [CheckoutController::class, 'confirmation'])->name('confirmation');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer Panel
Route::prefix('account')->name('customer.')->middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{orderNumber}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
});

// Vendor Panel
Route::prefix('vendor')->name('vendor.')->middleware(['auth', 'role:vendor'])->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/products', [VendorProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [VendorProductController::class, 'create'])->name('products.create');
    Route::post('/products', [VendorProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [VendorProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [VendorProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [VendorProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/items/{item}/status', [VendorOrderController::class, 'updateItemStatus'])->name('orders.update_item');

    Route::get('/store', [VendorStoreController::class, 'edit'])->name('store.edit');
    Route::put('/store', [VendorStoreController::class, 'update'])->name('store.update');
});

// Admin Panel
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::patch('/products/{product}/toggle', [AdminProductController::class, 'toggleActive'])->name('products.toggle');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update_status');

    Route::get('/vendors', [AdminVendorController::class, 'index'])->name('vendors.index');
    Route::patch('/vendors/{store}/toggle', [AdminVendorController::class, 'toggleStatus'])->name('vendors.toggle');

    Route::get('/webhooks', [AdminWebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [AdminWebhookController::class, 'store'])->name('webhooks.store');
    Route::delete('/webhooks/{endpoint}', [AdminWebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{endpoint}/test', [AdminWebhookController::class, 'testDispatch'])->name('webhooks.test');
});
