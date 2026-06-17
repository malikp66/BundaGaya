<?php

namespace Tests\Unit\Services;

use App\Exceptions\CartEmptyException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidOrderStatusException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;
    protected $cartService;
    protected $user;
    protected $shop;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->orderService = app(OrderService::class);
        $this->cartService = app(CartService::class);
        
        Setting::create([
            'key' => 'admin_fee',
            'value' => '5000',
            'type' => 'integer',
        ]);
        
        $this->user = User::factory()->create();
        $this->shop = Shop::factory()->create([
            'user_id' => User::factory(),
            'status' => 'active',
            'commission_rate' => 10,
        ]);
        
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $this->product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price_per_day' => 100000,
            'stock' => 5,
            'status' => 'active',
        ]);
    }

    public function test_can_create_order_from_cart()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 1);

        $order = $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'admin_fee' => 5000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shop->id,
        ]);

        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'shop_id' => $this->shop->id,
        ]);
    }

    public function test_cannot_create_order_with_empty_cart()
    {
        $this->expectException(CartEmptyException::class);

        $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 10);

        $this->expectException(InsufficientStockException::class);

        $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);
    }

    public function test_can_mark_order_as_paid()
    {
        $order = $this->createTestOrder();

        $paidOrder = $this->orderService->markAsPaid($order);

        $this->assertEquals('paid', $paidOrder->status);
        $this->assertEquals('paid', $paidOrder->payment_status);
        $this->assertNotNull($paidOrder->paid_at);
    }

    public function test_can_confirm_order_by_owner()
    {
        $order = $this->createTestOrder();
        $this->orderService->markAsPaid($order);

        $confirmedOrder = $this->orderService->confirmByOwner($order);

        $this->assertEquals('confirmed_by_owner', $confirmedOrder->status);
        $this->assertNotNull($confirmedOrder->confirmed_at);
        
        $this->product->refresh();
        $this->assertEquals(4, $this->product->stock); // Stock decreased by 1
    }

    public function test_cannot_confirm_without_payment()
    {
        $order = $this->createTestOrder();

        $this->expectException(InvalidOrderStatusException::class);

        $this->orderService->confirmByOwner($order);
    }

    public function test_can_mark_as_picked_up()
    {
        $order = $this->createTestOrder();
        $this->orderService->markAsPaid($order);
        $this->orderService->confirmByOwner($order);

        $pickedUpOrder = $this->orderService->markAsPickedUp($order);

        $this->assertEquals('picked_up', $pickedUpOrder->status);
    }

    public function test_can_mark_as_returned()
    {
        $order = $this->createTestOrder();
        $this->orderService->markAsPaid($order);
        $this->orderService->confirmByOwner($order);
        $this->orderService->markAsPickedUp($order);

        $returnedOrder = $this->orderService->markAsReturned($order);

        $this->assertEquals('returned', $returnedOrder->status);
        
        $this->product->refresh();
        $this->assertEquals(5, $this->product->stock); // Stock restored
        $this->assertEquals(1, $this->product->rental_count); // Rental count increased
    }

    public function test_can_complete_order()
    {
        $order = $this->createTestOrder();
        $this->orderService->markAsPaid($order);
        $this->orderService->confirmByOwner($order);
        $this->orderService->markAsPickedUp($order);
        $this->orderService->markAsReturned($order);

        $completedOrder = $this->orderService->complete($order);

        $this->assertEquals('completed', $completedOrder->status);
        $this->assertNotNull($completedOrder->completed_at);
        
        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'status' => 'settled',
        ]);
    }

    public function test_can_cancel_order()
    {
        $order = $this->createTestOrder();

        $cancelledOrder = $this->orderService->cancel($order, 'Customer request');

        $this->assertEquals('cancelled', $cancelledOrder->status);
        $this->assertNotNull($cancelledOrder->cancelled_at);
        $this->assertStringContainsString('Customer request', $cancelledOrder->notes);
    }

    public function test_cannot_cancel_completed_order()
    {
        $order = $this->createTestOrder();
        $this->orderService->markAsPaid($order);
        $this->orderService->confirmByOwner($order);
        $this->orderService->markAsPickedUp($order);
        $this->orderService->markAsReturned($order);
        $this->orderService->complete($order);

        $this->expectException(InvalidOrderStatusException::class);

        $this->orderService->cancel($order);
    }

    public function test_order_calculates_admin_fee_correctly()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 1);

        $order = $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);

        $subtotal = 300000; // 100000 * 3 days
        $adminFee = 5000;
        
        $this->assertEquals($subtotal, $order->subtotal);
        $this->assertEquals($adminFee, $order->admin_fee);
        $this->assertEquals($subtotal + $adminFee, $order->total);
    }

    protected function createTestOrder(): Order
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 1);

        return $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);
    }
}
