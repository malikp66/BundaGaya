<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOrderStatusException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function createOrderWithItems($user, $shop, $category, $brand)
    {
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'stock' => 10,
            'status' => 'active',
            'price_per_day' => 100000,
        ]);

        $cartService = app(CartService::class);
        $orderService = app(OrderService::class);
        
        $startDate = Carbon::now()->addDays(7);
        $endDate = Carbon::now()->addDays(9);

        $cartService->addItem($user, $product, $startDate, $endDate, 1);
        
        return $orderService->createFromCart($user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);
    }

    public function test_valid_status_transitions()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $this->assertEquals('pending_payment', $order->status);

        $orderService->markAsPaid($order);
        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $orderService->confirmByOwner($order);
        $order->refresh();
        $this->assertEquals('confirmed_by_owner', $order->status);

        $orderService->markAsPickedUp($order);
        $order->refresh();
        $this->assertEquals('picked_up', $order->status);

        $orderService->markAsReturned($order);
        $order->refresh();
        $this->assertEquals('returned', $order->status);

        $orderService->complete($order);
        $order->refresh();
        $this->assertEquals('completed', $order->status);
    }

    public function test_cannot_cancel_completed_order()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->confirmByOwner($order);
        $orderService->markAsPickedUp($order);
        $orderService->markAsReturned($order);
        $orderService->complete($order);

        $this->expectException(InvalidOrderStatusException::class);
        $this->expectExceptionMessage('Cannot transition from completed to cancelled');

        $orderService->cancel($order);
    }

    public function test_cannot_confirm_without_payment()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $this->assertEquals('pending_payment', $order->status);

        $this->expectException(InvalidOrderStatusException::class);
        $this->expectExceptionMessage('Cannot transition from pending_payment to confirmed_by_owner');

        $orderService->confirmByOwner($order);
    }

    public function test_cannot_skip_status_steps()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $orderService->markAsPaid($order);

        $this->expectException(InvalidOrderStatusException::class);
        $this->expectExceptionMessage('Cannot transition from paid to picked_up');

        $orderService->markAsPickedUp($order);
    }

    public function test_cannot_mark_picked_up_without_confirmation()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $orderService->markAsPaid($order);

        $this->expectException(InvalidOrderStatusException::class);

        $orderService->markAsPickedUp($order);
    }

    public function test_cannot_return_without_pickup()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->confirmByOwner($order);

        $this->expectException(InvalidOrderStatusException::class);

        $orderService->markAsReturned($order);
    }

    public function test_cannot_complete_without_return()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->confirmByOwner($order);
        $orderService->markAsPickedUp($order);

        $this->expectException(InvalidOrderStatusException::class);

        $orderService->complete($order);
    }

    public function test_cancel_from_pending_payment()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        
        $orderService->cancel($order, 'Customer request');
        $order->refresh();
        
        $this->assertEquals('cancelled', $order->status);
        $this->assertNotNull($order->cancelled_at);
    }

    public function test_cancel_from_paid()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $shop, $category, $brand);
        $orderService->markAsPaid($order);
        
        $orderService->cancel($order, 'Customer request');
        $order->refresh();
        
        $this->assertEquals('cancelled', $order->status);
    }
}
