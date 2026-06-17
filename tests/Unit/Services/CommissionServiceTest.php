<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $commissionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commissionService = app(CommissionService::class);
    }

    public function test_can_calculate_commission()
    {
        $result = $this->commissionService->calculate(1000000, 10);

        $this->assertEquals(1000000, $result['subtotal']);
        $this->assertEquals(10, $result['commission_rate']);
        $this->assertEquals(100000, $result['commission_fee']);
        $this->assertEquals(900000, $result['net_amount']);
    }

    public function test_can_calculate_commission_with_different_rate()
    {
        $result = $this->commissionService->calculate(500000, 15);

        $this->assertEquals(500000, $result['subtotal']);
        $this->assertEquals(15, $result['commission_rate']);
        $this->assertEquals(75000, $result['commission_fee']);
        $this->assertEquals(425000, $result['net_amount']);
    }

    public function test_can_calculate_commission_from_order_item()
    {
        $result = $this->commissionService->calculateFromOrderItem(
            100000, // price_per_day
            3,      // days
            2,      // quantity
            10      // commission_rate
        );

        $this->assertEquals(600000, $result['subtotal']); // 100000 * 3 * 2
        $this->assertEquals(10, $result['commission_rate']);
        $this->assertEquals(60000, $result['commission_fee']);
        $this->assertEquals(540000, $result['net_amount']);
    }

    public function test_can_get_admin_fee()
    {
        Setting::create([
            'key' => 'admin_fee',
            'value' => '5000',
            'type' => 'integer',
        ]);

        $adminFee = $this->commissionService->getAdminFee();

        $this->assertEquals(5000, $adminFee);
    }

    public function test_admin_fee_defaults_to_zero_if_not_set()
    {
        // Clear all settings and cache
        Setting::truncate();
        \Illuminate\Support\Facades\Cache::flush();
        
        // Create fresh service instance
        $freshService = new \App\Services\CommissionService();
        $adminFee = $freshService->getAdminFee();

        $this->assertEquals(0, $adminFee);
    }

    public function test_can_calculate_with_admin_fee()
    {
        Setting::create([
            'key' => 'admin_fee',
            'value' => '5000',
            'type' => 'integer',
        ]);

        $result = $this->commissionService->calculateWithAdminFee(1000000, 10);

        $this->assertEquals(1000000, $result['subtotal']);
        $this->assertEquals(100000, $result['commission_fee']);
        $this->assertEquals(5000, $result['admin_fee']);
        $this->assertEquals(1005000, $result['total_with_admin_fee']);
        $this->assertEquals(900000, $result['net_amount']);
    }

    public function test_commission_calculation_rounds_correctly()
    {
        $result = $this->commissionService->calculate(100001, 10);

        $this->assertEquals(10000.1, $result['commission_fee']);
        $this->assertEquals(90000.9, $result['net_amount']);
    }
}
