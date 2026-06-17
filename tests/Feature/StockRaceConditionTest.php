<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\CartService;
use App\Services\CommissionService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockRaceConditionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_stock_validation_prevents_negative_stock()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'stock' => 1,
            'status' => 'active',
            'price_per_day' => 100000,
        ]);

        $cartService = app(CartService::class);
        
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $cartService->addItem($user, $product, $startDate, $endDate, 1);

        $this->expectException(InsufficientStockException::class);
        $this->expectExceptionMessage('Insufficient stock');

        $cartService->addItem($user, $product, $startDate, $endDate, 1);
    }

    public function test_stock_decrement_with_locking()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'stock' => 2,
            'status' => 'active',
            'price_per_day' => 100000,
        ]);

        $cartService = app(CartService::class);
        $orderService = app(OrderService::class);
        
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $cartService->addItem($user, $product, $startDate, $endDate, 1);
        
        $order = $orderService->createFromCart($user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);

        $orderService->markAsPaid($order);
        $orderService->confirmByOwner($order);

        $product->refresh();
        $this->assertEquals(1, $product->stock);
    }

    public function test_insufficient_stock_exception_on_confirm()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'stock' => 1,
            'status' => 'active',
            'price_per_day' => 100000,
        ]);

        $cartService = app(CartService::class);
        $orderService = app(OrderService::class);
        
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $cartService->addItem($user, $product, $startDate, $endDate, 1);
        
        $order = $orderService->createFromCart($user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);

        $orderService->markAsPaid($order);

        $product->update(['stock' => 0]);

        $this->expectException(InsufficientStockException::class);
        $orderService->confirmByOwner($order);
    }
}
