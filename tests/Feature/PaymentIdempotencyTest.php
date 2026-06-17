<?php

namespace Tests\Feature;

use App\Exceptions\PaymentProcessingException;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    private function createOrderWithPayment($user, $shop, $category, $brand)
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
        
        $order = $orderService->createFromCart($user, [
            'address' => 'Test Address',
            'phone' => '081234567890',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_number' => 'PAY-' . now()->format('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'amount' => $order->total,
            'status' => 'pending',
            'gateway' => 'midtrans',
        ]);

        return [$order, $payment];
    }

    public function test_payment_notification_processed_once()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        [$order, $payment] = $this->createOrderWithPayment($user, $shop, $category, $brand);

        $paymentService = app(PaymentService::class);

        $notification = [
            'order_id' => $order->order_number,
            'transaction_id' => 'trx-123',
            'status_code' => '200',
            'gross_amount' => $order->total,
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', $order->order_number . '200' . $order->total . config('midtrans.server_key', '')),
        ];

        $result1 = $paymentService->handleNotification($notification);
        
        $this->assertEquals('paid', $result1->status);

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $result2 = $paymentService->handleNotification($notification);
        
        $this->assertEquals('paid', $result2->status);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }

    public function test_duplicate_payment_notification_does_not_change_status()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        [$order, $payment] = $this->createOrderWithPayment($user, $shop, $category, $brand);

        $paymentService = app(PaymentService::class);

        $notification = [
            'order_id' => $order->order_number,
            'transaction_id' => 'trx-123',
            'status_code' => '200',
            'gross_amount' => $order->total,
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', $order->order_number . '200' . $order->total . config('midtrans.server_key', '')),
        ];

        $paymentService->handleNotification($notification);
        
        $paidAt = $payment->fresh()->paid_at;

        $paymentService->handleNotification($notification);
        
        $this->assertEquals($paidAt, $payment->fresh()->paid_at);
    }

    public function test_invalid_signature_rejected()
    {
        config(['midtrans.server_key' => 'test-server-key-123']);
        
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        [$order, $payment] = $this->createOrderWithPayment($user, $shop, $category, $brand);

        $paymentService = app(PaymentService::class);

        $notification = [
            'order_id' => $order->order_number,
            'transaction_id' => 'trx-123',
            'status_code' => '200',
            'gross_amount' => $order->total,
            'payment_type' => 'bank_transfer',
            'signature_key' => 'invalid_signature',
        ];

        $this->expectException(PaymentProcessingException::class);
        $this->expectExceptionMessage('Invalid payment notification signature');

        $paymentService->handleNotification($notification);
    }

    public function test_missing_order_id_rejected()
    {
        $paymentService = app(PaymentService::class);

        $notification = [
            'transaction_id' => 'trx-123',
            'status_code' => '200',
            'gross_amount' => '100000',
            'payment_type' => 'bank_transfer',
            'signature_key' => 'some_signature',
        ];

        $this->expectException(PaymentProcessingException::class);
        $this->expectExceptionMessage('Missing order_id in notification');

        $paymentService->handleNotification($notification);
    }

    public function test_nonexistent_order_rejected()
    {
        $paymentService = app(PaymentService::class);

        $notification = [
            'order_id' => 'BG-99999999-NONEXIST',
            'transaction_id' => 'trx-123',
            'status_code' => '200',
            'gross_amount' => '100000',
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', 'BG-99999999-NONEXIST' . '200' . '100000' . config('midtrans.server_key', '')),
        ];

        $this->expectException(PaymentProcessingException::class);
        $this->expectExceptionMessage('Order not found');

        $paymentService->handleNotification($notification);
    }

    public function test_pending_status_update()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        [$order, $payment] = $this->createOrderWithPayment($user, $shop, $category, $brand);

        $paymentService = app(PaymentService::class);

        $notification = [
            'order_id' => $order->order_number,
            'transaction_id' => 'trx-123',
            'status_code' => '202',
            'gross_amount' => $order->total,
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', $order->order_number . '202' . $order->total . config('midtrans.server_key', '')),
        ];

        $result = $paymentService->handleNotification($notification);
        
        $this->assertEquals('pending', $result->status);
    }

    public function test_failed_status_update()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $shop = Shop::factory()->create(['status' => 'active']);
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        [$order, $payment] = $this->createOrderWithPayment($user, $shop, $category, $brand);

        $paymentService = app(PaymentService::class);

        $notification = [
            'order_id' => $order->order_number,
            'transaction_id' => 'trx-123',
            'status_code' => '400',
            'gross_amount' => $order->total,
            'payment_type' => 'bank_transfer',
            'signature_key' => hash('sha512', $order->order_number . '400' . $order->total . config('midtrans.server_key', '')),
        ];

        $result = $paymentService->handleNotification($notification);
        
        $this->assertEquals('failed', $result->status);
        $this->assertNotNull($result->expired_at);
    }
}
