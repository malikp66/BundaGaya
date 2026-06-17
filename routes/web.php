<?php

use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shop\DashboardController;
use App\Http\Controllers\Shop\OrderController as ShopOrderController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Shop\ShopController;
use App\Http\Controllers\Shop\TransactionController;
use App\Http\Controllers\Shop\WithdrawalController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $categories = \App\Models\Category::where('is_active', true)
        ->whereNull('parent_id')
        ->limit(8)
        ->get();
    
    $featuredProducts = \App\Models\Product::where('status', 'active')
        ->where('is_featured', true)
        ->with(['shop', 'category', 'brand', 'primaryPhoto'])
        ->limit(8)
        ->get();
    
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'categories' => $categories,
        'featuredProducts' => $featuredProducts,
    ]);
});

Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [CustomerProductController::class, 'show'])->name('products.show');
Route::get('/shops/{shop:slug}', [ShopController::class, 'show'])->name('shops.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'customer_or_shop_owner'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{itemId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', 'shop_owner'])->prefix('shop')->name('shop.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
    Route::post('/shop', [ShopController::class, 'store'])->name('shop.store');

    Route::resource('/products', ShopProductController::class);

    Route::get('/orders', [ShopOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [ShopOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [ShopOrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/picked-up', [ShopOrderController::class, 'markPickedUp'])->name('orders.picked-up');
    Route::post('/orders/{order}/returned', [ShopOrderController::class, 'markReturned'])->name('orders.returned');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals.store');
});

Route::post('/payment/callback', [\App\Http\Controllers\PaymentCallbackController::class, 'handle'])->name('payment.callback');

require __DIR__.'/auth.php';
