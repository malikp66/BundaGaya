<?php

namespace Tests\Feature\Customer;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $consignor;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'customer']);
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

    public function test_can_view_cart()
    {
        $response = $this->actingAs($this->user)
            ->get(route('customer.cart.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Cart/Index')
            ->has('cart')
        );
    }

    public function test_can_add_item_to_cart()
    {
        $startDate = Carbon::now()->addDays(1)->format('Y-m-d');
        $endDate = Carbon::now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->post(route('customer.cart.add'), [
                'product_id' => $this->product->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'quantity' => 1,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    }

    public function test_cannot_add_item_without_login()
    {
        $startDate = Carbon::now()->addDays(1)->format('Y-m-d');
        $endDate = Carbon::now()->addDays(3)->format('Y-m-d');

        $response = $this->post(route('customer.cart.add'), [
            'product_id' => $this->product->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_can_update_cart_item_quantity()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $cart = \App\Models\Cart::create(['user_id' => $this->user->id]);
        $cartItem = $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => 3,
            'price_per_day' => 100000,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($this->user)
            ->patch(route('customer.cart.update', $cartItem->id), [
                'quantity' => 2,
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 2,
            'subtotal' => 600000,
        ]);
    }

    public function test_can_remove_item_from_cart()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $cart = \App\Models\Cart::create(['user_id' => $this->user->id]);
        $cartItem = $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => 3,
            'price_per_day' => 100000,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('customer.cart.remove', $cartItem->id));

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_can_clear_cart()
    {
        $startDate = Carbon::now()->addDays(1);
        $endDate = Carbon::now()->addDays(3);
        
        $cart = \App\Models\Cart::create(['user_id' => $this->user->id]);
        $cart->items()->create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days' => 3,
            'price_per_day' => 100000,
            'subtotal' => 300000,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('customer.cart.clear'));

        $response->assertRedirect();
        
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_validates_start_date_is_in_future()
    {
        $startDate = Carbon::now()->subDays(1)->format('Y-m-d');
        $endDate = Carbon::now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->post(route('customer.cart.add'), [
                'product_id' => $this->product->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'quantity' => 1,
            ]);

        $response->assertSessionHasErrors('start_date');
    }

    public function test_validates_end_date_is_after_start_date()
    {
        $startDate = Carbon::now()->addDays(3)->format('Y-m-d');
        $endDate = Carbon::now()->addDays(1)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->post(route('customer.cart.add'), [
                'product_id' => $this->product->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'quantity' => 1,
            ]);

        $response->assertSessionHasErrors('end_date');
    }
}
