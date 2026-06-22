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

    public function calculateWithDP(float $subtotal, float $commissionRate, float $dpPercentage): array
    {
        $result = $this->calculate($subtotal, $commissionRate);
        $result['dp_amount'] = round($subtotal * ($dpPercentage / 100), 2);
        $result['dp_percentage'] = round($dpPercentage, 2);

        return $result;
    }

    public function getDefaultCommissionRate(): float
    {
        return config('commission.default_rate', 20.0);
    }

    public function getDefaultDPPercentage(): float
    {
        return (float) config('app.default_dp_percentage', 20.00);
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
