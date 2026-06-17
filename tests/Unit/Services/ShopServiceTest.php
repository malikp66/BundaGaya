<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Services\ShopService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $shopService;
    protected $user;
    protected $shop;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shopService = app(ShopService::class);
        $this->user = User::factory()->create(['role' => 'customer']);
        $this->shop = Shop::factory()->create(['user_id' => $this->user->id, 'status' => 'pending']);
    }

    public function test_can_approve_shop()
    {
        $approvedShop = $this->shopService->approveShop($this->shop);

        $this->assertEquals('active', $approvedShop->status);
        $this->assertTrue($approvedShop->is_verified);
        $this->assertNull($approvedShop->rejection_reason);
        
        $this->user->refresh();
        $this->assertEquals('shop_owner', $this->user->role);
    }

    public function test_can_reject_shop()
    {
        $reason = 'Incomplete documentation';
        $rejectedShop = $this->shopService->rejectShop($this->shop, $reason);

        $this->assertEquals('rejected', $rejectedShop->status);
        $this->assertFalse($rejectedShop->is_verified);
        $this->assertEquals($reason, $rejectedShop->rejection_reason);
    }

    public function test_can_get_shop_revenue()
    {
        $order = Order::factory()->create(['status' => 'completed']);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'shop_id' => $this->shop->id,
            'subtotal' => 1000000,
            'commission_fee' => 100000,
            'net_amount' => 900000,
        ]);
        
        Transaction::factory()->create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'shop_id' => $this->shop->id,
            'amount' => 1000000,
            'commission_fee' => 100000,
            'net_amount' => 900000,
            'status' => 'settled',
        ]);

        $revenue = $this->shopService->getShopRevenue($this->shop);

        $this->assertEquals(900000, $revenue['settled_amount']);
        $this->assertEquals(100000, $revenue['total_commission']);
        $this->assertEquals(1, $revenue['total_transactions']);
    }

    public function test_can_get_available_balance()
    {
        $order = Order::factory()->create(['status' => 'completed']);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'shop_id' => $this->shop->id,
            'net_amount' => 900000,
        ]);
        
        Transaction::factory()->create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'shop_id' => $this->shop->id,
            'net_amount' => 900000,
            'status' => 'settled',
        ]);

        $balance = $this->shopService->getAvailableBalance($this->shop);

        $this->assertEquals(900000, $balance);
    }

    public function test_available_balance_subtracts_withdrawals()
    {
        $order = Order::factory()->create(['status' => 'completed']);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'shop_id' => $this->shop->id,
            'net_amount' => 1000000,
        ]);
        
        Transaction::factory()->create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'shop_id' => $this->shop->id,
            'net_amount' => 1000000,
            'status' => 'settled',
        ]);

        Withdrawal::factory()->create([
            'shop_id' => $this->shop->id,
            'user_id' => $this->user->id,
            'amount' => 300000,
            'status' => 'approved',
        ]);

        $balance = $this->shopService->getAvailableBalance($this->shop);

        $this->assertEquals(700000, $balance);
    }

    public function test_can_request_withdrawal()
    {
        $order = Order::factory()->create(['status' => 'completed']);
        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'shop_id' => $this->shop->id,
            'net_amount' => 1000000,
        ]);
        
        Transaction::factory()->create([
            'order_id' => $order->id,
            'order_item_id' => $orderItem->id,
            'shop_id' => $this->shop->id,
            'net_amount' => 1000000,
            'status' => 'settled',
        ]);

        $withdrawal = $this->shopService->requestWithdrawal($this->shop, [
            'amount' => 500000,
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'account_holder' => 'Test User',
        ]);

        $this->assertDatabaseHas('withdrawals', [
            'shop_id' => $this->shop->id,
            'amount' => 500000,
            'status' => 'pending',
        ]);
    }

    public function test_cannot_withdraw_more_than_balance()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->shopService->requestWithdrawal($this->shop, [
            'amount' => 500000,
            'bank_name' => 'BCA',
            'bank_account' => '1234567890',
            'account_holder' => 'Test User',
        ]);
    }

    public function test_can_get_shop_stats()
    {
        Product::factory()->count(3)->create([
            'shop_id' => $this->shop->id,
            'status' => 'active',
        ]);

        Product::factory()->count(2)->create([
            'shop_id' => $this->shop->id,
            'status' => 'draft',
        ]);

        $stats = $this->shopService->getShopStats($this->shop);

        $this->assertEquals(5, $stats['total_products']);
        $this->assertEquals(3, $stats['active_products']);
    }
}
