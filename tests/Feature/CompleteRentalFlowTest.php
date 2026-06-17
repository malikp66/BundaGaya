<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteRentalFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $shopOwner;
    protected $shop;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        Setting::create([
            'key' => 'admin_fee',
            'value' => '5000',
            'type' => 'integer',
        ]);
        
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->shopOwner = User::factory()->create(['role' => 'shop_owner']);
        $this->shop = Shop::factory()->create([
            'user_id' => $this->shopOwner->id,
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

    public function test_complete_rental_flow()
    {
        // Step 1: Customer adds product to cart
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $response = $this->actingAs($this->customer)
            ->post(route('customer.cart.add'), [
                'product_id' => $this->product->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'quantity' => 1,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('cart_items', 1);

        // Step 2: Customer checks out
        $response = $this->actingAs($this->customer)
            ->post(route('customer.checkout'), [
                'address' => 'Customer Address',
                'phone' => '081234567890',
                'notes' => 'Please deliver on time',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'status' => 'pending_payment',
        ]);
        
        $order = \App\Models\Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $this->assertDatabaseCount('cart_items', 0); // Cart cleared

        // Step 3: Payment is made (simulated)
        $orderService = app(\App\Services\OrderService::class);
        $orderService->markAsPaid($order);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
            'payment_status' => 'paid',
        ]);

        // Step 4: Shop owner confirms order
        $response = $this->actingAs($this->shopOwner)
            ->post(route('shop.orders.confirm', $order));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed_by_owner',
        ]);
        
        $this->product->refresh();
        $this->assertEquals(4, $this->product->stock); // Stock decreased

        // Step 5: Customer picks up the product
        $response = $this->actingAs($this->shopOwner)
            ->post(route('shop.orders.picked-up', $order));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'picked_up',
        ]);

        // Step 6: Customer returns the product
        $response = $this->actingAs($this->shopOwner)
            ->post(route('shop.orders.returned', $order));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'returned',
        ]);
        
        $this->product->refresh();
        $this->assertEquals(5, $this->product->stock); // Stock restored
        $this->assertEquals(1, $this->product->rental_count);

        // Step 7: Order is completed
        $orderService->complete($order);
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'status' => 'settled',
        ]);

        // Step 8: Customer leaves a review
        $response = $this->actingAs($this->customer)
            ->post(route('customer.reviews.store', $order), [
                'product_id' => $this->product->id,
                'rating' => 5,
                'comment' => 'Excellent product!',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'rating' => 5,
        ]);

        // Verify final state
        $this->product->refresh();
        $this->assertEquals(5, $this->product->rating_average);
        $this->assertEquals(1, $this->product->rating_count);
    }

    public function test_order_cancellation_flow()
    {
        // Step 1: Customer adds to cart and checks out
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $this->actingAs($this->customer)
            ->post(route('customer.cart.add'), [
                'product_id' => $this->product->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'quantity' => 1,
            ]);

        $this->actingAs($this->customer)
            ->post(route('customer.checkout'), [
                'address' => 'Customer Address',
                'phone' => '081234567890',
            ]);

        $order = \App\Models\Order::where('user_id', $this->customer->id)->first();

        // Step 2: Customer cancels order
        $response = $this->actingAs($this->customer)
            ->post(route('customer.orders.cancel', $order), [
                'reason' => 'Changed my mind',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        
        $this->assertDatabaseHas('transactions', [
            'order_id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_shop_owner_withdrawal_flow()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Setup: Create completed order with transaction
        $order = \App\Models\Order::factory()->create([
            'user_id' => $this->customer->id,
            'status' => 'completed',
        ]);
        
        $orderItem = \App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'shop_id' => $this->shop->id,
            'subtotal' => 300000,
            'commission_fee' => 30000,
            'net_amount' => 270000,
        ]);
        
        \App\Models\Transaction::factory()->create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'shop_id' => $this->shop->id,
            'amount' => 300000,
            'commission_fee' => 30000,
            'net_amount' => 270000,
            'status' => 'settled',
        ]);

        // Step 1: Shop owner requests withdrawal
        $response = $this->actingAs($this->shopOwner)
            ->post(route('shop.withdrawals.store'), [
                'amount' => 200000,
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'account_holder' => $this->shopOwner->name,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('withdrawals', [
            'shop_id' => $this->shop->id,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        // Step 2: Admin approves withdrawal (simulated)
        $withdrawal = \App\Models\Withdrawal::where('shop_id', $this->shop->id)->first();
        $shopService = app(\App\Services\ShopService::class);
        $shopService->processWithdrawal($withdrawal, $admin->id);

        $this->assertDatabaseHas('withdrawals', [
            'id' => $withdrawal->id,
            'status' => 'approved',
        ]);
    }
}
