<?php

namespace Tests\Feature\Customer;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
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
        
        $this->user = User::factory()->create(['role' => 'customer']);
        $shopOwner = User::factory()->create(['role' => 'shop_owner']);
        $this->shop = Shop::factory()->create([
            'user_id' => $shopOwner->id,
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

    public function test_can_view_orders_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('customer.orders.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Orders/Index')
            ->has('orders')
        );
    }

    public function test_can_filter_orders_by_status()
    {
        $order = $this->createTestOrder();

        $response = $this->actingAs($this->user)
            ->get(route('customer.orders.index', ['status' => 'pending_payment']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Orders/Index')
            ->has('orders.data', 1)
        );
    }

    public function test_can_view_order_detail()
    {
        $order = $this->createTestOrder();

        $response = $this->actingAs($this->user)
            ->get(route('customer.orders.show', $order));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Orders/Show')
            ->has('order')
        );
    }

    public function test_cannot_view_other_users_order()
    {
        $otherUser = User::factory()->create();
        $order = $this->createTestOrder($otherUser);

        $response = $this->actingAs($this->user)
            ->get(route('customer.orders.show', $order));

        $response->assertStatus(403);
    }

    public function test_can_checkout()
    {
        $this->addProductToCart();

        $response = $this->actingAs($this->user)
            ->post(route('customer.checkout'), [
                'address' => 'Test Address 123',
                'phone' => '081234567890',
                'notes' => 'Test notes',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending_payment',
            'address' => 'Test Address 123',
            'phone' => '081234567890',
        ]);
        
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_checkout_with_empty_cart()
    {
        $this->expectException(\App\Exceptions\CartEmptyException::class);
        
        $this->actingAs($this->user)
            ->post(route('customer.checkout'), [
                'address' => 'Test Address 123',
                'phone' => '081234567890',
            ]);
    }

    public function test_can_cancel_order()
    {
        $order = $this->createTestOrder();

        $response = $this->actingAs($this->user)
            ->post(route('customer.orders.cancel', $order), [
                'reason' => 'Changed my mind',
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cannot_cancel_other_users_order()
    {
        $otherUser = User::factory()->create();
        $order = $this->createTestOrder($otherUser);

        $response = $this->actingAs($this->user)
            ->post(route('customer.orders.cancel', $order));

        $response->assertStatus(403);
    }

    public function test_validates_checkout_address()
    {
        $this->addProductToCart();

        $response = $this->actingAs($this->user)
            ->post(route('customer.checkout'), [
                'phone' => '081234567890',
            ]);

        $response->assertSessionHasErrors('address');
    }

    public function test_validates_checkout_phone()
    {
        $this->addProductToCart();

        $response = $this->actingAs($this->user)
            ->post(route('customer.checkout'), [
                'address' => 'Test Address',
            ]);

        $response->assertSessionHasErrors('phone');
    }

    protected function addProductToCart()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $cart = Cart::create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => 3,
            'price_per_day' => 100000,
            'subtotal' => 300000,
        ]);
    }

    protected function createTestOrder($user = null)
    {
        $user = $user ?? $this->user;
        
        return Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending_payment',
        ]);
    }
}
