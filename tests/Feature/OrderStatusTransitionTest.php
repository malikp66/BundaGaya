<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOrderStatusException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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

    private function createOrderWithItems($user, $consignor, $category, $brand)
    {
        $product = Product::factory()->create([
            'user_id' => $consignor->id,
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
            'customer_name' => 'Test Customer',
            'refund_bank_name' => 'BCA',
            'refund_bank_account' => '1234567890',
            'refund_bank_holder' => 'Test Customer',
        ]);
    }

    public function test_valid_status_transitions()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $this->assertEquals('pending_payment', $order->status);

        $orderService->markAsPaid($order);
        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $orderService->markAsProcessing($order);
        $order->refresh();
        $this->assertEquals('processing', $order->status);

        $orderService->markAsShipped($order, 'TRACK001', 'JNE', 'REG');
        $order->refresh();
        $this->assertEquals('shipped', $order->status);

        $orderService->markAsInUse($order);
        $order->refresh();
        $this->assertEquals('in_use', $order->status);

        $orderService->markAsReturned($order);
        $order->refresh();
        $this->assertEquals('returned', $order->status);

        $orderService->completeOrder($order);
        $order->refresh();
        $this->assertEquals('completed', $order->status);
    }

    public function test_cannot_cancel_completed_order()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->markAsProcessing($order);
        $orderService->markAsShipped($order, 'TRACK001', 'JNE', 'REG');
        $orderService->markAsInUse($order);
        $orderService->markAsReturned($order);
        $orderService->completeOrder($order);

        $this->expectException(InvalidOrderStatusException::class);
        $this->expectExceptionMessage('Cannot transition from completed to cancelled');

        $orderService->cancel($order);
    }

    public function test_cannot_confirm_without_payment()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $this->assertEquals('pending_payment', $order->status);

        $this->expectException(InvalidOrderStatusException::class);
        $this->expectExceptionMessage('Cannot transition from pending_payment to processing');

        $orderService->markAsProcessing($order);
    }

    public function test_cannot_skip_status_steps()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $orderService->markAsPaid($order);

        $this->expectException(InvalidOrderStatusException::class);
        $this->expectExceptionMessage('Cannot transition from paid to shipped');

        $orderService->markAsShipped($order, 'TRACK001', 'JNE', 'REG');
    }

    public function test_cannot_mark_in_use_without_shipped()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->markAsProcessing($order);

        $this->expectException(InvalidOrderStatusException::class);

        $orderService->markAsInUse($order);
    }

    public function test_cannot_return_without_pickup()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->markAsProcessing($order);

        $this->expectException(InvalidOrderStatusException::class);

        $orderService->markAsReturned($order);
    }

    public function test_cannot_complete_without_return()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $orderService->markAsPaid($order);
        $orderService->markAsProcessing($order);
        $orderService->markAsShipped($order, 'TRACK001', 'JNE', 'REG');
        $orderService->markAsInUse($order);

        $this->expectException(InvalidOrderStatusException::class);

        $orderService->completeOrder($order);
    }

    public function test_cancel_from_pending_payment()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        
        $orderService->cancel($order, 'Customer request');
        $order->refresh();
        
        $this->assertEquals('cancelled', $order->status);
        $this->assertNotNull($order->cancelled_at);
    }

    public function test_cancel_from_paid()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $consignor = User::factory()->create(['role' => 'consignor']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $orderService = app(OrderService::class);
        
        $order = $this->createOrderWithItems($user, $consignor, $category, $brand);
        $orderService->markAsPaid($order);
        
        $orderService->cancel($order, 'Customer request');
        $order->refresh();
        
        $this->assertEquals('cancelled', $order->status);
    }
}
