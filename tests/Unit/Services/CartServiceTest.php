<?php

namespace Tests\Unit\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\CartService;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $cartService;
    protected $user;
    protected $shop;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cartService = app(CartService::class);
        
        $this->user = User::factory()->create();
        $this->shop = Shop::factory()->create(['user_id' => $this->user->id, 'status' => 'active']);
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

    public function test_can_add_item_to_cart()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(3);
        
        $cartItem = $this->cartService->addItem(
            $this->user,
            $this->product,
            $startDate,
            $endDate,
            1
        );

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
            'days' => 3,
        ]);

        $this->assertEquals(300000, $cartItem->subtotal); // 100000 * 3 days
    }

    public function test_can_update_cart_item_quantity()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(3);
        
        $cartItem = $this->cartService->addItem(
            $this->user,
            $this->product,
            $startDate,
            $endDate,
            1
        );

        $updatedItem = $this->cartService->updateItemQuantity(
            $this->user,
            $cartItem->id,
            2
        );

        $this->assertEquals(2, $updatedItem->quantity);
        $this->assertEquals(600000, $updatedItem->subtotal); // 100000 * 3 days * 2 qty
    }

    public function test_can_remove_item_from_cart()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(3);
        
        $cartItem = $this->cartService->addItem(
            $this->user,
            $this->product,
            $startDate,
            $endDate,
            1
        );

        $result = $this->cartService->removeItem($this->user, $cartItem->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_can_clear_cart()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 1);
        
        $product2 = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'price_per_day' => 150000,
            'stock' => 3,
            'status' => 'active',
        ]);
        
        $this->cartService->addItem($this->user, $product2, $startDate, $endDate, 1);

        $this->cartService->clearCart($this->user);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cannot_add_more_than_stock()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(3);
        
        $this->expectException(\Exception::class);
        
        $this->cartService->addItem(
            $this->user,
            $this->product,
            $startDate,
            $endDate,
            10 // More than stock (5)
        );
    }

    public function test_get_cart_summary()
    {
        $startDate = now()->addDays(1);
        $endDate = now()->addDays(3);
        
        $this->cartService->addItem($this->user, $this->product, $startDate, $endDate, 2);

        $summary = $this->cartService->getCartSummary($this->user);

        $this->assertArrayHasKey('cart', $summary);
        $this->assertArrayHasKey('shops', $summary);
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('total_items', $summary);
        $this->assertEquals(2, $summary['total_items']);
        $this->assertEquals(600000, $summary['total']); // 100000 * 3 * 2
    }
}
