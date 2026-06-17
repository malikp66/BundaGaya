<?php

namespace App\Services;

use App\Models\Setting;

class CommissionService
{
    public function calculate(float $subtotal, float $commissionRate): array
    {
        $commissionFee = $subtotal * ($commissionRate / 100);
        $netAmount = $subtotal - $commissionFee;

        return [
            'subtotal' => round($subtotal, 2),
            'commission_rate' => round($commissionRate, 2),
            'commission_fee' => round($commissionFee, 2),
            'net_amount' => round($netAmount, 2),
        ];
    }

    public function calculateFromOrderItem(float $pricePerDay, int $days, int $quantity, float $commissionRate): array
    {
        $subtotal = $pricePerDay * $days * $quantity;

        return $this->calculate($subtotal, $commissionRate);
    }

    public function getDefaultCommissionRate(): float
    {
        return (float) config('app.default_commission_rate', 10.00);
    }

    public function getAdminFee(): float
    {
        return Setting::getAdminFee();
    }

    public function calculateWithAdminFee(float $subtotal, float $commissionRate): array
    {
        $result = $this->calculate($subtotal, $commissionRate);
        $adminFee = $this->getAdminFee();

        $result['admin_fee'] = $adminFee;
        $result['total_with_admin_fee'] = round($subtotal + $adminFee, 2);

        return $result;
    }
}
