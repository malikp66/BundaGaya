<?php

namespace Tests\Feature\Customer;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $consignor;
    protected $category;
    protected $brand;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create(['role' => 'customer']);
        $this->consignor = User::factory()->create(['role' => 'consignor']);
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
        
        $this->product = Product::factory()->create([
            'user_id' => $this->consignor->id,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'status' => 'active',
        ]);
    }

    public function test_can_view_product_index()
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Products/Index')
            ->has('products')
            ->has('categories')
            ->has('brands')
        );
    }

    public function test_can_search_products()
    {
        $response = $this->get(route('products.index', ['search' => 'test']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Products/Index')
            ->where('filters.search', 'test')
        );
    }

    public function test_can_filter_by_category()
    {
        $response = $this->get(route('products.index', ['category_id' => $this->category->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Products/Index')
            ->has('products.data', 1)
        );
    }

    public function test_can_filter_by_brand()
    {
        $response = $this->get(route('products.index', ['brand_id' => $this->brand->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Products/Index')
            ->has('products.data', 1)
        );
    }

    public function test_can_view_product_detail()
    {
        $response = $this->get(route('products.show', $this->product->slug));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Products/Show')
            ->has('product')
            ->has('relatedProducts')
        );
    }

    public function test_only_active_products_are_shown()
    {
        $draftProduct = Product::factory()->create([
            'user_id' => $this->consignor->id,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'status' => 'draft',
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Customer/Products/Index')
            ->has('products.data', 1) // Only active product
        );
    }
}
