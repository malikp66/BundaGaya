<?php

namespace Tests\Feature\Shop;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'shop_owner']);
    }

    public function test_can_view_create_shop_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('shop.shop.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Shop/Create')
        );
    }

    public function test_can_create_shop()
    {
        $response = $this->actingAs($this->user)
            ->post(route('shop.shop.store'), [
                'name' => 'Test Shop',
                'description' => 'Test description',
                'phone' => '081234567890',
                'address' => 'Test address',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
            ]);

        $response->assertRedirect(route('shop.dashboard'));
        
        $this->assertDatabaseHas('shops', [
            'user_id' => $this->user->id,
            'name' => 'Test Shop',
            'status' => 'pending',
        ]);
    }

    public function test_cannot_create_shop_if_already_has_one()
    {
        Shop::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->get(route('shop.shop.create'));

        $response->assertRedirect(route('shop.dashboard'));
    }

    public function test_validates_shop_name()
    {
        $response = $this->actingAs($this->user)
            ->post(route('shop.shop.store'), [
                'description' => 'Test description',
            ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_view_shop_dashboard()
    {
        $shop = Shop::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('shop.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Shop/Dashboard')
            ->has('shop')
            ->has('stats')
            ->has('revenue')
        );
    }

    public function test_shop_dashboard_shows_pending_message()
    {
        $shop = Shop::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('shop.dashboard'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Shop/Dashboard')
            ->where('shop.status', 'pending')
        );
    }

    public function test_cannot_access_shop_routes_without_shop_owner_role()
    {
        $response = $this->actingAs($this->user)
            ->get(route('shop.dashboard'));

        $response->assertStatus(403);
    }
}
