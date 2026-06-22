<?php

use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\ShippingRateController;
use App\Http\Controllers\PaymentCallbackController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $categories = \App\Models\Category::where('is_active', true)
        ->whereNull('parent_id')
        ->limit(8)
        ->get();

    $featuredProducts = \App\Models\Product::where('status', 'active')
        ->where('is_featured', true)
        ->with(['category', 'brand', 'primaryPhoto'])
        ->limit(8)
        ->get();

    return Inertia::render('Welcome', [
        'laravelVersion' => \Illuminate\Foundation\Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'categories' => $categories,
        'featuredProducts' => $featuredProducts,
        'seo' => [
            'title' => 'BundaGaya - Sewa Baju Kondangan Branded',
            'description' => 'Sewa baju kondangan branded dengan harga terjangkau. Gaun pesta, kebaya modern, tas Myzasac, dan aksesoris lengkap. Mulai Rp 50.000/hari.',
            'og_image' => null,
            'og_type' => 'website',
        ],
    ]);
})->name('welcome');

Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [CustomerProductController::class, 'show'])->name('products.show');

Route::prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'show'])->name('show');
    Route::patch('/', [AccountController::class, 'update'])->name('update');
});

Route::prefix('customer')->name('customer.')->group(function () {
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

    Route::post('/shipping/rates', [ShippingRateController::class, 'getRates'])->name('shipping.rates');
});

Route::get('/sitemap.xml', function () {
    $products = \App\Models\Product::where('status', 'active')
        ->select('slug', 'updated_at')
        ->get();

    $content = '<?xml version="1.0" encoding="UTF-8"?>';
    $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

    $content .= '<url><loc>' . url('/') . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>';
    $content .= '<url><loc>' . route('products.index') . '</loc><changefreq>daily</changefreq><priority>0.9</priority></url>';

    foreach ($products as $product) {
        $content .= '<url>';
        $content .= '<loc>' . route('products.show', $product->slug) . '</loc>';
        $content .= '<lastmod>' . $product->updated_at->toW3cString() . '</lastmod>';
        $content .= '<changefreq>weekly</changefreq>';
        $content .= '<priority>0.8</priority>';
        $content .= '</url>';
    }

    $content .= '</urlset>';

    return response($content, 200)->header('Content-Type', 'application/xml');
});

Route::post('/payment/callback', [PaymentCallbackController::class, 'handle'])->name('payment.callback');
