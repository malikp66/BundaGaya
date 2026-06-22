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
    protected $consignor;
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
        $this->consignor = User::factory()->create(['role' => 'consignor']);
        
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $this->product = Product::factory()->create([
            'user_id' => $this->consignor->id,
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
            'customer_name' => 'Test Customer',
            'refund_bank_name' => 'BCA',
            'refund_bank_account' => '1234567890',
            'refund_bank_holder' => 'Test Customer',
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
            'consignor_id' => $this->consignor->id,
        ]);
    }

    public function test_cannot_create_order_with_empty_cart()
    {
        $this->expectException(CartEmptyException::class);

        $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
            'customer_name' => 'Test Customer',
            'refund_bank_name' => 'BCA',
            'refund_bank_account' => '1234567890',
            'refund_bank_holder' => 'Test Customer',
        ]);
    }

    public function test_cannot_create_order_with_insufficient_stock()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);

        $this->expectException(InsufficientStockException::class);

        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 10);
    }

    public function test_can_mark_order_as_paid()
    {
        $order = $this->createTestOrder();

        $paidOrder = $this->orderService->markAsPaid($order);

        $this->assertEquals('paid', $paidOrder->status);
        $this->assertEquals('paid', $paidOrder->payment_status);
        $this->assertNotNull($paidOrder->paid_at);
    }

    public function test_can_cancel_order()
    {
        $order = $this->createTestOrder();

        $cancelledOrder = $this->orderService->cancel($order, 'Customer request');

        $this->assertEquals('cancelled', $cancelledOrder->status);
        $this->assertNotNull($cancelledOrder->cancelled_at);
        $this->assertStringContainsString('Customer request', $cancelledOrder->notes);
    }

    public function test_order_calculates_admin_fee_correctly()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 1);

        $order = $this->orderService->createFromCart($this->user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
            'customer_name' => 'Test Customer',
            'refund_bank_name' => 'BCA',
            'refund_bank_account' => '1234567890',
            'refund_bank_holder' => 'Test Customer',
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
            'customer_name' => 'Test Customer',
            'refund_bank_name' => 'BCA',
            'refund_bank_account' => '1234567890',
            'refund_bank_holder' => 'Test Customer',
        ]);
    }
}
