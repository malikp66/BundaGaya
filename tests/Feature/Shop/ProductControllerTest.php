<?php

namespace Tests\Feature\Shop;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $shop;
    protected $category;
    protected $brand;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->user = User::factory()->create(['role' => 'shop_owner']);
        $this->shop = Shop::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
    }

    public function test_can_view_products_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('shop.products.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Shop/Products/Index')
            ->has('products')
        );
    }

    public function test_can_view_create_product_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('shop.products.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Shop/Products/Create')
            ->has('categories')
            ->has('brands')
        );
    }

    public function test_can_create_product()
    {
        $response = $this->actingAs($this->user)
            ->post(route('shop.products.store'), [
                'name' => 'Test Product',
                'category_id' => $this->category->id,
                'brand_id' => $this->brand->id,
                'description' => 'Test description',
                'price_per_day' => 100000,
                'stock' => 5,
                'size' => 'M',
                'color' => 'Red',
                'material' => 'Cotton',
                'condition' => 'good',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('shop.products.index'));
        
        $this->assertDatabaseHas('products', [
            'shop_id' => $this->shop->id,
            'name' => 'Test Product',
            'price_per_day' => 100000,
            'stock' => 5,
        ]);
    }

    public function test_can_create_product_with_photos()
    {
        $file = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->user)
            ->post(route('shop.products.store'), [
                'name' => 'Test Product',
                'category_id' => $this->category->id,
                'brand_id' => $this->brand->id,
                'description' => 'Test description',
                'price_per_day' => 100000,
                'stock' => 5,
                'size' => 'M',
                'color' => 'Red',
                'material' => 'Cotton',
                'condition' => 'good',
                'status' => 'active',
                'photos' => [$file],
            ]);

        $response->assertRedirect(route('shop.products.index'));
        
        $product = Product::where('name', 'Test Product')->first();
        $this->assertNotNull($product);
        $this->assertEquals(1, $product->photos()->count());
    }

    public function test_can_view_edit_product_page()
    {
        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('shop.products.edit', $product));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Shop/Products/Edit')
            ->has('product')
            ->has('categories')
            ->has('brands')
        );
    }

    public function test_can_update_product()
    {
        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'name' => 'Old Name',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('shop.products.update', $product), [
                'name' => 'New Name',
                'category_id' => $this->category->id,
                'brand_id' => $this->brand->id,
                'description' => 'Updated description',
                'price_per_day' => 150000,
                'stock' => 10,
                'size' => 'L',
                'color' => 'Blue',
                'material' => 'Silk',
                'condition' => 'new',
                'status' => 'active',
            ]);

        $response->assertRedirect(route('shop.products.index'));
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
            'price_per_day' => 150000,
            'stock' => 10,
        ]);
    }

    public function test_cannot_edit_other_shops_product()
    {
        $otherShop = Shop::factory()->create(['status' => 'active']);
        $product = Product::factory()->create([
            'shop_id' => $otherShop->id,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('shop.products.edit', $product));

        $response->assertStatus(403);
    }

    public function test_can_delete_product()
    {
        $product = Product::factory()->create([
            'shop_id' => $this->shop->id,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('shop.products.destroy', $product));

        $response->assertRedirect(route('shop.products.index'));
        
        // Product is soft deleted
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_validates_product_name()
    {
        $response = $this->actingAs($this->user)
            ->post(route('shop.products.store'), [
                'category_id' => $this->category->id,
                'price_per_day' => 100000,
                'stock' => 5,
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_validates_price_per_day()
    {
        $response = $this->actingAs($this->user)
            ->post(route('shop.products.store'), [
                'name' => 'Test Product',
                'category_id' => $this->category->id,
                'price_per_day' => -100,
                'stock' => 5,
            ]);

        $response->assertSessionHasErrors('price_per_day');
    }

    public function test_validates_stock()
    {
        $response = $this->actingAs($this->user)
            ->post(route('shop.products.store'), [
                'name' => 'Test Product',
                'category_id' => $this->category->id,
                'price_per_day' => 100000,
                'stock' => 0,
            ]);

        $response->assertSessionHasErrors('stock');
    }
}
